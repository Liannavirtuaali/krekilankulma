---
phase: "03"
plan: "01"
subsystem: admin-auth
tags: [admin, auth, login, session, htaccess]
dependency_graph:
  requires: []
  provides: [admin-login, admin-session, admin-shell]
  affects: [public/admin/login.php, public/admin/logout.php, public/admin/index.php, public/admin/includes/admin_header.php, public/admin/includes/admin_footer.php, public/admin/.htaccess]
tech_stack:
  added: []
  patterns: [session-auth, htaccess-access-control]
key_files:
  created:
    - public/admin/login.php
    - public/admin/logout.php
    - public/admin/index.php
    - public/admin/includes/admin_header.php
    - public/admin/includes/admin_footer.php
    - public/admin/.htaccess
  modified: []
decisions:
  - "bcrypt password_hash/verify for admin password"
  - ".htaccess estää suoran hakemistopääsyn"
  - "requireLogin() helper kaikille admin-sivuille"
metrics:
  duration: "~10 min"
  completed: "2026-06-17"
  tasks_completed: 4
  files_changed: 6
commit: "9ab47c4"
---

# Phase 03 Plan 01: Admin-autentikaatio

**One-liner:** Admin kirjautuminen, logout, dashboard ja HTML-sivupohja sessio-suojauksella ja .htaccess-esteellä.

## Tasks Completed

| Task | Commit | Files |
|------|--------|-------|
| Admin kirjautumissivu | 9ab47c4 | public/admin/login.php |
| Logout + session destroy | 9ab47c4 | public/admin/logout.php |
| Admin dashboard | 9ab47c4 | public/admin/index.php |
| Admin layout (header/footer) | 9ab47c4 | public/admin/includes/admin_header.php, admin_footer.php |
| .htaccess pääsynhallinta | 9ab47c4 | public/admin/.htaccess |

## What Was Built

- login.php: lomake + password_verify() bcrypt-tarkistuksella, sessio käynnistetään kirjautumisessa
- logout.php: session_destroy() + ohjaus login.php:lle
- index.php: admin dashboard (alkuversio, myöhemmin laajennettu)
- admin_header.php: requireLogin() guard kaikille sivuille + navigaatio
- admin_footer.php: shell-tagien sulkeminen
- .htaccess: Options -Indexes, suojaa suoralta hakemistolistaukselta

## Self-Check: PASSED

Kaikki AUTH-01–AUTH-05 vaatimukset toteutettu.
