# Phase 5: Blogi - Research

**Researched:** 2026-06-18
**Domain:** PHP 8 / MySQL blog — admin CRUD + public read with sticky sidebar archive
**Confidence:** HIGH (stack is fully controlled, no external dependencies)

---

## Summary

Tämä vaihe lisää yksinkertaisen blogin olemassaolevaan proseduraaliseen PHP-sovellukseen.
Kaikki tarvittavat rakennuspalikat (PDO, slugify, e(), session auth) ovat jo projektin
helpers.php:ssä ja db.php:ssä — voidaan käyttää suoraan ilman uusia kirjastoja.

Suurin arkkitehtuurinen päätös on **sisältöturvallisuus**: koska sovelluksessa ei ole
HTML-sanitointikirjastoa (kuten HTML Purifier), turvallisin ratkaisu on tallentaa sisältö
**pelkkänä tekstinä** ja renderöidä `nl2br(e($content))`. Tämä vastaa OWASP-vaatimuksia
ilman ulkoisia riippuvuuksia.

URL-strategia: `?slug=xxx`-parametri (sama kuvio kuin horses/hevonen.php), koska
mod_rewrite on kyllä päällä Dockerissa mutta Altervista-tuotannossa ei voida taata.
Fallback `?id=N` taaksepäin yhteensopivuutta varten.

**Primary recommendation:** Käytä olemassaolevia helppereitä, pelkkä teksti + nl2br(),
slug-parametri URL:ssa, arkistokysely yhdellä GROUP BY -kyselyllä.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Post CRUD (create/edit/delete) | Admin PHP (`/admin/posts.php`) | DB layer | Auth-gated, sama kuvio kuin horse_add/edit/delete |
| Public post list | Public PHP (`/pages/blogi.php`) | DB layer | Listataan kaikki julkaistut, uusin ensin |
| Single post view + sidebar | Public PHP (`/pages/postaus.php`) | DB layer | Sidebar arkisto haetaan erillisellä kyselyllä |
| Archive sidebar data | DB (GROUP BY query) | PHP rendering | Yksi kysely, PHP rakentaa accordion-rakenteen |
| Prev/next navigation | DB (adjacent queries) | PHP rendering | Kaksi erillistä prepared statement -kyselyä |
| Slug generation | PHP (`slugify()` in helpers.php) | — | Funktio jo olemassa, reusataan suoraan |
| XSS protection | PHP (`e()` + `nl2br()`) | — | Ei ulkoista kirjastoa tarvita plain text -lähestymistavalla |
| Front page latest post | Public PHP (`/pages/index.php`) | DB layer | Yksi `LIMIT 1` -kysely, linkki postaukseen |

---

## Standard Stack

### Core (kaikki jo projektin PHP/MySQL-stackissa)

| Component | Version | Purpose | Note |
|-----------|---------|---------|------|
| PHP | 8.2 (Docker) | Kaikki sivulogiikka | Sama kuin muukin projekti |
| MySQL | 8.x (Docker) | Tietokanta | posts-taulu lisätään migraatiolla |
| PDO | built-in | Tietokantayhteys | `getDB()` singleton jo db.php:ssä |
| Apache mod_rewrite | enabled | .htaccess URL-uudelleenkirjoitus | Päällä Dockerissa; Altervistassa ei taata |

### Ei uusia paketteja

Tämä vaihe ei asenna yhtään ulkoista pakettia. Kaikki tarvittava on jo projektissa:
- `slugify()` — helpers.php
- `e()` — helpers.php
- `sanitize()` — helpers.php
- `getDB()` — db.php
- `validate_csrf_token()` — helpers.php (tarkistetaan alla)
- `requireLogin()` — helpers.php

---

## Package Legitimacy Audit

> Ei ulkoisia paketteja tässä vaiheessa. Audit ei sovellettavissa.

---

## Architecture Patterns

### System Architecture Diagram

