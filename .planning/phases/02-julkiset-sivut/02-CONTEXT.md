# Phase 02 — Julkiset sivut: CONTEXT.md

**Phase:** 02-julkiset-sivut
**Päivitetty:** 2026-06-17

---

## Tavoite

Kaikki viisi julkista sivua toimivat ja näyttävät live-dataa tietokannasta.

## Vaatimusten kattavuus

| Vaatimus | Suunnitelma | Toteutus |
|----------|-------------|----------|
| PUB-01 — Etusivu (esittely + hevoset) | 02-01 Task 1 | `public/pages/index.php` |
| PUB-02 — Hevoset-listaus | 02-01 Task 2 | `public/pages/hevoset.php` |
| PUB-03 — Hevosen profiilisivu (perustiedot, sukutaulu, kilpailut, galleria) | 02-02 Tasks 1–3 | `public/pages/hevonen.php` |
| PUB-04 — Kasvatussivu (varsat) | 02-03 Task 1 | `public/pages/kasvatus.php` |
| PUB-05 — Yhteystiedot | 02-03 Task 2 | `public/pages/yhteystiedot.php` |
| PUB-06 — Navigaatio PHP include:n kautta | 02-01 Task 3, 02-03 Task 3 | `public/src/includes/nav.php` |

---

## Lukitut päätökset

### D-01: PHP require_once -polku
Kaikki sivut käyttävät `require_once __DIR__ . '/../src/includes/db.php'` — db.php lataa automaattisesti config.php:n ja helpers.php:n. Ei lisätä erillisiä require-rivejä muille include-tiedostoille.

### D-02: XSS-suojaus e()-funktiolla
Kaikki tietokannasta tuleva data ja dynaamiset arvot tulostetaan `e()` -funktion kautta. Tämä on ehdoton vaatimus kaikissa PHP-sivuissa (OWASP A03).

### D-03: GET-parametrien validointi
`?id=` -parametri validoidaan aina: `$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;` + tarkistus `$id > 0`. Validointi tapahtuu ennen mitään tietokantakyselyä.

### D-04: 404-käsittely profiilisivulla
Jos hevosta ei löydy tai `is_deleted = 1`: aseta `http_response_code(404)`, näytä ystävällinen suomenkielinen viesti, lopeta suoritus `exit`:llä. Ei paljasteta tietokantarakennetta virheviesteissä.

### D-05: Kuvien URL-rakenne
Kuvat ladataan `UPLOADS_URL . e($filename)` -muodossa. `UPLOADS_URL` on määritelty config.php:ssä (`SITE_URL . '/uploads/'`). Ei muodosteta polkuja muilla tavoilla.

### D-06: evm-hevoset sukutaulussa
evm=1 (ulkoiset hevoset): näytetään nimi + linkki `profile_url`:iin jos se on asetettu. Ei-evm hevoset: linkki `SITE_URL . '/pages/hevonen.php?id=' . (int)$ancestor['id']`. `profile_url` tulostetaan `e()`:n kautta.

### D-07: Sivurakenne (header/footer/nav)
Jokainen sivu sisällyttää `header.php`:n (joka sisällyttää nav.php:n automaattisesti) ja `footer.php`:n. Navigaatiolinkit osoittavat tiedostoihin `/pages/`-hakemistossa.

### D-08: Ei admin-toiminnallisuutta
Tässä vaiheessa ei toteuteta lomakkeita, kirjautumista eikä muita admin-toimintoja. Yhteystietosivu on staattinen.

### D-09: Suomi sisältökielenä
Kaikki käyttäjälle näkyvä teksti on suomeksi: otsikot, virheilmoitukset, tyhjyysviestit, sukupuoliarvot (ori/tamma/ruuna), ryhmäotsikot jne.

---

## Lykätyt ideat (ei toteuteta tässä vaiheessa)

- Yhteydenottolomake yhteystietosivulle (→ myöhempi vaihe)
- Hevosen hakutoiminto / suodatus (→ myöhempi vaihe)
- Kuvien lightbox-galleria (→ myöhempi vaihe)
- Kasvatuksen tilastosivu (→ myöhempi vaihe)
- Hevosten vertailu (→ myöhempi vaihe)

---

## Tekniset reunaehdot

- **PHP-versio:** Docker-kontissa (ks. Dockerfile)
- **Tietokanta:** MySQL/MariaDB PDO-yhteyden kautta `getDB()` -singletoni
- **Istunto:** db.php käynnistää session automaattisesti
- **Vakiot:** `SITE_URL`, `SITE_NAME`, `UPLOADS_URL`, `MAX_PHOTOS_PER_HORSE` — määritelty config.php:ssä
- **Teema:** Ruskea ratsastusaihe (#3d2b1f otsikko, #f9f7f4 cream-tausta) — olemassa oleva style.css

## Suoritusaallot

| Aalto | Suunnitelma | Riippuvuudet |
|-------|-------------|--------------|
| 1 | 02-01 (Etusivu + Hevoset-listaus) | — |
| 2 | 02-02 (Profiilisivu) | 02-01 (nav.php korjattu) |
| 2 | 02-03 (Kasvatus + Yhteystiedot) | 02-01 (nav.php korjattu) |

02-02 ja 02-03 voidaan toteuttaa rinnakkain kun 02-01 on valmis.
