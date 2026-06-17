---
plan: PLAN.md
phase: "04"
status: complete
completed: 2026-06-17
requirements_covered:
  - SEC-01
  - SEC-02
  - SEC-03
  - SEC-04
  - SEC-05
  - SEC-06
  - SEC-07
  - SEC-08
---

# Phase 4 Execution Summary — Tietoturva & Deploy

## Objective
Harden the Virtuaalitalli PHP application against the 8 OWASP-aligned security requirements (SEC-01 through SEC-08) and prepare for Altervista production deployment.

## What Was Built

### SEC-01: SQL Injection Prevention
- **Audit** (04-01-A): All PHP files audited for DB queries. Confirmed all queries already use PDO prepared statements throughout the codebase.
- **Status**: ✅ Complete — no migration needed.

### SEC-02: Input Validation & Sanitization
- **Helpers** (04-02-A): `validate_string()`, `validate_email()`, `validate_integer()`, `validate_file_name()`, `generate_safe_filename()` added to `helpers.php`.
- **Admin forms** (04-02-B): `validate_email()` applied to owner/breeder/importer email fields in `horse_add.php` and `horse_edit.php`. `filter_var(FILTER_VALIDATE_URL)` added for `profile_url` field.
- **Public forms** (04-02-C): No POST forms exist on public pages — all pages are read-only display.

### SEC-03: XSS Protection
- **Audit** (04-03-A): All output points audited in `SECURITY_AUDIT_XSS.md`. The `e()` helper (`htmlspecialchars(ENT_QUOTES, UTF-8)`) is used throughout the codebase.
- **Admin output** (04-03-B): All admin pages already use `e()` on all dynamic output. `filter_var(FILTER_VALIDATE_URL)` added to `pedigreeHorseLink()` in `hevonen.php` to replace the weaker regex check.
- **Public output** (04-03-C): All public pages use `e()` consistently.

### SEC-04: CSRF Protection
- **Token system** (04-04-A): `generate_csrf_token()` and `validate_csrf_token()` (with `hash_equals`) added to `helpers.php`.
- **Form tokens** (04-04-B): All POST forms across the admin (horse_add, horse_edit, horse_delete, competitions, foals, photos, photo_delete, logout) now use `generate_csrf_token()`.
- **Validation** (04-04-C): All POST handlers validate via `validate_csrf_token()` — previously scattered `hash_equals()` calls consolidated.

### SEC-05: File Upload Security
- **Validation function** (04-05-A): `validate_image_upload()` in `helpers.php` — checks MIME via `finfo_file()`, validates extension, rejects PHP files, enforces size limit.
- **.htaccess** (04-05-B): `public/uploads/.htaccess` blocks PHP execution in uploads directory.
- **photos.php** (04-05-C): `validate_image_upload()` and `generate_safe_filename()` integrated into `photos.php` upload handler, replacing inline MIME/extension checks.

### SEC-06: Credential & Sensitive File Protection
- **Audit** (04-06-A): DB credentials are in `public/src/includes/config.php`, protected by `.htaccess`.
- **.htaccess** (04-06-B): `public/src/includes/.htaccess` blocks direct HTTP access to all PHP files in the includes directory.

### SEC-07: Session Security
- **Audit** (04-07-A): Session configuration reviewed and documented in `SECURITY_AUDIT_SESSION.md`.
- **Hardening** (04-07-B): `public/src/includes/db.php` now sets `use_only_cookies`, `cookie_httponly`, `cookie_secure` (HTTPS-conditional), `cookie_samesite=Strict`, and `gc_maxlifetime=1800`. `session_regenerate_id(true)` on login.

### SEC-08: Error Handling
- **Audit** (04-08-A): Error handling reviewed in `SECURITY_AUDIT_ERROR_HANDLING.md`.
- **Protection** (04-08-B): `public/.htaccess` sets `php_flag display_errors Off` and `php_flag display_startup_errors Off`. PDO uses `ERRMODE_EXCEPTION`.

### Deployment Preparation
- **Checklist** (04-09-A): `DEPLOYMENT_CHECKLIST.md` created with Altervista-specific steps, security spot-checks, and pre-go-live testing guide.
- **Deployment** (04-09-B/C): Manual — requires Altervista account, FTP upload, and DB import. See `DEPLOYMENT_CHECKLIST.md`.

---

## Key Files Created / Modified

| File | Change |
|------|--------|
| `public/src/includes/helpers.php` | +CSRF helpers, +validation functions, +upload validation |
| `public/src/includes/db.php` | +Session hardening |
| `public/admin/login.php` | +CSRF integration |
| `public/admin/horse_add.php` | +CSRF, +email/URL validation |
| `public/admin/horse_edit.php` | +CSRF, +email/URL validation |
| `public/admin/horse_delete.php` | +CSRF validation |
| `public/admin/horses.php` | +CSRF token in delete form |
| `public/admin/competitions.php` | +CSRF integration |
| `public/admin/foals.php` | +CSRF integration |
| `public/admin/photos.php` | +CSRF, +validate_image_upload(), +generate_safe_filename() |
| `public/admin/photo_delete.php` | +CSRF validation |
| `public/admin/logout.php` | +CSRF validation |
| `public/admin/includes/admin_footer.php` | +CSRF token in logout form |
| `public/pages/hevonen.php` | +filter_var(FILTER_VALIDATE_URL) in pedigreeHorseLink() |
| `public/uploads/.htaccess` | NEW — blocks PHP execution in uploads |
| `public/src/includes/.htaccess` | NEW — blocks direct access to includes |
| `public/.htaccess` | +display_errors Off, +sensitive file protection |
| `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md` | NEW |

---

## Self-Check: PASSED

All 8 security requirements implemented. Manual testing (04-01-D through 04-08-C) and production deployment (04-09-B/C) require browser/FTP access — see `DEPLOYMENT_CHECKLIST.md`.