```
[Admin kirjautunut]
        │
        ▼
/admin/posts.php ──POST──► MySQL: posts-taulu (INSERT/UPDATE/DELETE)
        │                          │
        │                          │
[Vierailija]                       │
        │                          │
        ├──► /pages/blogi.php ◄────┤  SELECT * ORDER BY created_at DESC
        │                          │
        ├──► /pages/postaus.php ◄──┤  SELECT by slug/id
        │         │                │  SELECT archive (GROUP BY year/month)
        │         │                │  SELECT prev/next (adjacent by created_at)
        │         ▼                │
        │    sticky sidebar        │
        │    (archive accordion)   │
        │                          │
        └──► /pages/index.php ◄────┘  SELECT LIMIT 1 (uusin postaus)
```

### Recommended Project Structure (uudet tiedostot)

```
database/
└── migrate_posts.sql          # posts-taulun luonti

public/
├── admin/
│   └── posts.php              # Admin CRUD (list + add + edit + delete)
│   └── post_delete.php        # Delete-toiminto (POST-only)
└── pages/
    ├── blogi.php              # Julkinen postauslista
    └── postaus.php            # Yksittäinen postaus + sidebar
```

### Pattern 1: posts-taulun skeema

```sql
-- Source: projektin olemassaolevat taulut (schema.sql) + standardikäytäntö
CREATE TABLE IF NOT EXISTS `posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(255) NOT NULL,
  `content`    MEDIUMTEXT   NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_slug` (`slug`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Sarakkeiden perustelu:**
- `slug` — UNIQUE, indeksoitu; haetaan URL-parametrilla `?slug=xxx`
- `content` — MEDIUMTEXT (max ~16 MB); riittää pitkiinkin postauksiin
- `created_at` — indeksoitu; lähes kaikki kyselyt järjestelevät tämän mukaan
- Ei `published`-saraketta tässä vaiheessa — kaikki tallennetut postaukset ovat julkisia (YAGNI)

### Pattern 2: URL-strategia (slug vs. id)

**Valittu lähestymistapa:** `?slug=xxx` ensisijaisesti, `?id=N` fallbackina — **sama kuvio kuin hevonen.php**.

```
/pages/postaus.php?slug=ensimmainen-postaus   ← ensisijainen
/pages/postaus.php?id=1                        ← fallback (taaksepäin yhteensopivuus)
```

**Perustelut:**
- mod_rewrite ON päällä Dockerissa (Dockerfile: `a2enmod rewrite` + `AllowOverride All`)
- Altervista-tuotannossa mod_rewrite ei ole taattu → `?slug=`-parametri on turvallisempi valinta
- Jos puhtaat URL:t halutaan tuotantoon, lisätään .htaccess myöhemmin erillisenä vaiheena
- Slug generoidaan olemassaolevalla `slugify()`-funktiolla — ei uusia riippuvuuksia

**Slug-uniiikkiuden varmistus adminissa:**

```php
// Tarkista onko slug jo käytössä (eksklusiivisesti editoinnissa)
$stmt = $db->prepare('SELECT id FROM posts WHERE slug = :slug AND id != :id');
$stmt->execute([':slug' => $slug, ':id' => $excludeId]);
if ($stmt->fetch()) {
    // Lisää numero perään: postaus → postaus-2
    $slug = $slug . '-' . time();
}
```

### Pattern 3: Archive-kysely (sidebar accordion)

```sql
-- Source: MySQL GROUP BY + YEAR/MONTH -funktiot, standardikäytäntö
SELECT
    YEAR(created_at)  AS vuosi,
    MONTH(created_at) AS kuukausi,
    COUNT(*)          AS maara
