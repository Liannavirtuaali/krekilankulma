# Research Summary: v1.1 Teemajärjestelmä

**Synthesized from:** STACK.md, FEATURES.md, ARCHITECTURE.md, PITFALLS.md
**Confidence:** HIGH — all findings derived from direct codebase inspection

---

## Executive Summary

File-based theme system for existing flat-PHP virtual stable site on Altervista (PHP 8.2, FTP-only). The existing codebase already has a CSS-variable color theme and a `settings` DB table — the new system extends both patterns. No new frameworks, Composer packages, or build steps needed: pure PHP with `require`, `glob()`, `realpath()`, and one new DB row.

**Approach:** shim-first migration — introduce `src/includes/theme.php` as central resolver, then migrate all 7 public page controllers to delegate HTML to `themes/default/pages/*.php` templates. Admin panel explicitly excluded throughout.

---

## Stack Additions

- **No new libraries.** PHP 8.2 built-ins: `glob()`, `realpath()`, `str_starts_with()`, `preg_replace`
- **DB `settings` row** (`active_theme`) — only correct storage; flat-file config risks write-permission failures on Altervista
- **One new helper file:** `src/includes/theme.php` — defines `THEME_PATH` and `THEME_URL` constants
- **Avoid:** Composer, Smarty/Twig/Blade, WordPress hook systems, `eval()`, storing absolute paths in DB

---

## Feature Landscape

### Table Stakes (must ship)
- `public/themes/default/` with all templates and CSS migrated from existing code
- `themeFile()` / `resolveThemePath()` helper with fallback-to-default
- All 7 public page controllers updated to use helper
- `active_theme` DB row in `settings` table
- Admin theme picker with `glob()` discovery and allowlist-validated save
- `theme.json` per theme (name, author — 10 lines, much better UX than raw directory names)

### Differentiators (include if time)
- Fallback indication in admin (show "using default" if active theme has missing files)

### Anti-features (explicitly out of scope)
- ZIP upload / remote install — path traversal risk + no shell on Altervista
- Second bundled theme — ship default first; custom themes created by file editing
- Theme preview thumbnails — nice but not required for MVP

---

## Architecture

```
public/
  themes/
    default/
      theme.json          ← {"name": "Oletus", "version": "1.0"}
      includes/           ← header.php, footer.php, nav.php
      pages/              ← hevoset.php, hevonen.php, ... (HTML only, no queries)
      assets/
        css/style.css     ← copy of current assets/css/style.css
src/
  includes/
    theme.php             ← NEW: defines THEME_PATH, THEME_URL constants
    db.php                ← unchanged (no theme init here)
    header.php            ← unchanged (admin uses this; not themed)
```

**Key integration points:**
- `src/includes/theme.php` required from public pages only — never from admin or bootstrap files
- Page controllers become data-only; HTML extracted to theme templates
- `THEME_PATH` for `require` calls, `THEME_URL` for HTML `<link>` and `<img>` src attributes
- `public/assets/css/style.css` kept in place — `admin_header.php` links to it directly

**Build order:**
1. DB migration + `theme.php` shim + `resolveThemePath()` helper
2. `public/themes/default/` scaffold (includes, CSS, theme.json) — no page changes yet
3. All 7 page controllers migrated to data-only + theme templates
4. Admin theme picker (`settings.php` extension)
5. Altervista verification (MIME types, CSS URLs, FTP upload order)

---

## Critical Pitfalls

| # | Pitfall | Prevention | Phase |
|---|---------|-----------|-------|
| 1 | Path traversal via theme name from DB | `preg_match('/^[a-z0-9_-]+$/i', $name)` + `realpath()` + prefix check in every `resolveThemePath()` call | 1 |
| 2 | Missing theme file → white screen | Fallback loader: `file_exists(THEME_PATH.'/pages/X.php') ?: THEME_PATH.'/default/pages/X.php'` | 1 |
| 3 | Pages broken during migration | Shim-first: introduce `theme.php`, verify all pages load, THEN move templates | 2→3 |
| 4 | Admin accidentally themed | Never put theme init in `db.php`/`config.php`/`helpers.php` | 1 |
| 5 | Free-text theme name saved unsanitized | `in_array($posted, getInstalledThemeNames(), true)` — extend existing `settings.php` allowlist pattern | 4 |
| 6 | CSS breaks after FTP deploy | Keep `public/assets/css/style.css` unchanged; theme CSS is additive | 2 |
| 7 | FTP partial-upload window | Deploy `themes/default/` before activating shim; fallback loader covers gap | 5 |

---

## Roadmap Implications

**Suggested phases:** 5

1. **Teema-infrastruktuuri** — `theme.php` shim, DB migration, `resolveThemePath()` with path-traversal guard and fallback, public/admin separation
2. **Oletusteman rakenne** — `public/themes/default/` scaffold; all includes, CSS, `theme.json`. Site stays identical.
3. **Sivukontrollerien migraatio** — All 7 controllers become data-only; HTML to theme templates
4. **Admin-teemavalinnan UI** — `glob()` discovery, `theme.json` display names, allowlist-validated save
5. **Altervista-verifiointi** — CSS MIME types, URLs, `.htaccess` direct-access protection, FTP upload order

---

## Open Questions for Planning

- Should `color_theme` (CSS palette) remain separate from file-based themes, or should each theme bundle its own fixed CSS? Affects how `data-theme` attribute is written inside theme `header.php`.
- Altervista CSS MIME type behavior for subdirectory `themes/*/assets/css/` — verify after first deploy (cannot resolve by research alone).
