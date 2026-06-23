# Stack Research

**Domain:** PHP file-based theme system for an existing virtual stable website
**Researched:** 2026-06-22
**Confidence:** HIGH

## Context

This is a subsequent milestone on an existing PHP 8.2 / MySQL application hosted on Altervista
(no shell access, FTP-only deployment). The existing codebase already uses:

- `public/src/includes/header.php`, `footer.php`, `nav.php` as shared partials
- A `settings` DB table (key-value store) that already persists `color_theme` and `stable_name`
- `require __DIR__ . '/../src/includes/header.php'` calls scattered across all public pages

The theme system replaces those hardcoded `require` paths with theme-aware lookups. No new
languages, runtimes, or external packages are needed.

---

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| PHP 8.2 | 8.2.31 (locked by Altervista) | Theme resolution, file loading, template rendering | Already in use; `glob()`, `is_dir()`, `require` cover all theme needs natively |
| MySQL / PDO | existing | Store active theme name in `settings` table | `settings` table already exists; add one row (`active_theme`) — zero schema churn |
| HTML5 / CSS3 | — | Theme template markup and per-theme stylesheet | Theme CSS lives in `public/themes/{name}/css/style.css`; same browser targets as today |

### Supporting Libraries

None required. Pure PHP is sufficient and preferable because:

- Altervista has no Composer support (no shell, FTP-only)
- Any dependency would need to be vendored manually and adds attack surface
- PHP's built-in `glob()`, `realpath()`, `is_file()`, `require` handle everything

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| GitHub Actions (existing) | FTP deploy of `public/` on push to main | No changes needed; `public/themes/` is inside `public/` so it deploys automatically |
| phpMyAdmin (Altervista cPanel) | Run SQL migration to insert `active_theme` default | One-time `INSERT IGNORE` into existing `settings` table |
| Docker (existing dev env) | Local development at localhost:8080 | Mount `public/themes/` volume exactly as production; no extra config |

---

## Theme Loading Mechanism

### The single correct PHP pattern

Add one function to `helpers.php`:

```php
/**
 * Returns the absolute filesystem path to a theme file.
 * Falls back to default/ if the file does not exist in the active theme.
 *
 * @param string $file  e.g. 'header.php' or 'pages/hevoset.php'
 * @return string       Absolute path safe to pass to require
 */
function theme_path(string $file): string {
    $active = $GLOBALS['active_theme'] ?? 'default';
    // Sanitize: only allow [a-z0-9_-] to prevent path traversal
    $active = preg_replace('/[^a-z0-9_-]/', '', strtolower($active));

    $themesBase = __DIR__ . '/../../themes/';          // public/themes/
    $candidate  = realpath($themesBase . $active . '/' . $file);
    $fallback   = realpath($themesBase . 'default/'   . $file);

    // Verify the resolved path still starts with $themesBase (path traversal guard)
    $themesBase = realpath($themesBase);
    if ($candidate && str_starts_with($candidate, $themesBase)) {
        return $candidate;
    }
    if ($fallback && str_starts_with($fallback, $themesBase)) {
        return $fallback;
    }
    throw new RuntimeException("Theme file not found: $file");
}
```

Call sites in public pages change from:

```php
require __DIR__ . '/../src/includes/header.php';
```

to:

```php
require theme_path('header.php');
```

### Active theme loading in header.php

`header.php` already loads settings from DB (it fetches `stable_name` and `color_theme`).
Extend the same query to also fetch `active_theme`:

```php
$rows = $db->query(
    "SELECT setting_key, setting_value FROM settings
     WHERE setting_key IN ('stable_name','color_theme','active_theme')"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$GLOBALS['active_theme'] = (!empty($rows['active_theme'])) ? $rows['active_theme'] : 'default';
```

This piggybacks on the existing `$GLOBALS['_vt_settings_loaded']` guard so settings are
fetched exactly once per request — no extra DB query.

---

## Storage Mechanism: DB `settings` table (recommended)

| Option | Verdict | Reason |
|--------|---------|--------|
| **DB `settings` row** (recommended) | Use this | `settings` table already exists; admin panel already reads/writes it via POST form; consistent with `color_theme` pattern already in use; survives FTP deploys without overwrite risk |
| Flat `config.php` constant | Avoid | Requires FTP upload or PHP file-write (`file_put_contents`) on every theme change; Altervista may restrict PHP writing to `src/` (protected by `.htaccess`); not consistent with existing pattern |
| JSON config file | Avoid | Same FTP-write problem; adds file parsing; no benefit over DB for a single value |
| `.htaccess` env var | Avoid | Not writable from PHP on Altervista shared hosting; intended for deploy-time config, not user-changeable setting |

**Migration SQL** (add to a new `migrate_theme.sql`):

```sql
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`)
VALUES ('active_theme', 'default');
```

---

## Theme Directory Structure

```
public/
└── themes/
    └── default/
        ├── header.php
        ├── footer.php
        ├── nav.php
        ├── pages/
        │   ├── index.php
        │   ├── hevoset.php
        │   ├── hevonen.php
        │   ├── kasvatus.php
        │   ├── yhteystiedot.php
        │   ├── ajankohtaista.php
        │   └── postaus.php
        └── css/
            └── style.css
