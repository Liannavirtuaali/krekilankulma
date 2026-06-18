---
phase: 04-tietoturva-deploy
verified: 2026-06-18T23:00:00Z
status: human_needed
score: 4/5 must-haves verified
overrides_applied: 0
re_verification: false
gaps: []
human_verification:
  - test: "Verify site works correctly in Altervista production environment"
    expected: "Admin login works, horse CRUD works, photo upload works, public pages display correctly, HTTPS is active, no error details shown to users"
    why_human: "Requires a live Altervista account, FTP access, database import, and browser-based functional and security testing — cannot be verified from the local codebase"
  - test: "Confirm FTP deploy uses encrypted transport (FTPS)"
    expected: "Either protocol: ftps is added to deploy.yml, or Altervista FTP is confirmed to always negotiate TLS. Credentials should not transit in cleartext."
    why_human: "The code review (04-REVIEW.md CR-01) found that SamKirkland/FTP-Deploy-Action defaults to plain FTP. Verifying whether Altervista's FTP server transparently upgrades to TLS, and whether to add 'protocol: ftps', requires testing against the live Altervista FTP endpoint."
  - test: "Confirm server-dir value (/  vs /htdocs/) is correct for this Altervista account"
    expected: "After a test push to main, deployed files appear at the correct web root and the site is accessible at the Altervista URL"
    why_human: "The checklist documents the ambiguity (/ vs /htdocs/). The correct path can only be verified by FTP login or by inspecting the live deploy result."
  - test: "Verify production uploads directory is not overwritten by CI/CD deploys"
    expected: "User-uploaded photos uploaded via the admin panel persist across git pushes; the deploy workflow's exclude list contains **/uploads/** to prevent the FTP action from deleting remote-only files"
    why_human: "Code review WR-02 found that public/uploads/ is not excluded from the deploy scope. If SamKirkland/FTP-Deploy-Action uses dangerous-clean-slate mode (which removes remote files not present locally), all production uploads would be deleted on next push. Must be resolved before first production use."
---

# Phase 4: Tietoturva & Viimeistely Verification Report

**Phase Goal:** Kaikki OWASP-tietoturvakohteet toteutettu ja koko sivusto on valmis julkaistavaksi Altervistaan automatisoidulla CI/CD-deploymentilla.
**Verified:** 2026-06-18
**Status:** human_needed
**Re-verification:** No — initial verification

---

## Goal Achievement

### Observable Truths

Verified against ROADMAP Phase 4 Success Criteria and 04-02-PLAN.md must_haves.

| # | Truth | Status | Evidence |
|---|-------|--------|----------|
| 1 | All DB queries use PDO prepared statements; all output protected with htmlspecialchars() (e()) | VERIFIED | `db.php` configures `ATTR_EMULATE_PREPARES => false` and `ERRMODE_EXCEPTION`. `helpers.php` defines `e()` as `htmlspecialchars(ENT_QUOTES, UTF-8)`. All admin pages (`horses.php`, `horse_add.php`, `horse_edit.php`, `photos.php`, `competitions.php`, `foals.php`, `login.php`, etc.) and all public pages (`hevonen.php`, `hevoset.php`, `kasvatus.php`, etc.) use `e()` on dynamic output. PDO prepare/execute used throughout. |
| 2 | All forms contain CSRF token and server validates it | VERIFIED | `generate_csrf_token()` and `validate_csrf_token()` (using `hash_equals`) exist in `helpers.php`. CSRF token present in forms and `validate_csrf_token()` called in POST handlers for: `login.php`, `horse_add.php`, `horse_edit.php`, `horse_delete.php`, `competitions.php`, `foals.php`, `photos.php`, `photo_delete.php`, `logout.php`. |
| 3 | Photo upload rejects PHP files and wrong MIME types | VERIFIED | `validate_image_upload()` in `helpers.php` checks MIME via `finfo_file()`, validates extension against allowlist, explicitly blocks PHP extensions. `generate_safe_filename()` produces randomized filenames. Both are wired into `photos.php`. `public/uploads/.htaccess` blocks PHP execution with `<FilesMatch "\.ph(p[3-7]?|ar|ps)$">`. |
| 4 | Site works correctly in Altervista production environment | UNCERTAIN | Cannot verify without live Altervista account. Requires human testing. |
| 5 | Push to main automatically deploys only public/ to Altervista via GitHub Actions FTP | VERIFIED | `.github/workflows/deploy.yml` exists. Triggers on `push: branches: [main]` only. Uses `SamKirkland/FTP-Deploy-Action@v4`. `local-dir: ./public/`. Credentials via `${{ secrets.FTP_HOST }}`, `${{ secrets.FTP_USERNAME }}`, `${{ secrets.FTP_PASSWORD }}` — nothing hardcoded. Defensive exclude list present. No `workflow_dispatch` trigger (per plan spec). |