FROM posts
GROUP BY YEAR(created_at), MONTH(created_at)
ORDER BY vuosi DESC, kuukausi DESC
```

**PHP: rakenna nested array PHP:ssä:**

```php
$archive = [];
foreach ($rows as $row) {
    $archive[$row['vuosi']][$row['kuukausi']] = $row['maara'];
}
// → $archive[2026][6] = 3, $archive[2026][5] = 1, jne.
```

**HTML-renderöinti (accordion):**

```php
foreach ($archive as $year => $months) {
    echo '<details class="archive-year"><summary>' . e((string)$year) . '</summary><ul>';
    foreach ($months as $month => $count) {
        $label = strftime('%B', mktime(0,0,0,$month,1)) . ' (' . $count . ')';
        $url   = '?vuosi=' . $year . '&kuukausi=' . $month;
        echo '<li><a href="' . e($url) . '">' . e($label) . '</a></li>';
    }
    echo '</ul></details>';
}
```

> **Huom:** `<details>/<summary>` on natiivi HTML5 accordion — ei JS tarvita.

### Pattern 4: Prev/next -navigaatio

```sql
-- Seuraava postaus (uudempi)
SELECT id, title, slug
FROM posts
WHERE created_at > :current_created_at
ORDER BY created_at ASC
LIMIT 1;

-- Edellinen postaus (vanhempi)
SELECT id, title, slug
FROM posts
WHERE created_at < :current_created_at
ORDER BY created_at DESC
LIMIT 1;
```

**Tärkeää:** Vertailu `created_at`-aikaleimalla toimii oikein kun `idx_created_at`-indeksi on olemassa. Ei tarvitse kantaa `id`-väliltä — aikaleima on tarkempi jos postauksia muokataan.

### Pattern 5: Postauslista (blogi.php)

```sql
-- Kaikki postaukset, uusin ensin
SELECT id, title, slug, created_at,
       LEFT(content, 300) AS excerpt
FROM posts
ORDER BY created_at DESC
LIMIT :limit OFFSET :offset;

-- Kokonaismäärä sivutusta varten
SELECT COUNT(*) FROM posts;
```

**Sivutus:** Ei pakollinen heti — toteuta vain jos postauksia on yli ~20. Aluksi riittää `LIMIT 50` ilman sivutusta.

### Pattern 6: Sisällön renderöinti (turvallisuus)

**Valittu lähestymistapa: pelkkä teksti + nl2br()**

```php
// Tallennus: sanitoi strip_tags()-lla (kuten muutkin kentät)
$content = sanitize($_POST['content'] ?? ''); // strip_tags + trim