```

The existing `public/assets/css/style.css` and `public/src/includes/` partials become the
source of truth that gets copied into `public/themes/default/` to bootstrap the first theme.
`header.php` inside the theme outputs `<link rel="stylesheet">` pointing to the theme's own
CSS, not the global `assets/css/style.css`.

---

## Admin Panel: Theme Selection UI

The admin panel (`public/admin/settings.php`) already handles `color_theme` selection with
a radio-button picker. The theme-file picker follows the exact same pattern:

1. Use `glob(PUBLIC_DIR . '/themes/*', GLOB_ONLYDIR)` to list installed themes — no hardcoded
   allow-list needed because new themes just need to be FTP-uploaded.
2. Validate the submitted theme name against the directory listing (not a static array) before
   saving to DB, to prevent saving a name that has no directory.
3. `basename()` the directory names before rendering to prevent XSS.

```php
// In settings.php POST handler — theme validation
$themes_dir = __DIR__ . '/../themes/';
$installed  = array_map('basename', glob($themes_dir . '*', GLOB_ONLYDIR) ?: []);
$theme_post = basename($_POST['active_theme'] ?? 'default');
$values['active_theme'] = in_array($theme_post, $installed, true) ? $theme_post : 'default';
```

---

## Alternatives Considered

| Recommended | Alternative | Why Not |
|-------------|-------------|---------|
| DB `settings` row for active theme | PHP constant in `config.php` | Requires PHP file-write on Altervista (restricted); not user-changeable from admin UI |
| `theme_path()` helper + `require` | Output buffering / `ob_start()` template engine | Unnecessary complexity; existing codebase uses plain `require` throughout; no new abstraction layer needed |
| Per-theme CSS in `themes/{name}/css/` | Single global `assets/css/style.css` with CSS vars | CSS vars already used for `color_theme`; file-based themes need full CSS replacement not just var override |
| `glob()` for installed theme listing | Hardcoded theme array in settings.php | `glob()` auto-discovers FTP-uploaded themes without code changes; safer operational model |
| Fallback to `default/` when file missing | Fatal error / 404 | Altervista has no shell to debug; graceful fallback prevents white-screen-of-death during partial theme uploads |

---

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| Composer / any third-party package | No shell on Altervista; FTP-only deployment makes vendoring fragile; zero PHP templating libs needed | Pure PHP `require` + `theme_path()` helper |
| Smarty / Twig / Blade | Templating engines require Composer or manual vendor bundling; adds 100–500 KB; the existing codebase uses no templating engine | Plain PHP templates (existing pattern) |
| PHP `eval()` or `preg_replace_callback` for template vars | Security risk; unnecessary | PHP files as templates (existing pattern) |
| WordPress-style `add_action` / hook system | Massive over-engineering for a single-admin site with 7 public pages | Direct `require theme_path(...)` calls |
| `include_once` for theme files | Theme partials should always be freshly included per request (header state, nav active state) | `require theme_path(...)` (not `require_once`) |
| Storing theme path as absolute path in DB | Brittle across environments (local vs Altervista); path traversal risk | Store only the theme directory name (`default`, `forest`, etc.) |

---

## Altervista-Specific Constraints

| Constraint | Impact | Mitigation |
|------------|--------|------------|
| No shell access | Cannot run `php artisan`, `composer install`, `npm build` | Pure PHP; no build step; all theme files are static PHP + CSS |
| FTP-only file management | Theme "installation" = FTP upload of a folder | `glob()` auto-discovers newly uploaded theme directories |
| PHP 8.2.31 (locked) | Must use 8.2-compatible syntax | `str_starts_with()` available since 8.0; `realpath()` path guard works on 8.2 |
| `public/` is the FTP deploy target | `themes/` must live inside `public/` | `public/themes/{name}/` is correct; already within deploy scope |
| `.htaccess` on `src/includes/` blocks direct web access | Theme config cannot be stored in `src/` as a writable file | DB storage avoids needing to write files in protected directories |
| Shared hosting: `file_put_contents` may be restricted in `src/` | Cannot write config files from PHP in `src/includes/` | DB row is the reliable write target |

---

## Version Compatibility

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 8.2.31 | `str_starts_with()` (8.0+), `glob()`, `realpath()`, `require` all available |
| MySQL | existing Altervista version | `INSERT IGNORE INTO settings` — no schema change, only a new row |
| `settings` table | existing schema | `setting_key VARCHAR(100) PRIMARY KEY` — `active_theme` key fits |

---

## Summary Recommendation

No new libraries, no new language, no build tools. The theme system is pure PHP:

1. **One new helper function** `theme_path()` in `helpers.php` — resolves and guards file paths
2. **One DB row** `active_theme = 'default'` in the existing `settings` table
3. **One new directory** `public/themes/default/` containing moved template files
4. **Extended settings.php** admin form — radio picker driven by `glob()` of theme directories
5. **Updated `require` calls** in all public pages — replace 14 hardcoded paths with `theme_path()`

The existing `$GLOBALS['_vt_settings_loaded']` pattern in `header.php` handles the DB read
with zero additional queries. The entire system is deployable via FTP with no server-side
configuration changes.
