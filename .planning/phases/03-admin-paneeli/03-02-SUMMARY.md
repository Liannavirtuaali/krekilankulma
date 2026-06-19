---
phase: "03"
plan: "02"
subsystem: admin-horses
tags: [admin, horses, crud, soft-delete]
dependency_graph:
  requires: [03-01]
  provides: [horse-crud, soft-delete]
  affects: [public/admin/horses.php, public/admin/horse_add.php, public/admin/horse_edit.php, public/admin/horse_delete.php]
tech_stack:
  added: []
  patterns: [pdo-prepared-statements, soft-delete, form-validation]
key_files:
  created:
    - public/admin/horses.php
    - public/admin/horse_add.php
    - public/admin/horse_edit.php
    - public/admin/horse_delete.php
  modified: []
decisions:
  - "Pehmeä poisto: is_deleted=1 eikä DELETE FROM"
  - "horse_edit.php estää itse-viittauksen sire/dam dropdowneissa"
metrics:
  duration: "~10 min"
  completed: "2026-06-17"
  tasks_completed: 4
  files_changed: 4
commit: "a766060"
---

# Phase 03 Plan 02: Hevosten hallinta

**One-liner:** Hevosten CRUD-hallinta — lista, lisäys, muokkaus ja pehmeä poisto (is_deleted=1).

## Tasks Completed

| Task | Commit | Files |
|------|--------|-------|
| Hevosten listaus | a766060 | public/admin/horses.php |
| Hevosen lisäys | a766060 | public/admin/horse_add.php |
| Hevosen muokkaus | a766060 | public/admin/horse_edit.php |
| Hevosen poisto (pehmeä) | a766060 | public/admin/horse_delete.php |

## What Was Built

- horses.php: lista kaikista hevosista (is_deleted=0), linkit add/edit/delete
- horse_add.php: lomake kaikkine kenttineen + INSERT
- horse_edit.php: esitäytetty lomake + UPDATE, sire/dam dropdown ilman itseosoitusta
- horse_delete.php: POST-only, asettaa is_deleted=1

## Self-Check: PASSED

Kaikki ADMIN-01–ADMIN-05 vaatimukset toteutettu.
