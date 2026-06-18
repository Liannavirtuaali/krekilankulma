# Phase 04: Tietoturva & Deploy — Context

**Gathered:** 2026-06-18
**Status:** Ready for planning
**Source:** User decisions (conversation)

<domain>
## Phase Boundary

Tietoturvakoodi on jo toteutettu (PLAN.md + PLAN-SUMMARY.md). Tämä suunnitelma kattaa ainoastaan **automatisoitu CI/CD-deployment GitHub Actionsin kautta Altervistaan**.

Skopo:
- Luo `.github/workflows/deploy.yml` — GitHub Actions workflow FTP-deployta varten
- Deployataan vain `public/`-hakemisto (ei `.planning/`, ei `database/`, ei muuta)
- Trigger: push main-haaraan
- FTP-tunnukset talletetaan GitHub Secretseihin

</domain>

<decisions>
## Implementation Decisions

### Deploy scope
- Deploytaan AINOASTAAN `public/`-hakemiston sisältö Altervistan web rootiin
- `.planning/`, `database/`, `.github/` jne. eivät mene tuotantoon

### Trigger
- Workflow käynnistyy automaattisesti kun push tehdään `main`-haaraan
- Ei manuaalisia triggereitä (workflow_dispatch) tässä vaiheessa

### FTP Action
- Käytetään `SamKirkland/FTP-Deploy-Action@v4` (tai uusin vakaa versio)
- Server-dir: Altervistan web root (`/`)
- Local-dir: `./public/`
- Älä deployta piilotiedostoja tai .htaccess-tiedostoja erikseen (ne ovat jo public/:ssa)

### GitHub Secrets
- `FTP_HOST` — Altervistan FTP-osoite (esim. `ftp.altervista.org`)
- `FTP_USERNAME` — Altervistan FTP-käyttäjätunnus
- `FTP_PASSWORD` — Altervistan FTP-salasana
- Nämä lisätään manuaalisesti GitHub-repositorion Settings → Secrets

### Deployment checklist
- Päivitä DEPLOYMENT_CHECKLIST.md viittaamaan GitHub Actionsin käyttöönottoon
- Manuaalideploy-kohdat korvataan Actions-setupin vaiheistuksella

### Claude's Discretion
- Workflow-tiedoston nimi ja rakenne
- Onko PHP-versiota syytä määrittää workflow:ssa
- Timeout-asetukset
- Mahdollinen fail-fast-logiikka

</decisions>

<canonical_refs>
## Canonical References

- `.planning/phases/04-tietoturva-deploy/DEPLOYMENT_CHECKLIST.md` — nykyinen manuaalinen checklist, korvataan/päivitetään
- `.planning/phases/04-tietoturva-deploy/PLAN-SUMMARY.md` — tietoturvakoodi jo toteutettu

</canonical_refs>

<specifics>
## Specific Ideas

- FTP-deploy action: https://github.com/SamKirkland/FTP-Deploy-Action
- Altervista FTP hostname: tyypillisesti `ftp.altervista.org` (käyttäjä varmistaa oman)
- Server-dir voi olla `/` tai `/htdocs/` — riippuu Altervistan konfiguraatiosta
- `exclude` pattern: `**/.planning/`, `**/database/`, `**/.git/`

</specifics>

<deferred>
## Deferred Ideas

- Automaattinen tietokantamigraatio deployssa (liian riski, ei nyt)
- Slack/email-notifikaatiot deploy-onnistumisesta
- Staging-ympäristö ennen tuotantoa

</deferred>

---

*Phase: 04-tietoturva-deploy*
*Context gathered: 2026-06-18 — GitHub Actions CI/CD deployment*
