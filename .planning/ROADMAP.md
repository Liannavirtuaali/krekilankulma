# Roadmap: Virtuaalitalli

**Milestone:** v1.0 — Täysin toimiva virtuaalitalli tietokantoineen
**Granularity:** Coarse (3–5 phases)
**Status:** Planning

## Overview

Projekti rakentaa PHP/MySQL-pohjaisen virtuaalitallin kokonaan uudelleen: ensin perusta ja tietokanta, sitten julkiset sivut, admin-paneeli ja viimeisenä tietoturva sekä Altervista-deployment.

## Phases

- [x] **Phase 1: Perusta & Tietokantarakenne** - Hakemistorakenne, tietokantaskeema ja PHP-peruspohja
- [x] **Phase 2: Julkiset sivut** - Kaikki 5 julkista sivua live-datalla tietokannasta
- [ ] **Phase 3: Admin-paneeli** - Hevosten hallinta, kirjautuminen, kuvat, kilpailut
- [ ] **Phase 4: Tietoturva & Viimeistely** - OWASP, CSRF, XSS ja Altervista-deployment
- [x] **Phase 5: Blogi** - Postausten hallinta adminissa, julkinen postauslista ja yksittäinen postaussivu arkistosidebarilla (completed 2026-06-18)

## Phase Details

### Phase 1: Perusta & Tietokantarakenne

**Goal**: Projektirakenne, tietokantaskeemat ja PHP-peruspohja pystyssä. Kaikki myöhemmät vaiheet rakentuvat tähän.
**Depends on**: Nothing (first phase)
**Requirements**: [DB-01, DB-02, DB-03, DB-04, DB-05, HORSE-01, HORSE-02, HORSE-03, HORSE-04, HORSE-05, HORSE-06, HORSE-07, HORSE-08, HORSE-09, HORSE-10]
**Success Criteria** (what must be TRUE):

  1. Sivuston hakemistorakenne on luotu ja PHP include -rakenne toimii (header/footer/nav näkyvät)
  2. Tietokantaskeema on ajettu — kaikki 6 taulua olemassa MySQL:ssä
  3. PDO-yhteys tietokantaan toimii ilman virheitä
  4. Testidata (2–3 hevosta) voidaan lisätä ja lukea tietokannasta

**Plans**: 3 plans

Plans:

- [ ] 01-01: Hakemistorakenne ja PHP-sivupohja (header.php, footer.php, nav.php, config.php)
- [ ] 01-02: MySQL-tietokantaskeema (horses, horse_photos, pedigree, competitions, foals, admin_users)
- [ ] 01-03: PDO-tietokantayhteys ja apufunktiot (db.php, helpers.php)

### Phase 2: Julkiset sivut

**Goal**: Kaikki viisi julkista sivua toimivat ja näyttävät live-dataa tietokannasta.
**Depends on**: Phase 1
**Requirements**: [PUB-01, PUB-02, PUB-03, PUB-04, PUB-05, PUB-06]
**Success Criteria** (what must be TRUE):

  1. Etusivu, hevoset-listaus, profiilisivu, kasvatus ja yhteystiedot latautuvat oikein
  2. Profiilisivu näyttää sukutaulun (3 sukupolvea), kisakalenterin ja kuvagallerian
  3. Navigaatio toimii PHP include:n kautta kaikilla sivuilla

**Plans**: 3 plans

Plans:

- [ ] 02-01: Etusivu (index.php) ja hevoslistaus (hevoset.php)
- [ ] 02-02: Hevosen profiilisivu (hevonen.php) — perustiedot, sukutaulu, kisakalenteri, kuvagalleria
- [ ] 02-03: Kasvatus-sivu (kasvatus.php) ja yhteystiedot-sivu (yhteystiedot.php)

### Phase 3: Admin-paneeli