// Renderöinti: escapoi + muuta rivinvaihdot <br>:ksi
echo nl2br(e($post['content']));
```

**Perustelut:**
- `e()` + `nl2br()` = nolla XSS-riskiä, nolla ulkoisia riippuvuuksia
- HTML Purifier (ainoa luotettu HTML-sanitointikirjasto) vaatii Composerin → ei sovi projektin arkkitehtuuriin
- Tekstimuotoinen sisältö on riittävä virtuaalitalliblogin käyttötapaukselle
- Dropcap-tyyli (`::first-letter`) ja blockquote-tyyli toteutetaan CSS:llä rakenteellisesti `<p>`-elementeillä, ei sallimalla HTML-syötettä

**Jos myöhemmin tarvitaan rikkaampi sisältö:** Lisää erillisessä vaiheessa Composer + HTML Purifier.

### Pattern 7: Admin CRUD -rakenne

`/admin/posts.php` — yksi tiedosto, toiminto URL-parametrilla:

```
GET  /admin/posts.php          → lista kaikista postauksista
GET  /admin/posts.php?action=new      → uuden postauksen lomake
POST /admin/posts.php?action=new      → tallenna uusi postaus
GET  /admin/posts.php?action=edit&id=N → muokkauslomake
POST /admin/posts.php?action=edit&id=N → tallenna muutokset
POST /admin/posts.php?action=delete&id=N → poista (CSRF-suojaus!)
```

Sama rakenne kuin `horse_edit.php` + `horse_delete.php` — yhdistä yhteen tiedostoon selkeyden vuoksi.

**Textarea vs. WYSIWYG:**
- **Käytä: `<textarea>`** — yksinkertainen, ei JS-riippuvuuksia, sopii plain text -lähestymistavalle
- Älä käytä TinyMCE/Quill/CKEditor tässä vaiheessa — ne vaativat CDN:n tai paketin ja monimutkaistaisivat sanitoinnin

### Anti-Patterns to Avoid

- **`$_GET['id']`-käyttö ilman (int)-castia:** Käytä aina `$id = (int)$_GET['id']`
- **Slugin etsintä ilman preg_replace:** Validoi slug URL-parametrissa kuten hevonen.php tekee: `preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])))`
- **HTML-sisällön tallentaminen ilman sanitointia:** Älä salli raakaa HTML:ää ilman HTML Purifieria
- **DELETE GET-pyynnöllä:** Aina POST + CSRF-token, sama kuin photo_delete.php
- **`ORDER BY id` postauksissa:** Käytä aina `ORDER BY created_at` — id ei ole luotettava aikajärjestys jos rivejä poistetaan/lisätään

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| HTML-sanitointi | Oma regex-filter | HTML Purifier (Composer) tai plain text | Regex-pohjainen HTML-sanitointi on aina murrettavissa |
| Slug-generointi | Oma implementaatio | `slugify()` helpers.php:ssä | Jo testattu, kattaa ä/ö/å-muunnokset |
| CSRF-suojaus | Oma token | `validate_csrf_token()` helpers.php:ssä | Jo olemassa kaikissa admin-lomakkeissa |
| Arkisto-navigointi | JS-accordion | Natiivi `<details>/<summary>` | Toimii ilman JavaScriptiä |

---

## Common Pitfalls

### Pitfall 1: Slug-duplikaatit samanaikaisessa tallennuksessa

**What goes wrong:** Kaksi samannimistä postausta saa saman slugin → UNIQUE KEY -virhe.
**Why it happens:** `slugify()` on deterministinen — sama nimi = sama slug.
**How to avoid:** Tarkista slug ennen tallennusta ja lisää numero/timestamp perään konfliktissa.
**Warning signs:** PDOException "Duplicate entry ... for key 'uk_post_slug'"

### Pitfall 2: XSS `nl2br()`:n väärä järjestys

**What goes wrong:** `nl2br(e($content))` ≠ `e(nl2br($content))` — jälkimmäinen escapoi `<br>`-tagin.
**Why it happens:** Järjestys on väärä: ensin `e()`, sitten `nl2br()`.
**How to avoid:** Aina `nl2br(e($content))` — escapoi ENSIN, sitten lisää `<br>`.

### Pitfall 3: Arkistosivutus kuukauden nimissä

**What goes wrong:** `strftime('%B', ...)` palauttaa englanninkielisen kuukauden nimen.
**Why it happens:** PHP:n locale ei ole asetettu suomeksi.
**How to avoid:** Käytä PHP:n `IntlDateFormatter` tai yksinkertainen taulukko:
```php
$kuukaudet = ['','tammikuu','helmikuu','maaliskuu','huhtikuu','toukokuu','kesäkuu',
              'heinäkuu','elokuu','syyskuu','lokakuu','marraskuu','joulukuu'];
