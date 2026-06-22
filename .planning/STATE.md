---
gsd_state_version: 1.0
milestone: v1.1
milestone_name: Teemajärjestelmä
status: planning
last_updated: "2026-06-22T14:19:15.732Z"
last_activity: 2026-06-22
progress:
  total_phases: 0
  completed_phases: 0
  total_plans: 0
  completed_plans: 0
  percent: 0
---

# Project State

**Project:** Virtuaalitalli
**Initialized:** 2026-06-17
**Current Phase:** 04

## Workflow Status

| Phase | Status | Started | Completed |
|-------|--------|---------|-----------|
| Phase 1 — Perusta & DB | ✅ Complete | 2026-06-17 | 2026-06-17 |
| Phase 2 — Julkiset sivut | ✅ Complete | 2026-06-17 | 2026-06-17 |
| Phase 3 — Admin-paneeli | ✅ Complete | 2026-06-17 | 2026-06-17 |
| Phase 4 — Tietoturva & Deploy | ✅ Code complete | 2026-06-17 | 2026-06-17 |

## Configuration

- **Mode:** YOLO
- **Granularity:** Coarse
- **Research:** Disabled
- **Plan Check:** Enabled
- **Verifier:** Enabled
- **Git tracking:** Enabled

## Active Decisions

- Admin-paneeli: kirjautuminen bcrypt+CSRF, pehmeä poisto hevosille, kuvat finfo_file() MIME-tarkistuksella
- Slug generoidaan automaattisesti nimestä (slugify), duplikaatit saavat numeron loppuun
- horse_edit.php estää itse-viittauksen sire/dam dropdowneissa
- Kaikki 8 tietoturvavaatimusta (SEC-01–SEC-08) toteutettu koodissa
- CI/CD: SamKirkland/FTP-Deploy-Action@v4 deployaa public/ Altervistaan push main -triggerillä; FTP-tunnukset GitHub Secretseissä

## Security Implementation (Phase 4)

- **SEC-01** ✅ PDO prepared statements throughout
- **SEC-02** ✅ validate_email(), filter_var(URL) on admin forms
- **SEC-03** ✅ e() / htmlspecialchars() on all output
- **SEC-04** ✅ CSRF tokens on all POST forms (generate + validate helpers)
- **SEC-05** ✅ validate_image_upload() + generate_safe_filename() + .htaccess
- **SEC-06** ✅ .htaccess blocks direct access to includes/
- **SEC-07** ✅ Session hardening (httponly, secure, samesite, 30min timeout)
- **SEC-08** ✅ display_errors Off via .htaccess

## CI/CD Deployment (Phase 4 Plan 2)

- **Workflow:** `.github/workflows/deploy.yml` — GitHub Actions FTP deploy on push to main
- **Action:** SamKirkland/FTP-Deploy-Action@v4
- **Scope:** Only `public/` → Altervista web root (`/`)
- **Setup required:** Add `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD` as GitHub repository secrets

## Next Action

Add GitHub Secrets (FTP_HOST, FTP_USERNAME, FTP_PASSWORD) and push to main to trigger first deploy.
See `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md` for full instructions.

---
*Last updated: 2026-06-18 — Phase 4 Plan 2 complete: GitHub Actions CI/CD deployment workflow added*

## Current Position

Phase: Not started (defining requirements)
Plan: —
Status: Defining requirements
Last activity: 2026-06-22 — Milestone v1.1 started
