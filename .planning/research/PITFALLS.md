# Pitfalls Research

**Domain:** File-based theme system added to existing PHP/MySQL app (Altervista hosting)
**Researched:** 2026-06-22
**Confidence:** HIGH — based on direct code inspection of the existing codebase plus established PHP security and migration patterns

---

## Critical Pitfalls

### Pitfall 1: Path Traversal via Theme Name from Database

**What goes wrong:**
The active theme name is read from the `settings` table and used to construct a file path like `require __DIR__ . '/../../themes/' . $active_theme . '/header.php'`. If a compromised DB value or a logic bug allows a string like `../../admin/includes/admin_header` to reach that `require`, an attacker who controls the DB (or finds a second-order injection) can load arbitrary PHP files on the server.

**Why it happens:**
Developers trust DB values as "already validated on write." But the read path has no validation, so anything that reaches the `require` statement is accepted. This is a second-order path traversal — the dangerous value was stored earlier, not injected at read time.

**How to avoid:**
Validate the theme name on *read*, not only on write. Use an allowlist approach:

```php
function resolveThemePath(string $theme_name): string {
    // 1. Reject anything that is not [a-z0-9_-]
    if (!preg_match('/^[a-z0-9_-]+$/', $theme_name)) {
        $theme_name = 'default';
    }
    // 2. Build the real path and confirm it sits inside themes/
    $base   = realpath(__DIR__ . '/../../themes');
    $target = realpath($base . '/' . $theme_name);
    if ($target === false || strpos($target, $base) !== 0) {
        $theme_name = 'default';
        $target     = $base . '/default';
    }
    return $target;
}
```

Never use `require` on a path that has not been through `realpath()` + prefix check.

**Warning signs:**
- Theme name stored in DB is used directly in `require` or `include` without sanitization.
- `realpath()` is not called anywhere in the theme-loading code.
- The theme name can contain `/`, `..`, or `.php` extensions.

**Phase to address:** Theme infrastructure phase — this must be built into the very first `loadThemeFile()` helper before any page uses it.

---

### Pitfall 2: Missing Theme File Causes Fatal Error (White Screen of Death)

**What goes wrong:**
A page calls `require __DIR__ . '/themes/custom/header.php'` and that file does not exist — perhaps the theme was partially uploaded over FTP, or a new page template was added to the default theme but not to the custom theme. PHP raises a fatal `E_ERROR` and the page is blank. On Altervista where `display_errors` may be off, the user sees only a white page with no feedback.

**Why it happens:**
`require` (not `require_once`) halts on missing files. File-based themes inherently create a "missing file" risk every time a theme is incomplete or a new page type is added to one theme but not another.

**How to avoid:**
Use a two-step fallback loader throughout:

```php
function requireThemeFile(string $theme_path, string $file, string $fallback_theme = 'default'): void {
    $primary  = $theme_path . '/' . $file;
    $fallback = dirname($theme_path) . '/' . $fallback_theme . '/' . $file;
    if (file_exists($primary)) {
        require $primary;
    } elseif (file_exists($fallback)) {
        require $fallback;
    } else {
        // Absolute last resort — log and render minimal safe output
        error_log("Theme file missing: $file in $theme_path and fallback $fallback_theme");
        // For header/footer: render a barebones HTML scaffold so the page does not break entirely
    }
}
```

The `default` theme must be treated as the canonical fallback and must always be complete — it is the "source of truth" template set.

**Warning signs:**
- `require` used instead of a wrapper function anywhere theme files are loaded.
- The default theme is modified but no completeness check exists.
- FTP deployment can upload a partial theme (no atomic deploy mechanism).

**Phase to address:** Theme infrastructure phase — the fallback loader must be the only mechanism used; no raw `require` on theme paths anywhere.

---

### Pitfall 3: Breaking Existing Pages During Migration to Theme Structure

**What goes wrong:**
The current pages (e.g. `pages/hevoset.php`) use hardcoded `require __DIR__ . '/../src/includes/header.php'`. When that file moves to `themes/default/`, the hardcoded path breaks all existing pages simultaneously. If the migration is done page by page without a compatibility shim, the site has broken pages in production during the transition.

