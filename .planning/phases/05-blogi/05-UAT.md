---
status: testing
phase: 05-blogi
source: [05-VERIFICATION.md]
started: 2026-06-18T13:45:00Z
updated: 2026-06-18T13:45:00Z
---

## Current Test

number: 1
name: Admin CRUD virta
expected: |
  Postauksen lisäys, muokkaus ja poisto toimivat adminissa. Slug saa -2-liitteen törmäyksessä, confirm-dialog näkyy poistossa.
awaiting: user response

## Tests

### 1. Admin CRUD virta
expected: Lisää/muokkaa/poista postauksia. Slug-törmäys → -2-liite automaattisesti. Confirm-dialog ennen poistoa.
result: [pending]

### 2. Sticky sidebar + CSS-only accordion
expected: Sidebar pysyy paikallaan vierittäessä. details/summary accordion toimii ilman JavaScriptiä. Dropcap näkyy ensimmäisen kappaleen ensimmäisellä kirjaimella.
result: [pending]

### 3. 404-käsittely
expected: `/pages/postaus.php?slug=ei-ole-olemassa` palauttaa HTTP 404 -statuskoodin.
result: [pending]

### 4. requireLogin-uudelleenohjaus
expected: `/admin/posts.php` ilman sessioita ohjaa välittömästi `/admin/login.php`-sivulle.
result: [pending]

## Summary

total: 4
passed: 0
issues: 0
pending: 4
skipped: 0
blocked: 0

## Gaps
