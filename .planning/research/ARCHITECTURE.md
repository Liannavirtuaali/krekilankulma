# Architecture: PHP File-Based Theme System

**Project:** Virtuaalitalli — v1.1 Teemajärjestelmä
**Researched:** 2026-06-22
**Scope:** How the theme system integrates with the existing flat-PHP architecture

---

## Existing Architecture (Baseline)

Before documenting the new design, the current structure must be understood precisely.

```
public/
  index.php                  ← redirect to pages/index.php
  .htaccess                  ← mod_rewrite rules + security
  pages/
    index.php                ← data fetch + require header + HTML + require footer
    hevoset.php
    hevonen.php
    kasvatus.php
    yhteystiedot.php
    ajankohtaista.php
    postaus.php
  src/includes/
    config.php               ← constants: SITE_URL, UPLOADS_DIR, etc.
    db.php                   ← PDO singleton + session start; auto-requires helpers.php
    helpers.php              ← e(), slugify(), horseUrl(), CSRF, validation, ...
    header.php               ← queries DB for stable_name + color_theme; emits <head>/<header>/<nav>
    footer.php               ← closes </body></html>
    nav.php                  ← included by header.php
  admin/
    includes/
      admin_header.php       ← full admin shell (sidebar, CSS, <body>)
      admin_footer.php
    *.php                    ← all admin pages
  assets/
    css/style.css            ← entire public CSS (CSS variables for color_theme)
  uploads/                   ← horse photos (excluded from FTP deploy)
```

**Current include pattern in every public page:**
```php
require_once __DIR__ . '/../src/includes/db.php';  // loads config, helpers, session
$page_title = 'Hevoset';
// ... data queries ...
require __DIR__ . '/../src/includes/header.php';   // <head> + <header> + <nav>
// ... page HTML ...
require __DIR__ . '/../src/includes/footer.php';   // </body></html>
```

**Key constraint already in use:** `header.php` already reads `settings` table rows `stable_name` and `color_theme` on every public request via the `$GLOBALS['_vt_settings_loaded']` guard. This is the exact hook point for the theme loader.

---

## Recommended Architecture

### Decision: DB settings table is the right place for active theme

Store `active_theme` as a row in the existing `settings` table (key = `active_theme`, value = theme directory name, e.g. `default`).

**Why not config.php:** Config requires an FTP upload to change. Admin cannot switch themes through the browser.

**Why not a JSON file:** JSON requires `file_put_contents()` which needs write permission on Altervista — unreliable. The DB is already used for `color_theme` with the same read pattern. Adding one row is zero extra infrastructure.

**Why not a PHP session or cookie:** Theme must be consistent for all visitors, not per-user.

**Implementation:** Add a single `settings` row:
```sql
INSERT INTO settings (setting_key, setting_value) VALUES ('active_theme', 'default')
ON DUPLICATE KEY UPDATE setting_value = 'default';
```

### Decision: Theme loading happens in a new `theme.php` bootstrap file

Do not modify `header.php` directly yet. Instead, add a thin `public/src/includes/theme.php` that is required once per page (inside `db.php`, or at the top of each page). It:

1. Reads `active_theme` from `$GLOBALS` (populated by the settings query already in `header.php`), or falls back to querying the DB itself.
2. Defines `THEME_PATH` and `THEME_URL` constants pointing to the active theme directory.
3. Falls back to `default` theme if the resolved directory does not exist.

```php
// theme.php — called after db.php has been required
function resolveTheme(): string {
    if (!empty($GLOBALS['active_theme'])) {
        $name = $GLOBALS['active_theme'];
    } else {
        try {
            $row = getDB()->query(
                "SELECT setting_value FROM settings WHERE setting_key = 'active_theme' LIMIT 1"
            )->fetchColumn();
            $name = ($row !== false && $row !== '') ? $row : 'default';
        } catch (Exception $e) {
            $name = 'default';
        }
    }
    // Sanitize: only a-z, 0-9, hyphen, underscore
    $name = preg_replace('/[^a-z0-9\-_]/', '', strtolower($name));
    $dir  = __DIR__ . '/../../themes/' . $name;
    return is_dir($dir) ? $name : 'default';
}

if (!defined('ACTIVE_THEME')) {
    $themeName = resolveTheme();
    define('ACTIVE_THEME', $themeName);
    define('THEME_PATH', __DIR__ . '/../../themes/' . ACTIVE_THEME);
    define('THEME_URL',  SITE_URL . '/themes/' . ACTIVE_THEME);
}
```