**Why it happens:**
The include paths are scattered across every page file. There is no central dispatch — each page independently resolves its own paths. Moving the files breaks all callsites at once.

**How to avoid:**
Do not move the files first. Instead:
1. Create a thin "theme loader" shim at `src/includes/theme.php` that delegates to the active theme's files.
2. Update each page to `require __DIR__ . '/../src/includes/theme.php'` (a single find-and-replace).
3. Only then move the actual template files into `themes/default/`.
4. The shim remains at the old path forever — pages always `require` the shim, which forwards to the theme.

This means the callsite (`src/includes/theme.php`) never changes, and all theme resolution is centralised in one place.

**Warning signs:**
- Any page still has a direct `require` pointing at `src/includes/header.php` after the migration starts.
- Migration is done by moving files without first introducing the shim.
- No intermediate commit where the shim exists and pages use it before templates are moved.

**Phase to address:** Migration sub-phase within the theme infrastructure phase — introduce the shim first, deploy, verify, then move templates.

---

### Pitfall 4: Admin Panel Accidentally Themed

**What goes wrong:**
The theme loader reads `$active_theme` from `$GLOBALS` or a singleton that is set early in `header.php`. The admin panel includes its own `admin_header.php`, but if any shared include (e.g. `db.php`, `helpers.php`) now also initialises the theme, admin pages begin loading theme CSS or layout that overwrites the admin style, breaking the admin UI.

**Why it happens:**
Theme initialisation is added to a shared bootstrap file (like `db.php` or `config.php`) for convenience, but admin pages also load those bootstrap files. There is no guard separating "public theme context" from "admin context."

**How to avoid:**
The theme resolution function must only be called explicitly from public page files (via the shim). It must never be called inside `db.php`, `config.php`, `helpers.php`, or any file that admin pages include. A simple constant guard works:

```php
// In theme.php (public shim only):
define('THEME_CONTEXT', 'public');
```

Admin files can check `!defined('THEME_CONTEXT')` before loading theme assets, or simply never include `theme.php` at all.

**Warning signs:**
- Theme initialisation code is placed in `db.php` or `config.php`.
- Admin pages begin showing a font or colour change after theme code is added.
- `$GLOBALS['_vt_settings_loaded']` is reused to also cache the active theme name (it currently handles `stable_name` and `color_theme` — adding a file-based theme name here risks entangling public and admin contexts).

**Phase to address:** Theme infrastructure phase — enforce public/admin separation from the start, not as a retrofit.

---

### Pitfall 5: Theme CSS Path Resolution Breaks on Altervista

**What goes wrong:**
The theme's CSS is referenced as `<link rel="stylesheet" href="<?= SITE_URL ?>/themes/default/style.css">`. This works locally but breaks on Altervista if `SITE_URL` is set incorrectly (no trailing slash, wrong subdirectory, or HTTPS vs HTTP mismatch). The current `header.php` already uses `SITE_URL . '/assets/css/style.css'`; migrating to themes requires updating this path, and Altervista's URL structure must be verified before the path is hardened.

**Why it happens:**
Altervista serves sites from subdirectory paths (e.g. `yourusername.altervista.org/foldername/`). `SITE_URL` in `config.php` is set manually — if it does not match the actual server path, all theme asset URLs are wrong. No automatic detection exists.

**How to avoid:**
- Test the exact `SITE_URL` value on Altervista before finalising any theme asset URL pattern.
- Use `SITE_URL . '/themes/' . $active_theme . '/style.css'` (same pattern as the existing CSS reference) — do not introduce a new URL-construction approach for theme assets.
- Add the `SITE_URL` value to a `.env` or deployment checklist note, not just in a comment in `config.php`.

**Warning signs:**
- 404 errors on theme CSS after FTP upload but not in local Docker.
- Theme file loads locally but page is unstyled on Altervista.
- `SITE_URL` contains a trailing slash in one place and not another.

