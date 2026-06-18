---
phase: 04-tietoturva-deploy
reviewed: 2026-06-18T00:00:00Z
depth: standard
files_reviewed: 1
files_reviewed_list:
  - .github/workflows/deploy.yml
findings:
  critical: 2
  warning: 3
  info: 2
  total: 7
status: issues_found
---

# Phase 04: Code Review Report

**Reviewed:** 2026-06-18
**Depth:** standard
**Files Reviewed:** 1
**Status:** issues_found

## Summary

One file was reviewed: `.github/workflows/deploy.yml`, which introduces automated FTP deployment from the `main` branch to Altervista. The workflow is functionally minimal and structurally correct, but has two critical security defects — unencrypted FTP transport and an unpinned third-party action — plus three quality warnings that can cause silent production failures or unnecessary data exposure.

---

## Critical Issues

### CR-01: Credentials transmitted over plain FTP (no encryption)

**File:** `.github/workflows/deploy.yml:17`
**Issue:** The `SamKirkland/FTP-Deploy-Action` defaults to plain, unencrypted FTP when no `protocol` key is specified. This means `FTP_USERNAME`, `FTP_PASSWORD`, and every deployed file are transmitted in cleartext over the network. An on-path observer (at the runner egress, transit, or Altervista ingress) can capture the FTP password in a single packet capture. Altervista supports FTPS (FTP over TLS).

**Fix:** Add `protocol: ftps` to the action inputs to enforce encrypted transport:
```yaml
      - name: Deploy public/ to Altervista via FTP
        uses: SamKirkland/FTP-Deploy-Action@v4
        with:
          server: ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          protocol: ftps          # <-- add this
          local-dir: ./public/
          server-dir: /
```
If Altervista's FTP server uses implicit TLS on port 990, also add `port: 990`.

---

### CR-02: Third-party action pinned to a mutable tag, not a commit SHA

**File:** `.github/workflows/deploy.yml:17`
**Issue:** `uses: SamKirkland/FTP-Deploy-Action@v4` pins to the `v4` tag, which is a mutable pointer. The tag owner can silently redirect it to a different — potentially malicious — commit at any time. GitHub Actions will then execute that new code with full access to `secrets.FTP_PASSWORD`, `secrets.FTP_HOST`, and `secrets.FTP_USERNAME` on every push to `main`. This is a well-documented supply-chain attack vector (analogous to the `tj-actions/changed-files` incident in 2025).

**Fix:** Pin to the exact commit SHA of the release you intend to use. Find the current SHA for v4.3.0 (latest stable as of this review):
```yaml
        uses: SamKirkland/FTP-Deploy-Action@1234abcd...  # v4.3.0
```
Run `gh release view --repo SamKirkland/FTP-Deploy-Action` or check the releases page to get the current SHA, then add a comment with the human-readable version tag.

---

## Warnings

### WR-01: `server-dir: /` may deploy outside the Altervista web root

**File:** `.github/workflows/deploy.yml:23`
**Issue:** `server-dir: /` is the FTP server's filesystem root, not necessarily the web root. On Altervista free hosting the web root is typically `/htdocs/` (or sometimes `/www/`). Deploying to `/` means files land in the FTP account's home directory root; they may not be served by Apache at all, or may overwrite files in unexpected locations. The context document (`04-CONTEXT.md:69`) already notes this ambiguity: "Server-dir voi olla `/` tai `/htdocs/` — riippuu Altervistan konfiguraatiosta."

**Fix:** Verify the correct web root path by logging in via FTP manually and confirming which directory Apache serves. Then set the correct path:
```yaml
          server-dir: /htdocs/   # confirm against your Altervista FTP layout
```

---

### WR-02: `public/uploads/` directory is deployed, syncing user-uploaded binary content

**File:** `.github/workflows/deploy.yml:22-30`
**Issue:** The `local-dir: ./public/` deployment includes `public/uploads/`, which contains user-uploaded images committed to the repository. On every push to `main`, the FTP action will attempt to sync all upload content. This creates two problems: (a) images uploaded directly to the production server (via the admin panel) will be **deleted** on next deploy because the action's default `dangerous-clean-slate` behaviour removes remote-only files, and (b) binary assets bloat every deployment unnecessarily. There is no `**/uploads/**` exclusion in the current exclude list.

**Fix:** Add `uploads/` to the exclude list, and ensure the directory exists on the remote server independently:
```yaml
          exclude: |
            **/.git*
            **/.git*/**
            **/.planning/**
            **/database/**
            **/node_modules/**
            **/.github/**
            **/uploads/**
```
Note: if `dangerous-clean-slate` (or `server-to-local-clean`) is ever enabled, missing this exclusion would delete all production uploads.

---

### WR-03: No `workflow_dispatch` trigger — deployments cannot be re-run manually

**File:** `.github/workflows/deploy.yml:3-7`
**Issue:** The workflow only triggers on `push` to `main`. If a deployment fails (FTP timeout, transient network error) or if secrets need to be rotated and a re-deploy is required without new code changes, the only option is an empty/dummy commit to `main`. This is operationally fragile and pollutes git history.

**Fix:** Add a `workflow_dispatch` trigger to allow manual re-runs from the GitHub Actions UI:
```yaml
on:
  push:
    branches:
      - main
  workflow_dispatch:
```

---

## Info

### IN-01: No job timeout — a hung FTP connection will consume the full 6-hour GitHub limit

**File:** `.github/workflows/deploy.yml:9`
**Issue:** There is no `timeout-minutes` set on the job or step. If the FTP server becomes unresponsive during upload, the GitHub Actions runner will wait until the default 6-hour job timeout, burning Actions minutes unnecessarily.

**Fix:** Add a reasonable timeout at the job level:
```yaml
  deploy:
    runs-on: ubuntu-latest
    timeout-minutes: 15
```

---

### IN-02: Redundant `.git*` exclude patterns applied to a checkout that cannot contain `.git` subdirectories in `./public/`

**File:** `.github/workflows/deploy.yml:24-25`
**Issue:** The patterns `**/.git*` and `**/.git*/**` in the exclude list are intended to prevent `.git` metadata from being uploaded. However, `local-dir: ./public/` scopes the upload to only `public/`, and `actions/checkout@v4` does not place `.git` metadata inside subdirectories — `.git/` exists only at the repository root (outside `./public/`). The patterns are harmless but misleading; they suggest `.git` files could appear inside `public/`, which they cannot under normal checkout behaviour.

**Fix:** Remove the redundant `.git*` patterns if you want to keep the exclude list clean, or leave them as a defensive measure but add a comment explaining their intent. No functional change required.

---

_Reviewed: 2026-06-18_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