**Score: 4/5 truths verified** (1 uncertain, requires human verification)

---

### Required Artifacts

| Artifact | Expected | Status | Details |
|----------|----------|--------|---------|
| `.github/workflows/deploy.yml` | GitHub Actions FTP deploy workflow triggering on push to main | VERIFIED | File exists, 31 lines, valid YAML, all required elements present |
| `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md` | Checklist v1.1 with CI/CD section, GitHub Secrets setup, and security verification table | VERIFIED | Version v1.1 confirmed, "Automated Deployment (GitHub Actions)" section present, all three secrets documented with sources, manual FTP step struck through with reference to deploy.yml |
| `public/src/includes/helpers.php` | CSRF functions, validation helpers, upload validation | VERIFIED | Contains `generate_csrf_token()`, `validate_csrf_token()`, `validate_string()`, `validate_email()`, `validate_integer()`, `validate_file_name()`, `generate_safe_filename()`, `validate_image_upload()`, `e()` |
| `public/src/includes/db.php` | Session hardening + PDO with ERRMODE_EXCEPTION and EMULATE_PREPARES=false | VERIFIED | Session started with `cookie_httponly=true`, `cookie_samesite=Strict`, `cookie_secure` (production-conditional), `gc_maxlifetime=1800`; PDO configured correctly |
| `public/.htaccess` | `display_errors Off`, `display_startup_errors Off` | VERIFIED | Lines 30-31 confirm both flags set to Off |
| `public/uploads/.htaccess` | PHP execution blocked in uploads directory | VERIFIED | `<FilesMatch "\.ph(p[3-7]?|ar|ps)$"> Deny from all` |
| `public/src/includes/.htaccess` | Direct access to config.php and db.php blocked | VERIFIED | Both files covered by `<Files>` Deny rules |

---

### Key Link Verification

| From | To | Via | Status | Details |
|------|----|-----|--------|---------|
| `.github/workflows/deploy.yml` | GitHub Secrets | `${{ secrets.FTP_HOST/USERNAME/PASSWORD }}` | VERIFIED | Lines 19-21 of deploy.yml use secrets references exclusively |
| `.github/workflows/deploy.yml` | `public/` directory only | `local-dir: ./public/` | VERIFIED | Line 22 confirmed |
| `helpers.php` CSRF functions | All POST admin forms | `generate_csrf_token()` in form HTML + `validate_csrf_token()` in POST handler | VERIFIED | Confirmed in login.php, horse_add.php, horse_edit.php, horse_delete.php, competitions.php, foals.php, photos.php, photo_delete.php, logout.php |
| `helpers.php` `validate_image_upload()` | `photos.php` upload handler | Called on `$_FILES['photo']` before move_uploaded_file | VERIFIED | Line 35 of photos.php |
| `db.php` session settings | All pages (via auto-include) | `session_start(['cookie_httponly' => true, ...])` | VERIFIED | db.php is required by every page via `require_once db.php` |

---

### Data-Flow Trace (Level 4)

Not applicable — this phase produces security hardening (functions, configuration) and a CI/CD workflow, not new data-rendering components.

---

### Behavioral Spot-Checks