**Phase to address:** Deployment/configuration phase — verify Altervista URL before FTP deploy of themed version.

---

### Pitfall 6: Theme Name Stored as Free Text Without Strict Validation on Write

**What goes wrong:**
When the admin saves the active theme via the settings form, the theme name is written to the `settings` table as a plain string. If the validation on write uses only a length check (or no check at all), then a user who bypasses the HTML form — via a direct POST or Burp Suite — can store a malicious string. This feeds Pitfall 1 when the value is later read.

**Why it happens:**
The existing `settings.php` correctly uses an allowlist for `color_theme` (line 56–58: `$valid_themes = [...]`). But if the new "active file theme" is added as another settings field and a developer copies the form handler without also copying the allowlist validation, the DB value is unconstrained.

**How to avoid:**
Apply the same allowlist pattern that already exists in `settings.php` for `color_theme`:

```php
// On write (settings.php POST handler):
$installed_themes = getInstalledThemeNames(); // scans themes/ directory once
$posted_theme = $_POST['active_theme'] ?? 'default';
$values['active_theme'] = in_array($posted_theme, $installed_themes, true)
    ? $posted_theme
    : 'default';
```

`getInstalledThemeNames()` should return only directory names matching `/^[a-z0-9_-]+$/`. Never accept a theme name that is not in the scanned list.

**Warning signs:**
- The POST handler for the theme selector uses only `sanitize()` or `trim()` — no allowlist.
- The list of valid themes is hard-coded in the form HTML but not validated server-side.
- A direct POST with `active_theme=../../admin/includes/admin_header` does not get rejected.

**Phase to address:** Admin theme selection phase — this is part of the same form that handles `color_theme`; apply the existing pattern.

---

### Pitfall 7: Altervista FTP Deploy Leaves Site in Partially-Updated State

**What goes wrong:**
FTP (no shell access, no atomic deploy) uploads files one by one. If a page loads `themes/default/header.php` before that file has been uploaded, visitors get a fatal error or a half-styled page. The existing CI/CD (GitHub Actions FTP) deploys `public/` non-atomically; adding a `themes/` subdirectory with multiple files increases the window where partially-uploaded state is live.

**Why it happens:**
FTP has no equivalent of a database transaction or a symlink swap. There is no way to make a multi-file deploy atomic without shell access.

**How to avoid:**
- Deploy the fallback shim first in a separate smaller deploy, verify it works with the old templates still in place, then deploy the theme files.
- In the GitHub Actions workflow, order the FTP sync so that theme template files are uploaded before the shim that activates them, OR keep `active_theme` = `default` in DB until the upload completes (add a post-deploy step that sets the theme).
- The fallback loader (Pitfall 2) provides a safety net: if a theme file is missing, it falls back to `default`, which is always present.

**Warning signs:**
- The deploy workflow uploads `src/includes/theme.php` (the shim) before `themes/default/` is fully uploaded.
- No "deploy order" is specified in the GitHub Actions workflow for the theme files.

**Phase to address:** Deployment phase — update the GitHub Actions FTP workflow to enforce upload order.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Hard-code `active_theme = 'default'` during migration | Faster to ship, no DB change needed yet | Must retrofit DB-driven selection later; two code paths exist simultaneously | MVP only — must be removed before theme selection UI ships |
| Put theme name resolution directly in `header.php` instead of a dedicated helper | Less code, no new file | Path traversal check, fallback logic, and admin/public separation must all be redone in one place; hard to test | Never — the helper is a one-day task that prevents all the critical pitfalls |
| Copy-paste `header.php` and `footer.php` into each theme without abstracting the DB call | Simpler per-theme files | DB query for settings duplicated in every theme's header; inconsistencies accumulate across themes | Never — the DB call belongs in the shared bootstrap, not in each theme file |
| Use `include` instead of a fallback-aware wrapper for theme files | Slightly less code | `include` still produces a warning on missing file and continues with broken state; worse than `require` for diagnosing partial failures | Never — use the explicit fallback loader |

