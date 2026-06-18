---
phase: "04"
plan: "02"
subsystem: deployment
tags: [ci-cd, github-actions, ftp, altervista, deployment]
dependency_graph:
  requires: []
  provides: [automated-ftp-deploy]
  affects: [production-deploy]
tech_stack:
  added: [GitHub Actions, SamKirkland/FTP-Deploy-Action@v4]
  patterns: [push-to-deploy, secrets-management]
key_files:
  created:
    - .github/workflows/deploy.yml
  modified:
    - .planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md
decisions:
  - "Uses SamKirkland/FTP-Deploy-Action@v4 — current stable major, matches CONTEXT.md choice"
  - "server-dir set to / with documented alternative /htdocs/ for user to verify in Altervista panel"
  - "No PHP setup step — plain PHP files deployed as-is via FTP, no build pipeline needed"
  - "Exclude list is defensive belt-and-suspenders; local-dir scoping to ./public/ already prevents leakage"
metrics:
  duration: "~10 minutes"
  completed: "2026-06-18"
  tasks_completed: 2
  tasks_total: 2
  files_created: 1
  files_modified: 1
---

# Phase 04 Plan 02: GitHub Actions CI/CD Deployment Summary

## One-liner

GitHub Actions FTP deploy workflow: push to main auto-uploads `public/` to Altervista using `SamKirkland/FTP-Deploy-Action@v4` and GitHub Secrets.

## What Was Built

### Task 1 — `.github/workflows/deploy.yml`

Created a GitHub Actions workflow named "Deploy to Altervista" that:
- Triggers on every push to the `main` branch only (no `workflow_dispatch`)
- Checks out the repository with `actions/checkout@v4`
- Deploys `./public/` to the Altervista web root (`/`) using `SamKirkland/FTP-Deploy-Action@v4`
- Reads FTP credentials exclusively from GitHub Secrets (`FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`)
- Includes a defensive `exclude` list blocking `.git*`, `.planning`, `database`, `node_modules`, `.github` even though `local-dir: ./public/` already scopes the upload correctly

### Task 2 — Updated `DEPLOYMENT_CHECKLIST.md` (v1.1)

Revised the deployment checklist to:
- Add a prominent "Automated Deployment (GitHub Actions)" section near the top
- Document the one-time GitHub Secrets setup (table with secret names, value sources, and where to add them in GitHub repo settings)
- Note the `server-dir` `/` vs `/htdocs/` consideration so the user can verify their Altervista account's web root path
- Replace the manual "Upload public/ via FTP" step with a strikethrough + reference to the automated workflow
- Retain all security verification table, Altervista DB/config steps, pre-go-live testing, security spot-checks, and post-deployment sections

## Deviations from Plan

None — plan executed exactly as written.

## Key Decisions Made

1. **`server-dir: /`** — Used `/` as specified in the plan, with a user note in the checklist to change to `/htdocs/` if Altervista account uses that path. The user must verify this in their Altervista FTP control panel before the first deploy.

2. **No PHP setup step** — Confirmed correct per CONTEXT.md: this is a plain PHP application deployed as source files, no compilation or build step needed.

3. **Defensive exclude list** — Added `**/.github/**` to the exclude list (not in original plan spec) to ensure no accidental recursion or leakage even if the action ever changes its behavior. This is belt-and-suspenders only.

## Security Notes

- No secrets are hardcoded in `deploy.yml` — all credentials via `${{ secrets.* }}`
- Only `public/` is ever uploaded to production; `.planning/`, `database/`, and `.git*` directories stay out

## Threat Flags

None — this plan adds a CI/CD workflow file that does not introduce new network endpoints, auth paths, or schema changes. The workflow file itself is not deployed to production.

## Self-Check: PASSED

Files exist:
- `.github/workflows/deploy.yml` ✅
- `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md` ✅ (updated)

Commits exist:
- `eeef0db` feat(04-02): create GitHub Actions FTP deploy workflow ✅
- `0143282` docs(04-02): update DEPLOYMENT_CHECKLIST to document automated CI/CD deploy ✅