**Goal**: Täysin toimiva admin-paneeli hevosten, kuvien, kilpailujen ja kasvatustietojen hallintaan, suojattuna salasanalla.
**Depends on**: Phase 2
**Requirements**: [AUTH-01, AUTH-02, AUTH-03, AUTH-04, AUTH-05, ADMIN-01, ADMIN-02, ADMIN-03, ADMIN-04, ADMIN-05, PHOTO-01, PHOTO-02, PHOTO-03, PHOTO-04, PHOTO-05, COMP-01, COMP-02, COMP-03, BREED-01, BREED-02, BREED-03]
**Success Criteria** (what must be TRUE):

  1. Admin voi kirjautua sisään ja ulos; session suojaa kaikki admin-sivut
  2. Admin voi lisätä, muokata ja poistaa hevosia kaikkine kenttiineen
  3. Admin voi ladata ja poistaa kuvia (max 5 per hevonen); kuva näkyy julkisella sivulla
  4. Admin voi hallita kilpailuja ja varsamerkintöjä

**Plans**: 4 plans

Plans:

- [ ] 03-01: Admin-kirjautuminen (login.php, session-hallinta, logout.php)
- [ ] 03-02: Hevosten CRUD-lomakkeet (lisää/muokkaa/poista, sukutaulun hallinta)
- [ ] 03-03: Kuvien lataus ja hallinta (file upload validointeineen)
- [ ] 03-04: Kisakalenterin ja kasvatustietojen hallinta

### Phase 4: Tietoturva & Viimeistely

**Goal**: Kaikki OWASP-tietoturvakohteet toteutettu ja koko sivusto on valmis julkaistavaksi Altervistaan automatisoidulla CI/CD-deploymentilla.
**Depends on**: Phase 3
**Requirements**: [SEC-01, SEC-02, SEC-03, SEC-04, SEC-05, SEC-06, SEC-07, SEC-08]
**Success Criteria** (what must be TRUE):

  1. Kaikki tietokantakyselyt käyttävät PDO prepared statements, kaikki tulosteet suojattu htmlspecialchars():lla
  2. Kaikki lomakkeet sisältävät CSRF-tokenin ja palvelin validoi sen
  3. Kuvalataus hylkää PHP-tiedostot ja väärät MIME-tyypit
  4. Sivusto toimii oikein Altervistan tuotantoympäristössä
  5. Push main-haaraan deployaa automaattisesti vain `public/`-hakemiston Altervistaan GitHub Actionsin (FTP) kautta

**Plans**: 2 plans

Plans:

- [x] PLAN.md: Tietoturva-audit ja korjaukset (SEC-01–SEC-08: SQL-injektio, XSS, CSRF, upload, session, virheet) — completed 2026-06-17
- [ ] 04-02-PLAN.md: GitHub Actions CI/CD -deployment Altervistaan (deploy.yml + DEPLOYMENT_CHECKLIST päivitys)

---
*Roadmap created: 2026-06-17*
*Last updated: 2026-06-18 — Phase 5 Blogi lisätty; Phase 4 CI/CD-deployment-suunnitelma lisätty*

### Phase 5: Blogi

**Goal**: Tallinpitäjä voi kirjoittaa blogipostauksia adminissa; vierailijat lukevat ne julkisella puolella postauslistalta tai yksittäiseltä sivulta sticky sidebar -arkistolla.
**Depends on**: Phase 2, Phase 3
**Requirements**: [BLOG-01, BLOG-02, BLOG-03, BLOG-04, BLOG-05, BLOG-06]
**Success Criteria** (what must be TRUE):

  1. `posts`-taulukko luotu ja migraatio ajettavissa
  2. Julkinen postauslista (`/pages/blogi.php`) näyttää kaikki postaukset uusimmasta vanhimpaan
  3. Yksittäinen postaussivu (`/pages/postaus.php`) näyttää artikkelin sticky sidebar -arkistolla (vuosi→kuukausi accordion)
  4. Etusivun overlay-kortti linkittää uusimpaan postaukseen
  5. Admin voi lisätä, muokata ja poistaa postauksia (`/admin/posts.php`)

**Plans**: 0 plans