---

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| `settings` table (existing) | Add `active_theme` as a new free-text row without modifying the write validation | Extend the existing `settings.php` POST handler with the same allowlist pattern already used for `color_theme` |
| Existing `$GLOBALS['_vt_settings_loaded']` cache | Add `active_theme` to the same globals block in `header.php` — tempting because it already fetches settings | Keep the globals cache for display settings; resolve the theme file path in the new `theme.php` shim separately, so admin pages that load `header.php` indirectly do not trigger theme file resolution |
| GitHub Actions FTP deploy | Deploy the new `themes/` directory in the same FTP sync step as the shim, relying on alphabetical upload order | Explicitly order theme file upload before shim activation, or use a DB flag to switch themes only after upload confirms success |
| Altervista `.htaccess` | Forget to protect `themes/` subdirectory PHP files from direct browser access | Add `Options -Indexes` and consider a `.htaccess` deny rule in `themes/` for PHP files that are only meant to be `include`-d, not accessed directly |

---

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| DB query for `active_theme` on every page load | Extra round-trip per request; imperceptible now but compounds if other per-request queries are added | Cache in `$GLOBALS` (same pattern as `stable_name` / `color_theme`), loaded once in the shim | Not a real problem at this site's scale — but design it correctly from the start so there is no habit of per-request DB reads for config |
| `file_exists()` called on every theme file load | Filesystem stat per include; slow on some shared hosts | Resolve theme path once per request, store in a `$GLOBALS` variable; do not call `file_exists` inside a loop over page components | Shared host with slow disk I/O — Altervista may throttle frequent stat calls |
| `scandir()` on `themes/` directory on every page load to get installed themes list | Directory scan per page; unnecessary for public pages | Only scan `themes/` in the admin panel (theme selector), never on public page load | Any traffic level — it is simply unnecessary on public pages |

---

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Theme name from DB used in `require` without `realpath()` + prefix check | Path traversal: attacker who can write to `settings` table (via SQL injection elsewhere) can load any PHP file | Always use `resolveThemePath()` with `realpath()` and `strpos($target, $base) !== 0` guard (see Pitfall 1) |
| Theme files directly web-accessible (browser can `GET /themes/default/header.php`) | Partial HTML output or info disclosure; header.php without full page context may expose internal paths in error messages | Add `Options -Indexes` to `themes/.htaccess`; optionally add a guard at top of each theme file: `if (!defined('THEME_CONTEXT')) { http_response_code(403); exit; }` |
| Theme CSS/JS files served with wrong MIME type on Altervista | Browser refuses to apply stylesheet if served as `text/html` (Altervista sometimes mis-serves files in subdirectories) | Verify MIME type in browser DevTools after first FTP upload; add a `.htaccess` `AddType` rule if needed |
| `include_once` vs `require_once` used inconsistently in theme files | `include_once` silently skips if a file was already included — if theme header was already loaded via a fallback, the main require is silently skipped, causing subtle rendering bugs | Use `require` (not `require_once`) inside the fallback loader; the loader itself controls whether to load primary or fallback, never both |

---

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| No preview of theme before activating | Admin activates a theme that looks broken or unfinished on the live public site | Show a live preview link or a screenshot thumbnail in the theme selector; alternatively show "preview" as a separate action before "save" |
| Theme selector in admin lists directory names (`my-theme-v2`) instead of human-readable labels | Admin cannot tell which theme is which without opening each one | Require each theme to include a `theme.json` manifest with a `name` field; display the manifest name in the selector |
| No indication in admin that the currently active theme is missing a required template file | Admin changes theme; public site shows fallback default layout without warning | On theme activation, validate that the selected theme has all required template files; show a warning if any are missing |

---

## "Looks Done But Isn't" Checklist

