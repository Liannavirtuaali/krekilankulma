---
phase: "05"
plan: "01-05"
subsystem: blog
tags: [blog, posts, crud, public-pages, navigation]
dependency_graph:
  requires: []
  provides: [posts-table, blog-css, public-blog-pages, admin-blog-crud, frontpage-integration]
  affects: [index.php, nav.php, style.css]
tech_stack:
  added: []
  patterns: [pdo-prepared-statements, csrf-validation, slug-collision-loop, graceful-degradation]
key_files:
  created:
    - database/migrate_posts.sql
    - public/pages/blogi.php
    - public/pages/postaus.php
    - public/admin/posts.php
    - public/admin/post_delete.php
  modified:
    - public/assets/css/style.css
    - public/admin/includes/admin_header.php
    - public/pages/index.php
    - public/src/includes/nav.php
decisions:
  - "CSS-only accordion in archive sidebar using HTML details/summary"
  - "Finnish month names as PHP array instead of deprecated strftime()"
  - "try/catch PDOException on index.php for graceful degradation"
  - "Hard delete in post_delete.php per plan spec"
metrics:
  duration: "~25 min"
  completed: "2026-06-18"
  tasks_completed: 8
  files_changed: 9
---

# Phase 05 Plans 01-05: Blog Feature Summary

**One-liner:** Blogiominaisuus: posts-taulu migraatiolla, julkiset blogi/postaus-sivut sticky sidebar -arkistolla, admin CRUD CSRF+slug-suojalla, etusivun dynaaminen overlay-kortti ja Ajankohtaista-navigointilinkki.

## Tasks Completed

| Plan | Task | Commit | Files |
|------|------|--------|-------|
| 01 | posts-table migration | e522aab | database/migrate_posts.sql |
| 02 | Blog CSS styles | 75df4d5 | public/assets/css/style.css |
| 03 | Public blog pages | 96043a9 | public/pages/blogi.php, public/pages/postaus.php |
| 04 | Admin CRUD + nav link | e99bea4 | public/admin/posts.php, public/admin/post_delete.php, admin_header.php |
| 05 | Frontpage + nav integration | 835e9f2 | public/pages/index.php, public/src/includes/nav.php |

## What Was Built

### Plan 01 - posts-taulu
- database/migrate_posts.sql: idempotent CREATE TABLE IF NOT EXISTS
- Columns: id, title, slug, content, created_at, updated_at
- UNIQUE KEY on slug, utf8mb4_unicode_ci

### Plan 02 - CSS
- Appended 209 lines of blog CSS to public/assets/css/style.css
- Brace balance verified: 196 open = 196 close
- Classes: .post-list, .post-list-card, .post-layout, .post-body, .post-sidebar, .post-prevnext, .archive-sidebar, .post-admin-form

### Plan 03 - Julkinen blogisivu
- blogi.php: post list with year/month archive filter via (int) cast (T-05-08)
- postaus.php: single post with prev/next nav and sticky archive sidebar
- CSS-only accordion using details/summary - no JavaScript needed
- 404 handling for unknown slug/id
- nl2br(e($content)) for XSS-safe output (T-05-03)

### Plan 04 - Admin CRUD
- posts.php: list + add/edit in single file (action=new/edit&id=N)
- post_delete.php: hard delete, POST-only, CSRF validated
- Slug collision loop with -2/-3 suffix (T-05-06)
- requireLogin() on both files (T-05-05)
- validate_csrf_token() on all POST actions (T-05-04)
- admin_header.php: Postaukset link under Media section

### Plan 05 - Integraatio
- index.php: latestPost query in try/catch PDOException (T-05-07)
- Overlay card links to latest post slug or falls back to blogi.php
- nav.php: Ajankohtaista link, active on both blogi and postaus pages

## Deviations from Plan

None - plan executed exactly as written.

## Threat Surface Scan

All 8 STRIDE threats mitigated as specified in plan threat model.

| Threat | Mitigation |
|--------|------------|
| T-05-01 slug tampering | preg_replace('/[^a-z0-9\-]/', ...) in postaus.php |
| T-05-02 SQL injection | PDO::prepare + named placeholders throughout |
| T-05-03 XSS in content | nl2br(e($post['content'])) in postaus.php |
| T-05-04 CSRF | validate_csrf_token() in posts.php, post_delete.php |
| T-05-05 broken access | requireLogin() in posts.php, post_delete.php |
| T-05-06 slug collision | UNIQUE KEY + -2/-3 loop in posts.php |
| T-05-07 PDOException | try/catch in index.php, no error shown to user |
| T-05-08 year/month injection | (int) cast before query in blogi.php |

## Known Stubs

None - all data wired to database. Placeholder text in news card only shows when no posts exist (intentional graceful degradation).

## Self-Check: PASSED

All 5 files created, all 5 commits verified in git log.
