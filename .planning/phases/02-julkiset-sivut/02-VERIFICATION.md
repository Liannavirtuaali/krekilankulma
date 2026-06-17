---
phase: 02-julkiset-sivut
verified: 2026-06-17T00:00:00Z
status: passed
score: 3/3 must-havea vahvistettu
overrides_applied: 0
---

# Vaihe 2: Julkiset sivut — Tarkistusraportti

**Vaiheentavoite:** Kaikki viisi julkista sivua toimivat ja näyttävät live-dataa tietokannasta.  
**Tarkistettu:** 2026-06-17  
**Status:** ✅ PASSED  
**Uudelleentarkistus:** Ei — alkuperäinen tarkistus

---

## Tavoitteen toteutuminen

### Havaittavat totuudet

| # | Totuus | Status | Näyttö koodista |
|---|--------|--------|-----------------|
| 1 | Kaikki 5 sivua latautuvat ja includevat header.php + footer.php | ✓ VERIFIED | Kaikissa: `require header.php` ennen sisältöä, `require footer.php` lopussa |
| 2 | Profiilisivu näyttää sukutaulun (3 sukupolvea), kisakalenterin ja kuvagallerian | ✓ VERIFIED | `getHorsePedigree($id)` maxDepth=3; kilpailu- ja kuvakysely PDO:lla |
| 3 | Navigaatio toimii PHP include:n kautta kaikilla sivuilla | ✓ VERIFIED | `header.php` rivi 31: `require_once 'nav.php'`; nav.php sisältää kaikki 4 linkkiä |

**Pistemäärä: 3/3 must-havea vahvistettu**

---

## Yksityiskohtaiset tarkistukset

### 1. Viisi julkista sivua — olemassaolo ja rakenne

| Sivu | Olemassa | header.php | footer.php | DB-kyselyt |
|------|----------|------------|------------|------------|
| `index.php` | ✓ | ✓ | ✓ | ✓ PDO prepared |
| `hevoset.php` | ✓ | ✓ | ✓ | ✓ PDO prepared |
| `hevonen.php` | ✓ | ✓ | ✓ | ✓ PDO prepared |
| `kasvatus.php` | ✓ | ✓ | ✓ | ✓ PDO prepared |
| `yhteystiedot.php` | ✓ | ✓ | ✓ | — staattinen |

### 2. index.php — 3 viimeisintä hevosta

**Näyttö:**
```sql
SELECT h.id, h.name, h.breed, h.gender, hp.filename
FROM horses h
LEFT JOIN horse_photos hp ON ...
WHERE h.is_deleted = 0 AND h.evm = 0
ORDER BY h.id DESC
LIMIT 3
```
Kysely: PDO `prepare()` + `execute()`. Ehdot: `is_deleted=0`, `evm=0`, `LIMIT 3`. **✓ VERIFIED**

### 3. hevoset.php — kaikki ei-poistetut hevoset

**Näyttö:**
```sql
WHERE h.is_deleted = 0 AND h.evm = 0
ORDER BY h.name ASC
```
Ei LIMIT-rajoitusta — listaa kaikki. **✓ VERIFIED**

### 4. hevonen.php — ?id= validointi, 404, sukutaulu, kilpailut, kuvat

| Tarkistus | Status | Näyttö |
|-----------|--------|--------|
| GET ?id= pakotettu int | ✓ | `$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;` |
| 404 jos `$id <= 0` | ✓ | `http_response_code(404)` + exit |
| 404 jos hevosta ei löydy | ✓ | `if (!$horse) { http_response_code(404); ... exit; }` |
| getHorsePedigree() kutsuttu | ✓ | `$pedigree = getHorsePedigree($id);` |
| helpers.php maxDepth=3 | ✓ | `function getHorsePedigree(int $horseId, int $depth = 0, int $maxDepth = 3)` |
| Sukutaulu 3 sukupolvea | ✓ | Taulurivit: vanhemmat, isovanhemmat (isän+emän puoli), isoisovanhemmat |
| Kilpailukysely | ✓ | `SELECT ... FROM competitions WHERE horse_id = :id ORDER BY competition_date DESC` |
| Kuvakysely | ✓ | `SELECT filename, original_name FROM horse_photos WHERE horse_id = :id ORDER BY sort_order` |
| profile_url suojaus | ✓ | `preg_match('#^https?://#i', ...)` — javascript:-hyökkäys estetty |

