---
phase: 05-blogi
verified: 2026-06-18T12:00:00Z
status: human_needed
score: 5/5
overrides_applied: 0
human_verification:
  - test: "Kirjaudu adminiin ja lisää postaus, muokkaa sitä ja poista se"
    expected: "Lista päivittyy joka vaiheen jälkeen, CSRF-tarkistus estää väärennetyt lomakkeet, slug-törmäyslogiikka tuottaa -2-liitteen duplikaateille"
    why_human: "Interaktiivinen CRUD-virta, CSRF-vahvistus ja redirect-ketju vaativat selaimen"
  - test: "Avaa /pages/postaus.php?slug=testijuttu selaimessa, vieritä sivu alas"
    expected: "Sticky sidebar pysyy näkyvissä vierittäessä; arkisto-accordion (details/summary) avautuu ja sulkeutuu ilman JavaScriptiä; dropcap näkyy ensimmäisen kappaleen ensimmäisellä kirjaimella"
    why_human: "CSS position:sticky, accordion-käyttäytyminen ja dropcap-renderöinti vaativat selaimen"
  - test: "Avaa /pages/postaus.php?slug=ei-ole-olemassa"
    expected: "HTTP 404 -statuskoodi palautetaan; sivu näyttää 'Postausta ei löydy' -viestin"
    why_human: "HTTP-statuskoodin ja sivun renderöinnin tarkistus vaatii selaimen tai curl-ajon live-palvelimella"
  - test: "Kirjautumattomuustesti: avaa /admin/posts.php ilman sessiota"
    expected: "Uudelleenohjaus /admin/login.php:hen välittömästi"
    why_human: "Session-käyttäytyminen vaatii selaimen tai curl-ajon live-palvelimella"
---

# Phase 05: Blogi — Verification Report

**Phase Goal:** Tallinpitäjä voi kirjoittaa blogipostauksia adminissa; vierailijat lukevat ne julkisella puolella postauslistalta tai yksittäiseltä sivulta sticky sidebar -arkistolla.
**Verified:** 2026-06-18T12:00:00Z
**Status:** human_needed
**Re-verification:** No — initial verification

---

## Goal Achievement

### Observable Truths

| # | Truth | Status | Evidence |
|---|-------|--------|----------|
| 1 | `posts`-taulukko luotu, migraatio idempotent | VERIFIED | `database/migrate_posts.sql` — CREATE TABLE IF NOT EXISTS, UNIQUE KEY `uk_post_slug`, utf8mb4_unicode_ci, kaikki 6 kolumnia present |
| 2 | Julkinen postauslista (`/pages/blogi.php`) näyttää postaukset uusimmasta vanhimpaan | VERIFIED | `blogi.php` tekee `ORDER BY created_at DESC` -kyselyn, renderöi `.post-list` / `.post-list-card`, empty-state "Ei vielä postauksia." |
| 3 | Yksittäinen postaussivu (`/pages/postaus.php`) näyttää artikkelin sticky sidebar -arkistolla | VERIFIED | `postaus.php` — `.post-layout` grid, `.post-sidebar`, `.archive-sidebar` `<details>/<summary>` accordion, prev/next nav, 404-käsittely slug ja id molemmille |
| 4 | Etusivun overlay-kortti linkittää uusimpaan postaukseen | VERIFIED | `index.php` — `try/catch PDOException`, `latestPost`-kysely, `overlay-card--news` href dynaaminen (`postaus.php?slug=...` tai fallback `blogi.php`) |
| 5 | Admin voi lisätä, muokata ja poistaa postauksia | VERIFIED | `posts.php` — `requireLogin()`, CSRF, slug-törmäyslooppi, INSERT/UPDATE; `post_delete.php` — POST-only, CSRF, `DELETE FROM posts WHERE id` |

**Score:** 5/5 truths verified (automated checks)

---

### Deferred Items

Ei siirrettyjä kohteita. Kaikki löydökset on käsitelty alla.

---

### Required Artifacts

