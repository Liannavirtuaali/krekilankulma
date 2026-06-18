---
status: testing
phase: 04-tietoturva-deploy
source: [04-VERIFICATION.md]
started: 2026-06-18T23:00:00Z
updated: 2026-06-18T23:00:00Z
---

## Current Test

number: 1
name: Verify site works correctly in Altervista production environment
expected: |
  Admin login works, horse CRUD works, photo upload works, public pages display correctly,
  HTTPS is active, no error details shown to users
awaiting: user response

## Tests

### 1. Verify site works correctly in Altervista production environment
expected: Admin login works, horse CRUD works, photo upload works, public pages display correctly, HTTPS is active, no error details shown to users
result: [pending]

### 2. Confirm FTP deploy uses encrypted transport (FTPS)
expected: Either `protocol: ftps` is added to `deploy.yml`, or Altervista FTP is confirmed to always negotiate TLS. Credentials should not transit in cleartext.
result: [pending]

### 3. Confirm server-dir value (/ vs /htdocs/) is correct for this Altervista account
expected: After a test push to main, deployed files appear at the correct web root and the site is accessible at the Altervista URL
result: [pending]

### 4. Verify production uploads directory is not overwritten by CI/CD deploys
expected: User-uploaded photos persist across git pushes; `deploy.yml` exclude list contains `**/uploads/**`
result: [pending]

## Summary

total: 4
passed: 0
issues: 0
pending: 4
skipped: 0
blocked: 0

## Gaps