| Behavior | Command | Result | Status |
|----------|---------|--------|--------|
| deploy.yml triggers on push to main only | `grep -n 'push\|branches\|workflow_dispatch' .github/workflows/deploy.yml` | Found `push: branches: [main]`; no `workflow_dispatch` | PASS |
| Credentials not hardcoded in workflow | `grep -E 'ftp\.(altervista|org)\|password.*=.*[a-zA-Z0-9]{6}' .github/workflows/deploy.yml` | No matches | PASS |
| `local-dir` scoped to public/ | Content of deploy.yml line 22 | `local-dir: ./public/` | PASS |
| CSRF validation uses hash_equals | Read helpers.php lines 150-155 | `return hash_equals($_SESSION['csrf_token'], $token ?? '')` confirmed | PASS |
| PHP execution blocked in uploads | Content of uploads/.htaccess | `<FilesMatch "\.ph(p[3-7]?...)"> Deny from all` | PASS |
| Session cookie_httponly set | Grep db.php for cookie_httponly | `'cookie_httponly' => true` on line 76 | PASS |
| display_errors Off in production | Grep public/.htaccess | `php_flag display_errors Off` on line 30 | PASS |
| PDO uses real prepared statements | Grep db.php for ATTR_EMULATE_PREPARES | `PDO::ATTR_EMULATE_PREPARES => false` on line 41 | PASS |

---

### Probe Execution

No probes defined for this phase. Step 7c: SKIPPED — no `scripts/*/tests/probe-*.sh` files exist for phase 04.

---

### Requirements Coverage

All 8 security requirements mapped to Phase 4 in REQUIREMENTS.md. The `04-02-PLAN.md` declares `requirements: []` (it covers only the CI/CD deploy task), while `PLAN.md` (the security audit plan) and `PLAN-SUMMARY.md` front matter declare SEC-01 through SEC-08. The PLAN-SUMMARY.md is the authoritative summary for the security plan, covering all 8 IDs.

| Requirement | Description | Status | Evidence |
|-------------|-------------|--------|----------|
| SEC-01 | PDO prepared statements throughout | SATISFIED | `db.php` ATTR_EMULATE_PREPARES=false; all admin and public pages use `prepare()/execute()` |
| SEC-02 | Server-side input validation before storage | SATISFIED | `validate_email()`, `validate_string()`, `sanitize()` in helpers.php; wired in horse_add.php, horse_edit.php |
| SEC-03 | htmlspecialchars() on all HTML output | SATISFIED | `e()` helper present in helpers.php; used across all 21 admin files and 7 public page files |
| SEC-04 | CSRF tokens on all forms, server validates | SATISFIED | `generate_csrf_token()`/`validate_csrf_token()` in helpers.php; wired in all 9+ admin POST handlers |
| SEC-05 | Image upload validates MIME + rejects PHP | SATISFIED | `validate_image_upload()` checks finfo MIME + extension allowlist + PHP blocklist; `uploads/.htaccess` adds defense-in-depth |
| SEC-06 | DB credentials not in web root or protected | SATISFIED | `src/includes/.htaccess` blocks direct HTTP access to `config.php` and `db.php`; credentials use env vars with Altervista fallback |
| SEC-07 | Admin session uses httponly, secure, samesite | SATISFIED | `db.php` session_start with `cookie_httponly=true`, `cookie_samesite=Strict`, `cookie_secure=$isProduction`; `session_regenerate_id(true)` on login |
| SEC-08 | Error messages don't expose DB/directory structure | SATISFIED | `public/.htaccess` sets `php_flag display_errors Off`; `db.php` catch block uses `error_log()` + generic user message; no `display_errors On` override found anywhere |

**Note on PLAN.md vs 04-02-PLAN.md requirements field:** `04-02-PLAN.md` has `requirements: []` because it covers only the CI/CD workflow task (no SEC requirements). The security requirements (SEC-01 to SEC-08) are covered by `PLAN.md` / `PLAN-SUMMARY.md` (the security audit plan, completed 2026-06-17). Both plans together satisfy Phase 4's full requirement set.

