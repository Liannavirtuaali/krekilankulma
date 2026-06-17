# Project State

**Project:** Virtuaalitalli
**Initialized:** 2026-06-17
**Current Phase:** Phase 4 — Tietoturva & Deploy (code complete)

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

## Security Implementation (Phase 4)

- **SEC-01** ✅ PDO prepared statements throughout
- **SEC-02** ✅ validate_email(), filter_var(URL) on admin forms
- **SEC-03** ✅ e() / htmlspecialchars() on all output
- **SEC-04** ✅ CSRF tokens on all POST forms (generate + validate helpers)
- **SEC-05** ✅ validate_image_upload() + generate_safe_filename() + .htaccess
- **SEC-06** ✅ .htaccess blocks direct access to includes/
- **SEC-07** ✅ Session hardening (httponly, secure, samesite, 30min timeout)
- **SEC-08** ✅ display_errors Off via .htaccess

## Next Action

Deploy to Altervista: see `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md`

---
*Last updated: 2026-06-17 — Phase 4 code complete, all 8 security requirements implemented*