| Artifact | Expected | Status | Details |
|----------|----------|--------|---------|
| `database/migrate_posts.sql` | CREATE TABLE posts idempotent | VERIFIED | IF NOT EXISTS, kaikki kolumnit, UNIQUE slug, utf8mb4 |
| `public/pages/blogi.php` | Julkinen postauslista | VERIFIED | Substantiivinen — DB-kysely, renderöi listaa, suodatus year/month |
| `public/pages/postaus.php` | Yksittäinen postaussivu sticky sidebar -arkistolla | VERIFIED | Substantiivinen — artikkeli, prev/next, archive accordion, 404-käsittely |
| `public/admin/posts.php` | Admin CRUD (requireLogin, CSRF, slug dedup) | VERIFIED | Substantiivinen — kaikki kolme piirrettä löytyy koodista |
| `public/admin/post_delete.php` | Delete handler (requireLogin, CSRF) | VERIFIED | POST-only, CSRF-tarkistus, `DELETE` PDO prepared statement |
| `public/admin/includes/admin_header.php` | Postaukset-navigaatiolinkki | VERIFIED | Rivi 272–273: `📝 Postaukset` -linkki strpos-tarkistuksella |
| `public/assets/css/style.css` | Blog CSS -luokat | VERIFIED | Luokat `.post-list`, `.post-list-card`, `.post-layout`, `.post-body`, `.post-sidebar`, `.post-prevnext`, `.archive-sidebar`, `.post-admin-form` kaikki löytyvät (rivit 898–1100); aaltosulkutasapaino 196=196 |
| `public/pages/index.php` | latestPost-kysely try/catch | VERIFIED | Rivit 19–28: `$latestPost = null`, `try { $stmtPost = $db->query(...)`, `} catch (PDOException $e) { $latestPost = null; }` |
| `public/src/includes/nav.php` | Ajankohtaista-navigointilinkki | VERIFIED | Rivi 15–17: linkki `blogi.php`:hen, aktiivinen luokka sekä blogi- että postaus-sivuille |

---

### Key Link Verification

| From | To | Via | Status | Details |
|------|----|-----|--------|---------|
| `blogi.php` | `posts` DB-taulu | `$db->query('SELECT ... FROM posts ...')` | WIRED | Kysely ajaa, `$posts->fetchAll()` rendataan listaksi |
| `blogi.php` | Arkistosuodatus | `(int)$_GET['year']`, `(int)$_GET['month']` | WIRED | Parametrit validoitu ja haetaan prepared statementilla |
| `postaus.php` | `posts` DB-taulu | slug/id prepared statement | WIRED | Kaksihaarainen haku, `preg_replace` slug-sanitointi (T-05-01) |
| `postaus.php` | Arkistosidebar | `$stmtArchive->query(...)`, `$archive[][]` | WIRED | Kysely ryhmittelee yr/mo, `<details>/<summary>` renderöi |
| `admin/posts.php` | `posts` DB-taulu | INSERT/UPDATE prepared statements | WIRED | Slug-törmäyslooppi + INSERT/UPDATE |
| `admin/posts.php` | CSRF | `validate_csrf_token($_POST['csrf_token'])` | WIRED | Tarkistetaan heti POST-käsittelyn alussa |
| `admin/posts.php` | `requireLogin()` | `requireLogin();` heti tiedoston alussa (rivi 3) | WIRED | Palauttaa kirjautumattoman käyttäjän loginiin |
| `admin/post_delete.php` | `posts` DB-taulu | `DELETE FROM posts WHERE id = :id` | WIRED | PDO prepared statement, `(int)` cast id:lle |
| `admin/post_delete.php` | CSRF | `validate_csrf_token($_POST['csrf_token'])` | WIRED | Tarkistetaan ennen poistoa |
| `index.php` | `posts` DB-taulu | `try { $stmtPost = $db->query(...) }` | WIRED | try/catch graceful degradation, tulos käytetään overlay-kortissa |
| `nav.php` | `blogi.php` | `href="...blogi.php"` + strpos active-tarkistus | WIRED | Linkki navigaatiossa, aktiivinen sekä blogi- että postaus-sivuilla |

---

### Data-Flow Trace (Level 4)

