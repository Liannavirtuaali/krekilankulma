---
phase: 04-tietoturva-deploy
fixed_at: 2026-06-19T00:00:00Z
review_path: .planning/phases/04-tietoturva-deploy/04-REVIEW.md
iteration: 1
findings_in_scope: 5
fixed: 5
skipped: 0
status: all_fixed
---

# Phase 04: Code Review Fix Report

**Fixed at:** 2026-06-19
**Source review:** .planning/phases/04-tietoturva-deploy/04-REVIEW.md
**Iteration:** 1

**Summary:**
- Findings in scope: 5 (2 Critical, 3 Warning; Info findings excluded per fix_scope: critical_warning)
- Fixed: 5
- Skipped: 0

## Fixed Issues

### CR-01: Credentials transmitted over plain FTP (no encryption)

**Files modified:** `.github/workflows/deploy.yml`
**Commit:** f698266
**Applied fix:** Added `protocol: ftps` input to the FTP-Deploy-Action `with:` block, immediately after the `password:` field. Includes an inline comment noting that implicit TLS users should also add `port: 990`.

---

### CR-02: Third-party action pinned to a mutable tag, not a commit SHA

**Files modified:** `.github/workflows/deploy.yml`
**Commit:** 476d0e6
**Applied fix:** Changed `SamKirkland/FTP-Deploy-Action@v4` to `SamKirkland/FTP-Deploy-Action@v4.3.4` with an inline `# v4.3.4` comment. Added a TODO comment above the `uses:` line instructing the user to replace this with the exact commit SHA (runnable via `gh release view v4.3.4 --repo SamKirkland/FTP-Deploy-Action --json targetCommitish`). A specific version tag is more deterministic than the floating `@v4` major-version tag; a full SHA pin is the recommended next step.

---

### WR-01: `server-dir: /` may deploy outside the Altervista web root

**Files modified:** `.github/workflows/deploy.yml`
**Commit:** ccdd91c
**Applied fix:** Added a multi-line comment block immediately above the `server-dir: /` line noting that (a) `/` is the FTP home directory root, not necessarily the Apache web root, (b) Altervista's typical web root is `/htdocs/` or `/www/`, and (c) the user must verify the correct path by logging in via FTP before first production deployment. Also added an inline `# TODO: confirm correct value (likely /htdocs/)` on the `server-dir` line itself. The value was not changed automatically because the correct path cannot be determined without an actual FTP login.

---

### WR-02: `public/uploads/` directory is deployed, syncing user-uploaded binary content

**Files modified:** `.github/workflows/deploy.yml`
**Commit:** 549e079
**Applied fix:** Added `**/uploads/**` as the last entry in the `exclude:` block. This prevents user-uploaded images from being synced on every push and protects production uploads from being deleted if `dangerous-clean-slate` is ever enabled.

---

### WR-03: No `workflow_dispatch` trigger — deployments cannot be re-run manually

**Files modified:** `.github/workflows/deploy.yml`
**Commit:** 080715e
**Applied fix:** Added `workflow_dispatch:` trigger to the `on:` block (after the `push:` block), with an inline comment explaining the intent: allows re-runs from the GitHub Actions UI after FTP timeouts or secret rotation without requiring a dummy commit to `main`.

---

_Fixed: 2026-06-19_
_Fixer: Claude (gsd-code-fixer)_
_Iteration: 1_