### Decision: Pages become thin controllers; templates live in the theme

Each page file (`pages/hevoset.php` etc.) currently mixes data fetching and HTML output. Under the theme system, pages become controllers:

```php
// pages/hevoset.php — after migration
require_once __DIR__ . '/../src/includes/db.php';
// ... data queries (unchanged) ...
require THEME_PATH . '/pages/hevoset.php';   // delegate all HTML to theme
```

The theme page template gets all the data as variables already set in the calling scope (PHP include shares scope). No function calls, no globals — the template just uses `$horses`, `$page_title`, etc.

### Decision: Theme header/footer replace src/includes header/footer

`header.php` currently hardcodes the CSS path to `/assets/css/style.css`. The theme version points to `THEME_URL . '/assets/css/style.css'` instead. The include calls in page controllers change from:

```php
require __DIR__ . '/../src/includes/header.php';  // OLD
```
to:
```php
require THEME_PATH . '/includes/header.php';       // NEW
```

The theme's `header.php` can internally `require_once` the original `src/includes/header.php` logic for the DB query (or duplicate only the relevant parts). The cleanest approach: the theme's `header.php` calls the settings-loading logic that is already extracted into `theme.php`, then emits its own HTML with its own CSS path.

---

## Recommended Directory Structure

```
public/
  themes/
    default/
      theme.json             ← metadata: name, version, author, description
      includes/
        header.php           ← <head> + <header> + <nav>; uses THEME_URL for CSS
        footer.php           ← </body></html>
        nav.php              ← navigation markup
      pages/
        index.php            ← frontpage template
        hevoset.php          ← horse list template
        hevonen.php          ← horse profile template
        kasvatus.php         ← breeding page template
        yhteystiedot.php     ← contact page template
        ajankohtaista.php    ← news list template
        postaus.php          ← single post template
      assets/
        css/
          style.css          ← theme CSS (copied/adapted from current assets/css/style.css)
        js/                  ← theme JS (optional)
        img/                 ← theme images (logo, bg, etc.)
  src/includes/
    theme.php                ← NEW: ACTIVE_THEME, THEME_PATH, THEME_URL constants
    config.php               ← unchanged
    db.php                   ← add: require_once theme.php at the end
    helpers.php              ← unchanged
    header.php               ← DEPRECATED: kept for fallback, will be superseded by theme includes
    footer.php               ← DEPRECATED: same
    nav.php                  ← DEPRECATED: same
  pages/
    index.php                ← becomes: data queries + require THEME_PATH/pages/index.php
    hevoset.php              ← same pattern
    hevonen.php              ← same pattern
    kasvatus.php             ← same pattern
    yhteystiedot.php         ← same pattern
    ajankohtaista.php        ← same pattern
    postaus.php              ← same pattern
  admin/                     ← UNTOUCHED by theme system
  assets/
    css/style.css            ← kept for admin panel (admin_header.php links here)
```

**theme.json format:**
```json
{
  "name": "Default",
  "version": "1.0",
  "author": "Tilli-simgame",
  "description": "Alkuperäinen tallin ulkoasu"
}
```

---

## Component Boundaries