| Artifact | Data Variable | Source | Produces Real Data | Status |
|----------|---------------|--------|--------------------|--------|
| `blogi.php` | `$posts` | `$db->query('SELECT ... FROM posts ...')` | Kyllä — DB-kysely, ei staattinen | FLOWING |
| `postaus.php` | `$post` | PDO prepare + execute, slug/id-haku | Kyllä — DB-kysely | FLOWING |
| `postaus.php` | `$archive` | `$stmtArchive->query(...)`, `fetchAll()` | Kyllä — GROUP BY -kysely | FLOWING |
| `index.php` | `$latestPost` | `$db->query('SELECT ... FROM posts ORDER BY created_at DESC LIMIT 1')` | Kyllä — DB-kysely, fallback null | FLOWING |
| `admin/posts.php` | `$posts` (lista) | `$db->query('SELECT id, title, slug, created_at FROM posts ORDER BY created_at DESC')` | Kyllä — DB-kysely | FLOWING |

---

### Behavioral Spot-Checks

Palvelinta ei käynnistetty — live-curl-testit siirretty Human Verification -osioon. Staattinen kooditarkistus:

| Behavior | Check | Result | Status |
|----------|-------|--------|--------|
| `migrate_posts.sql` sisältää oikean rakenteen | Luettu tiedosto, tarkistettu kolumnit ja UNIQUE KEY | Kaikki 6 kolumnia, UNIQUE KEY `uk_post_slug` | PASS |
| `blogi.php` suodattaa year/month turvallisesti | `(int)$_GET['year']`, `(int)$_GET['month']` löytyy | T-05-08 mitigoitu | PASS |
| `postaus.php` sanitoi slugin | `preg_replace('/[^a-z0-9\-]/', '', strtolower(trim(...)))` löytyy | T-05-01 mitigoitu | PASS |
| XSS-suojaus sisällössä | `nl2br(e($post['content']))` käytetään postaus.php:ssä | T-05-03 mitigoitu | PASS |
| CSS-luokkien aaltosulkutasapaino | `{` = 196, `}` = 196 | Tasapainossa | PASS |
| `slugify()` funktio olemassa | `helpers.php` rivi 24 | Olemassa | PASS |
| `requireLogin()` funktio olemassa | `helpers.php` rivi 58 | Olemassa | PASS |
| `validate_csrf_token()` olemassa | `helpers.php` rivi 150 | Olemassa | PASS |

---

### Probe Execution

Ei probe-tiedostoja tunnistettu tässä vaiheessa. Step 7c: SKIPPED (no probe scripts found).

---

### Requirements Coverage

**Tärkeä huomio — BLOG-* vaatimusten puuttuminen REQUIREMENTS.md:stä:**

ROADMAP.md Phase 5 viittaa vaatimuksiin `[BLOG-01, BLOG-02, BLOG-03, BLOG-04, BLOG-05, BLOG-06]`, mutta näitä tunnisteita ei ole määritelty `REQUIREMENTS.md`:ssä lainkaan. Blogi-ominaisuus on `REQUIREMENTS.md`:ssä merkitty `V2-02: Uutiset/blogi-osio` tulevaisuuden laajennukseksi.

Tämä tarkoittaa, että blogi-feature kehitettiin "out-of-band" — ROADMAP.md päivitettiin, mutta REQUIREMENTS.md jätettiin päivittämättä.

| Requirement | Source | Description | Status | Evidence |
|-------------|--------|-------------|--------|---------|
| BLOG-01 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| BLOG-02 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| BLOG-03 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| BLOG-04 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| BLOG-05 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| BLOG-06 | ROADMAP.md Phase 5 | (ei määritelty REQUIREMENTS.md:ssä) | ORPHANED — ei voi vahvistaa ID-tasolla, toiminnallisuus koodissa |
| V2-02 | REQUIREMENTS.md | Uutiset/blogi-osio | SATISFIED — toteutettu Phase 5:ssä vaikka se oli V2-luettelossa |

**Suositus:** Lisää BLOG-01 – BLOG-06 REQUIREMENTS.md:hen (tai korvaa V2-02:lla), jotta jäljitettävyys on täydellinen. Tämä ei ole blocker — koodi on olemassa ja toiminnallinen — mutta traceability-raportti on epätäydellinen.