- [ ] **Path traversal guard:** `resolveThemePath()` uses both `preg_match('/^[a-z0-9_-]+$/')` AND `realpath()` + prefix check — verify both branches are exercised in a test with `../../etc/passwd`
- [ ] **Fallback loader:** Every place in the code that loads a theme file uses the fallback-aware wrapper — grep for bare `require.*themes/` and confirm zero results
- [ ] **Admin isolation:** Admin pages (`admin/*.php`) do not include `theme.php` or any file that calls `resolveThemePath()` — confirm by tracing the include chain from `admin/settings.php`
- [ ] **Write-time validation:** The POST handler for active theme selection rejects any theme name not in `getInstalledThemeNames()` — test with a direct POST containing `active_theme=../../admin/index`
- [ ] **Default theme completeness:** `themes/default/` contains every template file that any page can request — a missing default fallback is as dangerous as a missing active theme file
- [ ] **FTP deploy order:** GitHub Actions workflow uploads `themes/default/` before uploading the shim `src/includes/theme.php` — inspect the workflow YAML step order
- [ ] **Altervista CSS URL:** Theme stylesheet URL resolves correctly on the live Altervista host — verify after first deploy by checking DevTools Network tab for 404s
- [ ] **Direct file access blocked:** `GET /themes/default/header.php` returns 403 or a non-HTML response — verify with browser or curl after deploy

---

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Path traversal exploited via DB | HIGH | 1. Immediately set `active_theme = 'default'` in DB via phpMyAdmin; 2. Audit `settings` table for unexpected values; 3. Deploy patched `resolveThemePath()` with `realpath()` guard; 4. Review access logs for suspicious theme names |
| White screen due to missing theme file | LOW | 1. FTP-upload the missing file; or 2. Set `active_theme = 'default'` in DB via phpMyAdmin — default theme always resolves |
| Migration broke existing pages (all pages 500) | MEDIUM | 1. Revert via FTP: re-upload the original `src/includes/header.php`; 2. Remove the shim; 3. Redo migration with shim-first strategy |
| Admin panel accidentally styled with public theme | LOW | 1. Remove theme initialisation from shared bootstrap; 2. Add `!defined('THEME_CONTEXT')` guard; 3. Redeploy — no data loss |
| Altervista CSS 404 after theme deploy | LOW | 1. Verify `SITE_URL` in `config.php` matches the actual Altervista URL; 2. FTP-upload corrected `config.php` |

---

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Path traversal via theme name from DB | Theme infrastructure (first phase) — build `resolveThemePath()` before any page uses it | Attempt direct POST with `active_theme=../../admin/index`; confirm it falls back to `default` |
| Missing theme file white screen | Theme infrastructure — build fallback loader before migrating templates | Delete one theme file; confirm public page still renders using default fallback |
| Breaking existing pages during migration | Migration sub-phase — shim first, templates second | Deploy shim with old templates still in place; confirm all pages work before moving templates |
| Admin panel accidentally themed | Theme infrastructure — enforce public/admin separation from the start | Load any admin page after theming is active; confirm admin CSS is unchanged |
| CSS URL broken on Altervista | Deployment phase — verify URL after first FTP deploy | Check DevTools Network on Altervista; confirm theme CSS returns 200 |
| Free-text theme name stored without allowlist | Admin UI phase — extend existing `settings.php` validation pattern | Direct POST with malicious theme name; confirm DB value remains `default` |
| Partial FTP deploy breaks site | Deployment phase — update GitHub Actions workflow step order | Monitor site during a test deploy; confirm no white screens during upload window |

---

## Sources

- Direct code inspection: `public/src/includes/header.php`, `public/src/includes/helpers.php`, `public/admin/settings.php`, `public/src/includes/db.php`, `public/src/includes/config.php`, `public/pages/hevoset.php`
- Established PHP security patterns: path traversal via `realpath()` + prefix check is the canonical PHP defence (OWASP Path Traversal cheatsheet)
- Existing codebase precedent: `validate_file_name()` in `helpers.php` already uses `strpos($filename, '..')` and `basename()` — same principle applied to theme names
- Altervista constraints: no shell access, FTP-only deploy, PHP 8.2, documented in `PROJECT.md`
- Existing allowlist pattern for `color_theme` in `settings.php` (lines 56–58) — the new theme selector must follow the same pattern

---
*Pitfalls research for: File-based theme system on PHP/MySQL app (Altervista)*
*Researched: 2026-06-22*
