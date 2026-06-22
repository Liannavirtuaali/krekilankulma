# Feature Landscape

**Domain:** File-based PHP theme system for a virtual stable website
**Researched:** 2026-06-22

---

## Context: What Already Exists

The existing system has a **CSS-variable color theme** (`color_theme` DB setting, `data-theme` attribute on `<html>`). Ten named color palettes (savi, metsa, yo, etc.) swap CSS custom properties — no PHP template changes. This is **not** a file-based theme system. The new milestone replaces it with a real file-based theme system where each theme owns its own PHP template files and CSS.

Current include structure:
- `public/src/includes/header.php` — reads `color_theme` from DB, loads CSS, renders `<html>` + `<header>` + nav
- `public/src/includes/nav.php` — hardcoded nav markup
- `public/src/includes/footer.php` — static markup
- `public/pages/*.php` — each page does `require header.php` then renders content, then `require footer.php`
- `public/assets/css/style.css` — single stylesheet for entire public site

All pages call `require __DIR__ . '/../src/includes/header.php'` with a relative path. That path must change to route through the active theme.

---

## Table Stakes

Features users (the stable owner) expect. Missing = the theme system feels broken or incomplete.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| `public/themes/{name}/` directory structure | The entire feature premise — themes live on the filesystem | Low | Flat structure: theme files directly in the named folder |
| Theme contains `header.php`, `footer.php`, `nav.php` | These are the shared chrome rendered on every public page | Low | Direct equivalents of the current `src/includes/` files |
| Theme contains one CSS stylesheet | Visual identity of the theme — without it there is no "theme" | Low | Should be `style.css` inside the theme folder |
| Theme contains all public page templates | A theme that only reskins header/footer but leaves page content unstyled is not a real theme | Medium | 7 templates: index, hevoset, hevonen, kasvatus, yhteystiedot, ajankohtaista (blog list), postaus (single post) |
| Active theme stored in `settings` table | Persistence across requests; already has `color_theme` key — reuse or extend this table | Low | New key `active_theme`, or repurpose `color_theme` to hold a theme directory name |
| PHP routing loads templates from active theme | The core routing logic — replaces hardcoded `require '../src/includes/header.php'` | Medium | Needs a `getThemePath()` helper in helpers.php; each page calls it instead of a literal path |
| Admin theme selector lists installed themes | Admin must be able to see what is available before choosing | Low | `scandir('public/themes/')` filtered to directories; display name from directory name or optional `theme.json` |
| Admin theme selector saves active theme | Admin action must persist to DB and take effect on next public page load | Low | POST to existing settings mechanism; same CSRF flow already in settings.php |
| Default theme ships with the project | Without a bundled theme the site breaks immediately after migration | Medium | Current `src/includes/` files + `assets/css/style.css` moved/copied into `public/themes/default/` |
| Admin panel is NOT themed | Requirements state admin stays unchanged; mixing theme loading into admin adds risk and scope with zero user value | Low (don't-do) | Admin continues to use `admin_header.php` + `assets/css/style.css` directly |

---

## Differentiators

Features that go beyond baseline. Not expected for the feature to work, but valued if present.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| `theme.json` metadata file per theme | Gives themes a display name, author, version without parsing directory names; enables nicer admin UI (show "Forest Green" instead of "metsa2") | Low | Optional sidecar file: `{"name": "Metsä", "version": "1.0"}` — read with `json_decode(file_get_contents(...))` if present, fallback to dirname |
| Theme preview in admin selector | Visual swatch or thumbnail so admin can see how a theme looks before activating | Medium | Could be a `preview.png` (320×180) inside the theme folder; shown as `<img>` in the picker. No JS required. |
| Theme fallback for missing files | If active theme is missing a file, fall back to `default` theme's version — site never breaks from a partial theme | Low-Medium | One helper function `themeFile(string $file): string` that checks active theme first, then default; same pattern WordPress uses for child themes |
| Existing CSS color themes preserved as a sub-feature of the default theme | The 10 existing named palettes (savi, metsa, yo…) still work within the default theme's CSS | Low | Default theme CSS keeps `[data-theme="..."]` blocks; `color_theme` setting still applies the `data-theme` attribute within that theme's header.php |
| Theme isolation: themes cannot reach outside their directory | Security guard: `validate_file_name()` already exists; apply same logic to theme directory names | Low | Regex `^[a-z0-9_-]+$` on theme names; reject `../` traversal attempts when building paths |

---

## Anti-Features

Features to explicitly NOT build for this milestone.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| Theme PHP files execute arbitrary logic / have their own functions | Themes are presentation only — allowing business logic in theme files makes the site unauditable and breaks security guarantees | Theme files are templates: they receive variables from the page controller and render them. All DB queries stay in page controllers and helpers.php. |
| Automatic theme installation from ZIP upload | Scope creep; introduces file extraction attack surface (ZipSlip); admin can just FTP new themes directly | Manual FTP deploy of new theme directories — same workflow as site deployment |
| Theme marketplace or remote listing | No hosting, no API; Altervista has no shell access for any dynamic installation | Not relevant to this stable owner |
| Per-page theme overrides | Adds complexity to routing with minimal value for a single-owner site | One active theme applies site-wide |
| Admin panel theming | Requirements explicitly exclude it; admin has its own stable stylesheet in admin_header.php | Leave admin_header.php and its inline CSS untouched |
| Live theme preview (before save) | Requires either a hidden iframe or a session flag for "preview mode" — disproportionate complexity | Theme picker shows `preview.png` thumbnail if present; admin clicks save to apply |
| Theme versioning / rollback | Overkill for a single-owner small site | The previous active theme key is easily reset manually in DB if needed |
| Separate CSS for admin under theme | Admin's visual identity is independent of public themes — mixing them creates maintenance burden | Admin always uses `assets/css/style.css` |

---

## Feature Dependencies

Dependencies on **existing code** that must be understood before implementation:

```
DB settings table (existing) → active_theme storage
  └── Already has color_theme key; upsert pattern already in settings.php

helpers.php (existing) → add getThemePath() / themeFile() functions
  └── validate_file_name() already exists — reuse for theme name sanitization
  └── SITE_URL constant already defined in config.php

header.php, footer.php, nav.php (existing) → become default theme files
  └── Must keep identical behavior; color_theme / data-theme attribute stays in default theme's header.php

Each public page (existing):
  require __DIR__ . '/../src/includes/header.php';   ← changes to themeFile('header.php')
  require __DIR__ . '/../src/includes/footer.php';   ← changes to themeFile('footer.php')

settings.php (existing) → add theme selector UI
  └── Already has POST validation + CSRF + DB upsert pattern
  └── Existing color_theme radio picker can be replaced or accompanied by theme picker

admin_header.php (existing) → must NOT be changed
  └── Loads public assets/css/style.css; if default theme moves CSS, admin needs its own copy or symlink
  └── Critical: admin CSS path must remain stable regardless of active theme
```

### Dependency graph (build order within milestone):

```
1. DB migration: add active_theme row to settings
2. helpers.php: add themeFile(string $file): string  (reads active_theme from GLOBALS/_settings)
3. Create public/themes/default/ and move existing template files + CSS into it
4. Update each public page: swap literal include paths to themeFile() calls
5. Admin settings.php: add theme selector UI + save logic
6. (Optional) theme.json parsing in theme lister
7. (Optional) preview.png display in admin picker
```

---

## MVP Recommendation

The minimum that makes the feature real and usable:

**Prioritize (must ship):**
1. `public/themes/default/` with all existing templates + CSS migrated in — existing site continues to work
2. `themeFile(string $file): string` helper in helpers.php with fallback-to-default logic
3. Update all 7 public page controllers to use `themeFile()` instead of hardcoded paths
4. DB: `active_theme` setting stored and read
5. Admin settings.php: theme selector dropdown (list installed themes, save selection)

**Include if straightforward (adds real value, low risk):**
6. `theme.json` metadata (display name only) — 10 lines of code, much better UX than raw directory names
7. Theme name sanitization guard (regex on directory name before building path)

**Defer to a later iteration:**
- `preview.png` thumbnail support — nice but not needed for the feature to work
- Per-theme `color_theme` sub-setting — can stay as a separate concern
- Second bundled theme (beyond default) — validates the system works but can be done post-milestone

---

## What "Done" Looks Like for MVP

1. `public/themes/default/` exists and contains: `header.php`, `footer.php`, `nav.php`, `style.css`, and all 7 page templates
2. All public pages load templates via `themeFile()` — no hardcoded `src/includes/` paths remain in page controllers
3. Admin settings page lists installed themes and saves selection to DB
4. Activating `default` theme shows identical output to the current site
5. If `active_theme` is not set or points to a nonexistent directory, the site falls back to `default` gracefully
6. Admin panel visuals are unchanged (no regression)
7. The existing CSS color theme (savi, metsa, etc.) still works within the default theme

---

## How PHP Theme Systems Typically Work (Reference)

**WordPress pattern (simplified):**
- `get_template_part()` function resolves a file relative to the active theme directory
- `locate_template()` checks child theme first, then parent theme — the fallback chain
- Theme is identified by directory name under `wp-content/themes/`
- Active theme stored in `wp_options` table as `template` (directory name)
- Required files: `index.php`, `style.css` (with theme header comment) — everything else optional

**Simple PHP CMS pattern (what fits this project):**
- `THEME_PATH` constant set at bootstrap to `public/themes/{active_theme}/`
- Each include call: `require THEME_PATH . 'header.php'` with `file_exists()` fallback to default
- No child theme concept needed for a single-owner site

**What this project should do** (closest fit, minimal complexity):
```php
// In helpers.php
function themeFile(string $filename): string {
    $theme = $GLOBALS['active_theme'] ?? 'default';
    // Sanitize: only allow safe directory names
    if (!preg_match('/^[a-z0-9_-]+$/', $theme)) $theme = 'default';
    $themeFile   = __DIR__ . '/../../themes/' . $theme . '/' . $filename;
    $defaultFile = __DIR__ . '/../../themes/default/' . $filename;
    return file_exists($themeFile) ? $themeFile : $defaultFile;
}
```

Page controllers call `require themeFile('header.php')` instead of the hardcoded path. That is the entire routing change.

---

## Sources

- Codebase analysis: `public/src/includes/header.php`, `footer.php`, `nav.php`, `config.php`, `helpers.php`
- Codebase analysis: `public/pages/index.php`, `hevoset.php`, `ajankohtaista.php`, `postaus.php`
- Codebase analysis: `public/admin/settings.php`, `public/admin/includes/admin_header.php`
- `.planning/PROJECT.md` — milestone definition
- WordPress theme development documentation (general pattern reference, not version-specific)
- Confidence: HIGH for table stakes and dependencies (derived from direct codebase reading); MEDIUM for differentiators (standard PHP CMS patterns)