---

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|------|------|---------|----------|--------|
| `public/admin/posts.php` | 125, 131 | `placeholder="..."` HTML-attribuutti | Info | HTML form input placeholder — EI koodistub. Hyväksytty käyttötapa. |

Muut tarkastellut tiedostot (`blogi.php`, `postaus.php`, `post_delete.php`, `index.php`, `nav.php`, `admin_header.php`, `migrate_posts.sql`): Ei TBD/FIXME/XXX/TODO/HACK-merkintöjä. Ei tyhjiä implementaatioita. Ei kovakoodattua staattista dataa.

---

### Human Verification Required

#### 1. Admin CRUD -virta selaimella

**Test:** Kirjaudu `/admin/` -paneeliin, navigoi "📝 Postaukset" -linkin kautta, lisää uusi postaus (otsikko + sisältö), muokkaa sitä, lisää toinen samalla otsikolla, tarkista slug saa `-2`-liitteen, poista ensimmäinen confirm-dialogilla.
**Expected:** Jokainen toiminto ohjaa takaisin listaan; flash-viestit "Postaus lisätty", "Muutokset tallennettu", "Postaus poistettu" näkyvät; duplikaatti saa `-2`-slugin; confirm-dialogi näkyy ennen poistoa.
**Why human:** Interaktiivinen CRUD-virta, redirect-ketju, JavaScript confirm-dialogi ja CSRF-kierros vaativat selaimen.

#### 2. Sticky sidebar ja CSS-only accordion

**Test:** Lisää testisisältöä kantaan, avaa `/pages/postaus.php?slug=testijuttu`, vieritä sivu alas.
**Expected:** `.post-sidebar` pysyy näkyvissä (`position: sticky; top: 68px`). Arkisto-accordion (`<details>/<summary>`) avautuu klikattaessa ja sulkeutuu uudelleen ilman JavaScriptiä. Dropcap (`::first-letter`) näkyy ensimmäisen kappaleen alussa kullankeltaisena.
**Why human:** CSS sticky-käyttäytyminen, accordion-animaatio ja pseudo-elementti vaativat selaimen.

#### 3. 404-käsittely

**Test:** Avaa `/pages/postaus.php?slug=ei-ole-olemassa` selaimessa tai `curl -o /dev/null -s -w "%{http_code}" "http://localhost:8080/pages/postaus.php?slug=ei-ole-olemassa"`.
**Expected:** HTTP 404, sivu näyttää "Postausta ei löydy" -viestin.
**Why human:** HTTP-statuskoodin verifiointi live-palvelinta vasten.

#### 4. Kirjautumattomuus-suojaus

**Test:** Tyhjennä sessio (tai avaa incognito-ikkuna), yritä avata `/admin/posts.php` suoraan.
**Expected:** Välitön uudelleenohjaus `/admin/login.php`:hen ilman admin-sisällön näyttämistä.
**Why human:** Session-käyttäytyminen ja uudelleenohjaus vaativat selaimen tai curl-ajon.

---

### Gaps Summary

Ei koodillisia gappeja. Kaikki 5 ROADMAP-success-criteria on täytetty:

1. `posts`-taulukko luotu, migraatio idempotent — VERIFIED
2. Julkinen postauslista toiminnallinen — VERIFIED
3. Yksittäinen postaussivu sticky sidebar -arkistolla — VERIFIED
4. Etusivun overlay-kortti dynaaminen — VERIFIED
5. Admin CRUD toimii — VERIFIED

**Ainoa tunnistettu puute** on traceability-aukko: BLOG-01 – BLOG-06 vaatimusID:t puuttuvat REQUIREMENTS.md:stä. Tämä on dokumentaatio-ongelma, ei koodivirhe, eikä estä phase-hyväksyntää. Suositellaan korjattavaksi REQUIREMENTS.md-päivityksellä.

4 human-verification -kohdetta vaativat live-palvelintestin ennen lopullista hyväksyntää.

---

_Verified: 2026-06-18T12:00:00Z_
_Verifier: Claude (gsd-verifier)_