| Component | Responsibility | Communicates With |
|-----------|---------------|-------------------|
| `src/includes/db.php` | PDO singleton, session start | Requires `config.php`, `helpers.php`, `theme.php` |
| `src/includes/theme.php` | Resolve active theme name, define THEME_PATH/THEME_URL | Reads `settings` table via `getDB()` |
| `pages/*.php` (controllers) | Data queries only; no HTML | Requires db.php, then requires THEME_PATH/pages/*.php |
| `themes/{name}/includes/header.php` | Emit full HTML head, site header, nav | Uses THEME_URL, SITE_URL, SITE_NAME, stable_name |
| `themes/{name}/includes/footer.php` | Close page HTML | Uses SITE_NAME, date() |
| `themes/{name}/pages/*.php` | All page HTML | Consumes variables set by controller |
| `themes/{name}/assets/css/style.css` | Theme visual design | Loaded by theme header.php |
| `admin/settings.php` | Save active_theme to settings table | Writes settings row; lists installed themes |
| `admin/` (all other) | Admin CRUD, unchanged | Still uses `src/includes/header.php` for admin shell |

---

## Data Flow

```
HTTP request → pages/hevoset.php
  └── require db.php
        └── require config.php     (SITE_URL etc.)
        └── require helpers.php    (e(), slugify() etc.)
        └── require theme.php      (ACTIVE_THEME, THEME_PATH, THEME_URL)
              └── SELECT active_theme FROM settings
  └── $horses = [... PDO queries ...]
  └── $page_title = 'Hevoset'
  └── require THEME_PATH/pages/hevoset.php
        └── require THEME_PATH/includes/header.php
              └── SELECT stable_name, color_theme FROM settings  (already in $GLOBALS guard)
              └── emit: <!DOCTYPE html> ... <link href="THEME_URL/assets/css/style.css"> ...
        └── emit: page HTML using $horses, $page_title
        └── require THEME_PATH/includes/footer.php
              └── emit: </body></html>
```

**Admin path (unchanged):**
```
HTTP request → admin/horses.php
  └── require src/includes/db.php
  └── requireLogin()
  └── ... data queries ...
  └── require admin/includes/admin_header.php   (uses assets/css/style.css — NOT theme)
  └── ... admin HTML ...
  └── require admin/includes/admin_footer.php
```

---

## Patterns to Follow

### Pattern 1: Thin Controller + Theme Template

The page controller does data work; the theme template does presentation. The controller sets variables; the template uses them. No return values, no function arguments — plain PHP include scope sharing.

```php
// pages/hevoset.php (controller)
require_once __DIR__ . '/../src/includes/db.php';
$page_title = 'Hevoset';
$horses = $db->query('SELECT ... FROM horses ...')->fetchAll();
require THEME_PATH . '/pages/hevoset.php';
// EOF — no HTML in this file
```

```php
// themes/default/pages/hevoset.php (template)
require THEME_PATH . '/includes/header.php';
?>
<main>
  <?php foreach ($horses as $horse): ?>
    ...
  <?php endforeach; ?>
</main>
<?php require THEME_PATH . '/includes/footer.php'; ?>
```

### Pattern 2: Settings-Table Active Theme with Directory Guard

Always validate that the theme directory actually exists before using it. Fall back to `default` silently. This prevents a broken install from taking the site down.

```php
$name = preg_replace('/[^a-z0-9\-_]/', '', strtolower($raw));
$dir  = __DIR__ . '/../../themes/' . $name;
return is_dir($dir) ? $name : 'default';
```

### Pattern 3: theme.json Discovery for Admin Theme Picker

The admin theme picker scans `themes/*/theme.json` with `glob()` to list installed themes without hardcoding names. This means adding a new theme only requires uploading the directory — no code change.

```php
// In admin/settings.php (or a helper function)
$themeDir = __DIR__ . '/../themes/';
$installed = [];
foreach (glob($themeDir . '*/theme.json') as $jsonFile) {
    $meta = json_decode(file_get_contents($jsonFile), true);
    $dirName = basename(dirname($jsonFile));
    $installed[$dirName] = $meta['name'] ?? $dirName;
}
```

### Pattern 4: THEME_PATH as Absolute Path Constant

Always use `THEME_PATH` (absolute) for `require`/`include` calls. Use `THEME_URL` (HTTP URL) only for HTML `href`/`src` attributes. Never construct paths relative to the calling file inside theme templates — it will break if the include depth changes.

---

## Anti-Patterns to Avoid

### Anti-Pattern 1: Modifying Every Page File's require Paths Piecemeal

**What:** Changing `require '/../src/includes/header.php'` to a theme path in each page one by one over multiple sessions.

**Why bad:** The site is in a half-migrated state if the work is interrupted. Some pages use the theme; others use the old header. CSS conflicts, duplicate DB queries, and navigation inconsistencies appear.

**Instead:** Migrate all controllers atomically in one plan. The old `src/includes/header.php` stays in place as a fallback until every page is migrated and the default theme is verified.

### Anti-Pattern 2: Putting Business Logic in Theme Templates

**What:** Placing PDO queries, `requireLogin()`, or validation inside `themes/default/pages/*.php`.

**Why bad:** Themes become non-portable. A theme author cannot swap themes without understanding the application's data layer. Breaks the one-theme-per-look contract.

**Instead:** All data fetching stays in `pages/*.php` (controllers). Templates receive only pre-fetched, already-sanitized variables.

### Anti-Pattern 3: Using file_put_contents() to Store the Active Theme

**What:** Writing the active theme name to a JSON file or a PHP config file from the admin panel.

**Why bad:** Altervista may have write restrictions on web directories. FTP deploys would also overwrite the file. The DB `settings` table already exists and handles this pattern correctly for `color_theme`.

**Instead:** Use `INSERT ... ON DUPLICATE KEY UPDATE` on `settings` table, same as the existing color theme save.

### Anti-Pattern 4: Putting Theme CSS Inside the Global assets/css/style.css

**What:** Adding theme-specific rules to `public/assets/css/style.css` and using CSS class names or data attributes to switch between theme styles at runtime.

**Why bad:** The file grows unbounded with each theme. A custom theme uploader cannot isolate their CSS without touching the core file. Already partially done with `data-theme` color variables — that pattern is fine for color theming, but full layout/structure changes need separate files.

**Instead:** Each theme has its own `assets/css/style.css`. The `default` theme starts as a copy of the current file. `public/assets/css/style.css` is kept only for the admin panel.

---

## New vs Modified Files

### New Files

| File | Purpose |
|------|---------|
| `public/src/includes/theme.php` | Theme resolver: reads DB, defines THEME_PATH/THEME_URL |
| `public/themes/default/theme.json` | Theme metadata for admin discovery |
| `public/themes/default/includes/header.php` | Theme-specific header (copy + adapt from src/includes/header.php) |
| `public/themes/default/includes/footer.php` | Theme footer |
| `public/themes/default/includes/nav.php` | Theme nav |
| `public/themes/default/pages/index.php` | Frontpage template (HTML extracted from pages/index.php) |
| `public/themes/default/pages/hevoset.php` | Horse list template |
| `public/themes/default/pages/hevonen.php` | Horse profile template |
| `public/themes/default/pages/kasvatus.php` | Breeding template |
| `public/themes/default/pages/yhteystiedot.php` | Contact template |
| `public/themes/default/pages/ajankohtaista.php` | News list template |
| `public/themes/default/pages/postaus.php` | Single post template |
| `public/themes/default/assets/css/style.css` | Copy of current assets/css/style.css |

### Modified Files

| File | Change |
|------|--------|
| `public/src/includes/db.php` | Add `require_once __DIR__ . '/theme.php';` at the end |
| `public/pages/index.php` | Keep data queries; replace `require header/footer` with THEME_PATH equivalents |
| `public/pages/hevoset.php` | Same |
| `public/pages/hevonen.php` | Same |
| `public/pages/kasvatus.php` | Same |
| `public/pages/yhteystiedot.php` | Same |
| `public/pages/ajankohtaista.php` | Same |
| `public/pages/postaus.php` | Same |
| `public/admin/settings.php` | Add theme picker UI; add `active_theme` save to existing POST handler; add theme scanner with glob() |
| DB `settings` table | Add row: `active_theme` = `default` (migration SQL) |

### Untouched Files

| File | Reason |
|------|--------|
| `public/admin/**` | Admin panel is explicitly out of theme scope |
| `public/src/includes/config.php` | No change needed |
| `public/src/includes/helpers.php` | No change needed |
| `public/src/includes/header.php` | Kept as fallback; deprecated but not deleted |
| `public/src/includes/footer.php` | Same |
| `public/assets/css/style.css` | Still needed by admin panel |
| `public/.htaccess` | No new rewrites needed; themes/ served as static files |
| `.github/workflows/deploy.yml` | Already deploys entire public/; themes/ is under public/ |

---

## Build Order (Dependency-Respecting)

### Step 1: DB migration + theme.php
Add `active_theme` row to `settings` table. Write `src/includes/theme.php`. Add the require to the end of `db.php`. Verify `THEME_PATH` and `THEME_URL` constants are defined after any page loads.

*Dependency: None. This is safe to ship alone — no existing code breaks.*

### Step 2: Default theme scaffolding (includes only)
Create `public/themes/default/` with `theme.json`, `includes/header.php`, `includes/footer.php`, `includes/nav.php`, and `assets/css/style.css` (copy from current). Do NOT yet change page controllers.

*Dependency: Step 1 (THEME_PATH must be defined).*

### Step 3: Migrate page controllers one by one
For each page in `pages/`, replace hardcoded `require '/../src/includes/header.php'` with `require THEME_PATH . '/includes/header.php'` and extract the HTML body into the corresponding `themes/default/pages/*.php` template. Test each page before moving to the next.

*Dependency: Step 2 (theme includes must exist).*

### Step 4: Admin theme picker
Add the theme selector to `admin/settings.php`. Uses `glob()` to scan `themes/*/theme.json`. Saves `active_theme` to DB with the existing POST handler pattern. Shows installed themes as cards (similar to existing color_theme picker).

*Dependency: Step 1 (settings table row); Step 2 (at least one theme must be discoverable via theme.json).*

### Step 5: End-to-end test on Altervista
Deploy, verify all public pages load from the theme, switch active theme in admin, confirm the change takes effect without touching files.

---

## Altervista-Specific Constraints

| Constraint | Implication |
|------------|-------------|
| No shell access | Cannot run `php artisan`, `composer`, or migration scripts manually. DB migration must be done via a one-time PHP script or phpMyAdmin. |
| FTP-only deploy | `themes/` must live under `public/` so the existing deploy.yml uploads it. No change to workflow needed. |
| PHP 8.2.31, no OPcache guarantees | Keep includes flat and avoid deep autoloading chains. `require_once` chains of 3–4 files maximum per request. |
| `is_dir()` is available | The theme directory existence check works without shell access. |
| `glob()` is available | Theme discovery in admin works. |
| `file_get_contents()` on local paths is available | Reading `theme.json` works. |
| No write permission assumed | Do not attempt `file_put_contents()` for theme switching. Use DB only. |
| FTP deploy excludes `uploads/` | Theme assets are in `themes/*/assets/` which IS deployed. Horse photos in `uploads/` remain excluded — correct. |

---

## Scalability Considerations

This is a single-owner virtual stable. Scalability means "how many themes without headache," not traffic volume.

| Concern | With 1–3 themes | With 10+ themes |
|---------|----------------|-----------------|
| Theme discovery | `glob()` is fast | Still fast; no DB query needed for list |
| CSS isolation | One file per theme | Same; no conflict possible |
| Template duplication | Pages duplicated per theme | Accept it — this is the right tradeoff for a no-framework stack |
| Admin theme picker | Renders all cards | Might need scrolling; acceptable |

---

## Sources

All findings are derived directly from reading the existing codebase. Confidence: HIGH — the integration points are based on exact code that was read, not inferred.

- `public/src/includes/header.php` — settings query pattern, `$GLOBALS` guard
- `public/src/includes/db.php` — require chain and session start location
- `public/admin/settings.php` — existing color_theme save pattern and `INSERT ON DUPLICATE KEY`
- `public/pages/hevoset.php` — representative controller/template mixing pattern
- `public/.htaccess` — no changes needed; themes/ serves as static files
- `.github/workflows/deploy.yml` — deploys entire `public/`; themes/ included automatically