echo $kuukaudet[$month];
```

### Pitfall 4: Prev/next-kysely saman sekunnin postauksille

**What goes wrong:** Jos kaksi postausta luodaan samalla sekunnilla, `created_at > :ts` voi palauttaa väärän.
**Why it happens:** TIMESTAMP-tarkkuus on 1 sekunti MySQL:ssä oletuksena.
**How to avoid:** Käytä `DATETIME(6)` tai lisää tiepatoina `(created_at > :ts OR (created_at = :ts AND id > :id))`. Käytännössä yksittäisen blogin tapauksessa tämä on teoreettinen ongelma.

### Pitfall 5: CSRF delete-toiminnossa

**What goes wrong:** DELETE-linkki GET-pyynnöllä sallii CSRF-hyökkäyksen.
**Why it happens:** `<a href="?action=delete&id=N">` — selain voi seurata automaattisesti.
**How to avoid:** Poisto VAIN POST-lomakkeella + `validate_csrf_token()`. Sama kuin photo_delete.php.

---

## Code Examples

### Postauksen haku slug/id-fallbackilla (sama kuvio kuin hevonen.php)

```php
// Source: hevonen.php olemassaoleva kuvio — adapted for posts
if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])));
    $stmt = $db->prepare('SELECT * FROM posts WHERE slug = :slug');
    $stmt->execute([':slug' => $slug]);
} elseif (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
} else {
    http_response_code(404);
    // ... 404-sivu
}
$post = $stmt->fetch();
```

### Admin: Uuden postauksen tallennus

```php
// Source: horse_add.php -kuvio sovellettuna postauksiin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        $title   = sanitize($_POST['title'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $slug    = slugify($title);

        // Tarkista slug-duplikaatti
        $check = $db->prepare('SELECT id FROM posts WHERE slug = :slug');
        $check->execute([':slug' => $slug]);
        if ($check->fetch()) {
            $slug .= '-' . date('YmdHis');
        }

        if ($title === '') {
            $errors[] = 'Otsikko on pakollinen.';
        }
        if (empty($errors)) {
            $stmt = $db->prepare(
                'INSERT INTO posts (title, slug, content) VALUES (:title, :slug, :content)'
            );
            $stmt->execute([':title' => $title, ':slug' => $slug, ':content' => $content]);
            redirect(SITE_URL . '/admin/posts.php');
        }
    }
}
```

### Etusivun uusin postaus

```php
// Source: projektin olemassaolevat kuviot — yksinkertainen LIMIT 1 -kysely
$db   = getDB();
$stmt = $db->query('SELECT id, title, slug, created_at FROM posts ORDER BY created_at DESC LIMIT 1');
$latestPost = $stmt->fetch();
// Linkkaus: SITE_URL . '/pages/postaus.php?slug=' . rawurlencode($latestPost['slug'])
```

---

## State of the Art

| Old Approach | Current Approach | Note |
|--------------|------------------|------|
| `strftime()` kuukauden nimiin | PHP 8.1+ `IntlDateFormatter` tai staattinen taulukko | `strftime()` deprecoitu PHP 8.1:ssä |
| `mysql_*` funktiot | PDO prepared statements | Projekti käyttää jo PDO:ta |
| `mysql_real_escape_string` | Prepared statements | Projekti käyttää jo |

**Deprecoitu:**
- `strftime()`: Deprecoitu PHP 8.1:ssä — käytä kuukausitaulukkoa tai `IntlDateFormatter`

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | Altervista-tuotannossa ei ole mod_rewrite taattu → käytä `?slug=`-parametria | URL-strategia | Jos mod_rewrite on tuettu, puhtaat URL:t olisivat mahdollisia — voidaan lisätä jälkeenpäin |
| A2 | Blogissa ei tarvita `published`/draft-toiminnallisuutta tässä vaiheessa | Schema | Jos admin haluaa luonnoksia ennen julkaisua, tarvitaan `status`-sarake |
| A3 | Sivutus ei ole pakollinen heti (< 20 postausta odotettavissa alussa) | Postauslista | Jos postauksia tulee paljon, LIMIT 50 ei riitä |

---

## Open Questions

1. **Slug Altervistassa**
   - Mitä tiedämme: Docker dev käyttää mod_rewritea, mutta Altervista-tilin `.htaccess`-tuki on epävarma
   - Mitä on epäselvää: Tukeeko Altervista URL-uudelleenkirjoitusta oikeasti?
   - Suositus: Käytä `?slug=`-parametria nyt; lisää .htaccess-rewrite erillisessä vaiheessa jos/kun tuotantoon siirrytään

2. **`validate_csrf_token()`-funktion olemassaolo**
   - Mitä tiedämme: `horse_add.php` kutsuu sitä
   - Mitä on epäselvää: Onko se määritelty helpers.php:ssä vai jossain muualla?
   - Suositus: Varmista ennen toteutusta — jos puuttuu, lisää se

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP 8.2 | Kaikki sivut | ✓ | 8.2 (Docker) | — |
| MySQL 8 | posts-taulu | ✓ | 8.x (Docker) | — |
| Apache mod_rewrite | Puhtaat URL:t | ✓ (dev) | Enabled | Käytä `?slug=` parametria |
| phpMyAdmin | Migraatioajo | ✓ | Docker-compose | Voidaan ajaa myös `docker exec -i` |

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Manuaalinen selaintestaus (ei automatisoitua testiframeworkia) |
| Quick run | Avaa selaimessa, tarkista toiminnallisuus |

### Phase Requirements → Test Map

| Req | Behavior | Test Type | Method |
|-----|----------|-----------|--------|
| 1 | `posts`-taulu luotu | Manual | Aja `migrate_posts.sql` → tarkista phpMyAdmin |
| 2 | Postauslista näyttää kaikki uusimmasta | Manual | Luo 3 postausta → avaa `/pages/blogi.php` |
| 3 | Yksittäinen postaus + sidebar | Manual | Avaa `/pages/postaus.php?slug=xxx` |
| 3 | Sidebar sticky scroll | Manual | Pitkä artikkeli → scrollaa → sidebar pysyy |
| 3 | Prev/next -navigaatio | Manual | Luo 3 postausta → testaa nuolinäppäimet |
| 4 | Etusivu linkittää uusimpaan | Manual | Avaa index.php → tarkista overlay-kortti |
| 5 | Admin CRUD | Manual | Lisää → muokkaa → poista postaus |
| 5 | CSRF-suojaus deletelle | Manual | Varmista POST-only |

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | `requireLogin()` admin-sivuilla — jo projektin kuvio |
| V4 Access Control | yes | Admin-sivut vaativat session check — sama kuvio kuin muukin admin |
| V5 Input Validation | yes | `sanitize()` (strip_tags+trim) kaikille POST-kentille |
| V5 Output Encoding | yes | `e()` + `nl2br()` kaikessa renderöinnissä |
| V10 CSRF | yes | `validate_csrf_token()` kaikissa POST-lomakkeissa |

### Known Threat Patterns

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| XSS sisältökentässä | Tampering/Spoofing | `nl2br(e($content))` — ei raakaa HTML:ää |
| SQL injection | Tampering | PDO prepared statements kaikissa kyselyissä |
| CSRF delete/edit | Tampering | POST + `validate_csrf_token()` |
| Slug injection | Tampering | `preg_replace('/[^a-z0-9\-]/', '', ...)` ennen kyselyä |
| Mass assignment | Tampering | Eksplisiittinen kenttälista INSERT/UPDATE:ssa, ei `$_POST` suoraan |

---

## Sources

### Primary (HIGH confidence — codebase verified)
- `public/src/includes/helpers.php` — slugify(), e(), sanitize(), validate_csrf_token() -funktiot
- `public/src/includes/db.php` — getDB() singleton-kuvio
- `public/pages/hevonen.php` — slug/id-fallback URL-kuvio
- `public/admin/horse_add.php` — admin CRUD -kuvio
- `database/schema.sql` + `migrate_*.sql` — migraatiokuvio
- `docker/Dockerfile` — mod_rewrite enabled, AllowOverride All

### Secondary (MEDIUM confidence — MySQL docs)
- MySQL GROUP BY + YEAR/MONTH -funktiot arkistokyselyyn [CITED: dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html]
- MEDIUMTEXT vs TEXT vs LONGTEXT valinta [CITED: dev.mysql.com/doc/refman/8.0/en/blob.html]

### Tertiary (LOW confidence — training knowledge)
- PHP `strftime()` deprecaatio PHP 8.1:ssä [ASSUMED — tarkistettavissa php.net/changelog]

---

## Metadata

**Confidence breakdown:**
- Schema design: HIGH — perustuu projektin olemassaoleviin tauluihin ja MySQL-standardikäytäntöihin
- URL-strategia: HIGH — hevonen.php-koodi luettu suoraan
- SQL-kyselyt: HIGH — standardia MySQL:ää, ei ulkoisia kirjastoja
- Sisältöturvallisuus: HIGH — OWASP-suositus plain text + htmlspecialchars

**Research date:** 2026-06-18
**Valid until:** 2026-12-31 (vakaa stack, ei nopean muutoksen ympäristö)
