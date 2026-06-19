---
phase: "03"
plan: "03"
subsystem: admin-photos
tags: [admin, photos, upload, file-validation]
dependency_graph:
  requires: [03-01, 03-02]
  provides: [photo-upload, photo-delete]
  affects: [public/admin/photos.php, public/admin/photo_delete.php]
tech_stack:
  added: []
  patterns: [file-upload, finfo-mime-check, unique-filename]
key_files:
  created:
    - public/admin/photos.php
    - public/admin/photo_delete.php
  modified: []
decisions:
  - "finfo_file() MIME-tarkistus tiedostotyypin validointiin"
  - "Uniikki tiedostonimi uniqid+hash-generaatiolla"
  - "Max 5 kuvaa per hevonen"
metrics:
  duration: "~10 min"
  completed: "2026-06-17"
  tasks_completed: 3
  files_changed: 2
commit: "ea9d95c"
---

# Phase 03 Plan 03: Kuvien hallinta

**One-liner:** Kuvien lataus hevoselle MIME-tarkistuksella, uniikilla nimellä ja poisto-tuella.

## Tasks Completed

| Task | Commit | Files |
|------|--------|-------|
| Kuvan lataus + validointi | ea9d95c | public/admin/photos.php |
| Kuvan poisto | ea9d95c | public/admin/photo_delete.php |

## What Was Built

- photos.php: upload-lomake per hevonen, max 5 kpl, finfo_file() MIME-tarkistus, koon rajoitus
- photo_delete.php: poistaa tiedoston + DB-rivin, POST-only

## Self-Check: PASSED

Kaikki PHOTO-01–PHOTO-05 vaatimukset toteutettu.