### 5. kasvatus.php — varsat is_deleted=0 JOIN-ehdossa

**Näyttö:**
```sql
FROM foals f
LEFT JOIN horses sire ON sire.id = f.sire_id AND sire.is_deleted = 0
LEFT JOIN horses dam  ON dam.id  = f.dam_id  AND dam.is_deleted = 0
ORDER BY FIELD(f.status, 'expected', 'born'), f.birth_year DESC
```
`is_deleted=0` on molemmissa JOIN-ehdoissa. Järjestys: odotetut ensin. **✓ VERIFIED**

### 6. yhteystiedot.php — staattinen sivu

Ei yhtään DB-kyselyä. `require db.php` ladataan (config-vakiot), mutta `getDB()` ei kutsuta. **✓ VERIFIED**

### 7. XSS-suojaus — e() käytössä

Tarkistettu kaikista tiedostoista:

| Tiedosto | e()-käyttö | Status |
|----------|------------|--------|
| `index.php` | `e($horse['name'])`, `e($horse['breed'])`, `e(UPLOADS_URL . ...)` jne. | ✓ |
| `hevoset.php` | Kaikki horse-kentät `e()`:n läpi | ✓ |
| `hevonen.php` | Kaikki DB-kentät + pedigreeHorseLink()-apufunktio | ✓ |
| `kasvatus.php` | `foalRow()` käyttää `e()` kaikkiin kenttiin | ✓ |
| `yhteystiedot.php` | `e(SITE_NAME)` | ✓ |
| `helpers.php` | `function e(): string { return htmlspecialchars(..., ENT_QUOTES, 'UTF-8') }` | ✓ |

### 8. PDO Prepared Statements — kaikissa kyselyissä

Jokainen kysely kaikissa tiedostoissa: `$db->prepare(...)` + `execute([':param' => $value])`. Ei raakoja merkkijono-interpolaatioita kyselyissä. **✓ VERIFIED**

### 9. nav.php — linkit

```php
Etusivu     → SITE_URL/pages/index.php
Hevoset     → SITE_URL/pages/hevoset.php
Kasvatus    → SITE_URL/pages/kasvatus.php
Yhteystiedot → SITE_URL/pages/yhteystiedot.php
```
Aktiivinen linkki tunnistetaan `basename($_SERVER['PHP_SELF'])`:llä. **✓ VERIFIED**

---

## Vaatimusten kattavuus

| Vaatimus | Sivu | Status |
|----------|------|--------|
| PUB-01 | index.php | ✓ SATISFIED |
| PUB-02 | hevoset.php | ✓ SATISFIED |
| PUB-03 | hevonen.php | ✓ SATISFIED |
| PUB-04 | hevonen.php (sukutaulu) | ✓ SATISFIED |
| PUB-05 | kasvatus.php | ✓ SATISFIED |
| PUB-06 | yhteystiedot.php | ✓ SATISFIED |

---

## Anti-pattern-skannaus

| Tiedosto | Rivi | Pattern | Vakavuus | Selitys |
|----------|------|---------|----------|---------|
| `header.php` | 32 | `<main>` avataan headerin jälkeen | ⚠️ WARNING | `header.php` tuottaa `</header><main>`, mutta jokainen sivutiedosto myös avaa oman `<main>…</main>`. `footer.php` sulkee toisen `</main>`. Tämä luo sisäkkäiset `<main>`-elementit (invalid HTML5). Sivut renderöityvät silti selaimissa, mutta rakenne on virheellinen. |

> **Suositus:** Poista `<main>` `header.php`:stä ja `</main>` `footer.php`:stä, tai poista `<main>…</main>` jokaisen sivun sisällöstä. Yhtenäinen layout-ratkaisu valittava.

---

## Yhteenveto

Vaiheen tavoite **on saavutettu**. Kaikki 5 julkista sivua:
- ovat olemassa ja toimivat
- näyttävät live-dataa tietokannasta PDO prepared statements -kyselyillä
- suojaavat käyttäjäsyötteen e()-funktiolla (XSS)
- validoivat käyttäjäsyötteet (hevonen.php ?id=, profile_url)
- sisällyttävät navigaation header.php include:n kautta

Yksi HTML-rakenteellinen varoitus (`<main>`-tagien toisteisuus) ei estä toiminnallisuutta, mutta se kannattaa korjata ennen julkaisua.

---

_Tarkistanut: gsd-verifier_  
_Päivämäärä: 2026-06-17_