---

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|------|------|---------|----------|--------|
| `.github/workflows/deploy.yml` | 17 | `SamKirkland/FTP-Deploy-Action@v4` — mutable tag, not SHA-pinned | WARNING | Supply chain risk: tag owner can redirect to malicious commit. No formal follow-up issue referenced. |
| `.github/workflows/deploy.yml` | 17-22 | No `protocol: ftps` — FTP defaults to unencrypted transport | WARNING | FTP credentials and file content transmitted in cleartext. Documented in 04-REVIEW.md CR-01. No follow-up issue reference. |
| `.github/workflows/deploy.yml` | 22-30 | `public/uploads/` not excluded from deploy scope | WARNING | If FTP action ever uses clean-slate mode, all production-uploaded photos would be deleted on next push. Documented in 04-REVIEW.md WR-02. |

No `TBD`, `FIXME`, or `XXX` markers found in any phase 04 artifacts.

The `$flash` variable in `horses.php` at line 31 (`<?= $flash ?>`) is hardcoded HTML strings (not user input), set conditionally based on GET param presence. This is safe — not a stub or XSS vector.

---

### Human Verification Required

The following items cannot be verified from the codebase alone and require a human to test against the live Altervista environment.

#### 1. Altervista Production Deployment

**Test:** Follow DEPLOYMENT_CHECKLIST.md: configure GitHub Secrets (FTP_HOST, FTP_USERNAME, FTP_PASSWORD), push to main, verify the GitHub Actions run completes, confirm the site is accessible at the Altervista URL.
**Expected:** GitHub Actions workflow completes with exit 0; public pages load; admin login works; horse CRUD works; photo upload works; no PHP error details shown to users.
**Why human:** Requires live Altervista hosting account, database import, and FTP credentials that cannot be provided in CI.

#### 2. FTP Transport Encryption

**Test:** Verify the FTP deploy uses encrypted transport. Either add `protocol: ftps` to deploy.yml and confirm the action connects successfully, or verify that Altervista's FTP server negotiates TLS automatically.
**Expected:** FTP credentials are never transmitted in cleartext; deploy.yml should include `protocol: ftps` (or `protocol: sftp` if Altervista supports SFTP).
**Why human:** The code review (04-REVIEW.md CR-01) identified that `SamKirkland/FTP-Deploy-Action@v4` defaults to plain FTP. Whether Altervista auto-upgrades to TLS requires a live connection test. This must be resolved before the first production deploy to avoid credential exposure.

#### 3. Correct server-dir Path for Altervista Web Root

**Test:** Log in to Altervista FTP manually and confirm whether the web root is `/` or `/htdocs/`. Update deploy.yml `server-dir` accordingly before the first automated deploy.
**Expected:** After a push to main, deployed files are served at `https://yoursite.altervista.org/` — the site is accessible, not returning 404.
**Why human:** The checklist documents this ambiguity; resolution requires inspecting the live FTP directory structure.

#### 4. Production Uploads Persistence (WR-02 Resolution)

**Test:** Add `**/uploads/**` to the exclude list in deploy.yml. Verify that after adding a horse photo via the admin panel on production and then pushing a code change to main, the photo still exists on the production server after the deploy completes.
**Expected:** `public/uploads/` is excluded from the FTP sync; production user-uploaded photos are never deleted by a CI/CD push.
**Why human:** Requires running a deploy against the live server and checking that remote-only files (production uploads) are not deleted. Must be fixed before first production use.

---

### Gaps Summary

No hard blockers were found in the codebase implementation. All 8 security requirements (SEC-01 through SEC-08) have verifiable implementation in the codebase. The CI/CD workflow and deployment checklist are correctly structured per plan spec.

Four items route to human verification before the phase can be marked complete:

1. **Live Altervista validation** — ROADMAP SC-4 explicitly requires the site to work in production. Cannot be verified from the codebase.
2. **FTP plaintext transport** — The deploy workflow does not specify `protocol: ftps`, meaning credentials transit in cleartext. This contradicts the "security hardening" goal and must be addressed before the first production deploy.
3. **server-dir path ambiguity** — Must be confirmed against the live Altervista account before the workflow can be relied upon.
4. **uploads/ exclusion** — Missing from the exclude list; could cause production photos to be deleted on next push. Must be added to deploy.yml.

Items 2, 3, and 4 can all be resolved in a single deploy.yml edit before the first real push.

---

_Verified: 2026-06-18T23:00:00Z_
_Verifier: Claude (gsd-verifier)_
