# Altervista Deployment Checklist — Virtuaalitalli v1.0

## Pre-Deployment

- [ ] All code committed and pushed to git (`git status` clean)
- [ ] Database schema at `database/schema.sql` tested locally
- [ ] Seed data at `database/seed.sql` available for test population
- [ ] `.planning/` directory and security audit documents committed

---

## Security Verification (Complete before deploy)

| Task | Requirement | Status |
|------|-------------|--------|
| 04-01 (SEC-01) | All DB queries use PDO prepared statements | ✅ Done — PDO throughout |
| 04-02 (SEC-02) | Server-side input validation on all forms | ✅ Done — validate_email(), filter_var(URL) |
| 04-03 (SEC-03) | All output escaped with e() / htmlspecialchars() | ✅ Done — e() used everywhere |
| 04-04 (SEC-04) | CSRF tokens on all POST forms, validated | ✅ Done — generate_csrf_token() / validate_csrf_token() |
| 04-05 (SEC-05) | Image upload validates MIME, blocks PHP, size limits | ✅ Done — validate_image_upload() + .htaccess |
| 04-06 (SEC-06) | DB credentials protected, .htaccess in place | ✅ Done — .htaccess blocks src/includes/ |
| 04-07 (SEC-07) | Sessions httponly, secure (HTTPS), samesite, 30 min timeout | ✅ Done — db.php session hardening |
| 04-08 (SEC-08) | Error display off, no DB details shown to users | ✅ Done — .htaccess php_flag display_errors Off |

---

## Altervista-Specific Configuration

- [ ] Log in to Altervista control panel
- [ ] PHP version confirmed: 8.x (check via phpinfo.php, then delete it)
- [ ] MySQL database created in Altervista panel
- [ ] Update `public/src/includes/config.php`:
  - `DB_HOST` → Altervista MySQL host
  - `DB_NAME` → database name from Altervista panel
  - `DB_USER` → Altervista MySQL user
  - `DB_PASS` → Altervista MySQL password
  - `SITE_URL` → `https://yoursite.altervista.org`
- [ ] Import `database/schema.sql` via Altervista phpMyAdmin
- [ ] (Optional) Import `database/seed.sql` for test horses
- [ ] Upload `public/` directory contents via FTP to Altervista web root
- [ ] Upload directory exists and is writable: `uploads/` with permissions 755
- [ ] HTTPS enabled (Altervista offers free Let's Encrypt SSL)
- [ ] Verify `session.cookie_secure` works under HTTPS

---

## Pre-Go-Live Testing (on Altervista)

- [ ] Public homepage loads: `https://yoursite.altervista.org/`
- [ ] Horse list page loads with no PHP errors
- [ ] Individual horse page loads (including pedigree)
- [ ] Admin login works: `https://yoursite.altervista.org/admin/login.php`
- [ ] Add a test horse → verify appears in list
- [ ] Edit the test horse → verify changes saved
- [ ] Delete the test horse → verify soft-deleted (not visible)
- [ ] Upload a test photo → verify image displays
- [ ] Delete the test photo → verify removed
- [ ] Add a competition entry → verify appears
- [ ] Logout → verify redirected to login

---

## Security Spot-Checks on Altervista

- [ ] Direct access to `config.php` returns 403 (not PHP source)
- [ ] Direct access to `src/includes/db.php` returns 403
- [ ] Try uploading `test.php` as a photo → should be rejected
- [ ] Submit horse add form without CSRF token → should show error
- [ ] Try accessing admin pages without login → should redirect to login
- [ ] Check browser dev tools: session cookie has `HttpOnly` and `Secure` flags
- [ ] Check that error messages don't reveal database structure

---

## Post-Deployment

- [ ] Monitor Altervista error logs for first 48 hours
- [ ] Share URL with test users for feedback
- [ ] Document any issues found in `.planning/phases/04-tietoturva-deploy/`
- [ ] Set up regular database backups (Altervista backup feature or manual export)
- [ ] Update `STATE.md` to mark Phase 4 complete

---

*Checklist version: v1.0 — created 2026-06-17*
