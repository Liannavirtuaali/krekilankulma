# Phase 1: Perusta & Tietokantarakenne - Context

**Gathered:** 2026-06-17
**Status:** Ready for planning

<domain>
## Phase Boundary

Phase 1 toimittaa virtuaalitallin koko teknisen perustan: hakemistorakenteen, MySQL-tietokantaskeeman kaikkine tauluineen, PDO-tietokantayhteyden ja PHP include -sivupohjan. Tässä vaiheessa ei rakenneta yhtään julkista sivua — ainoastaan infrastructure ja data-malli, jonka päälle Phase 2–4 rakentuvat.

</domain>

<decisions>
## Implementation Decisions

### Hakemistorakenne
- **D-01:** Käytetään `src/public`-jakoa, molemmat `public/`-juurikansion alla (ei web-juuren ulkopuolista tasoa, koska Altervista-rajoitteet).
- **D-02:** Julkiset sivutiedostot sijoitetaan `public/pages/`-alakansioon (ei suoraan `public/`-juureen).
- **D-03:** Hakemistorakenne:
  ```
  public/
    pages/          ← julkiset sivut (index.php, hevoset.php, hevonen.php, kasvatus.php, yhteystiedot.php)
    admin/          ← hallintasivut
    assets/
      css/
      js/
      img/
    uploads/        ← hevosten kuvat
    src/
      includes/     ← header.php, footer.php, nav.php, db.php, config.php, helpers.php
  ```

### Tietokantaskeema — yleistä
- **D-04:** Etusivun sisältö (tallin kuvaus, tervetuloviesti) kovakoodataan PHP/HTML:ssa — ei tallenneta tietokantaan.
- **D-05:** Yhteystiedot-sivu on staattinen PHP/HTML — ei tallenneta tietokantaan.
- **D-06:** Pehmeä poisto hevosille: ensimmäinen "poista"-klikkaus asettaa `is_deleted = 1` (piilottaa, mutta data säilyy). Toinen "poista"-klikkaus (vahvistusikkuna/confirm) poistaa rivin pysyvästi (`DELETE FROM`).

### Tietokantaskeema — sukutaulu
- **D-07:** Sukutaulu rakennetaan rekursiivisesti `horses`-taulun sisäisten viittausten avulla. Hevosella on kentät `sire_id` (isä, viittaa `horses.id`) ja `dam_id` (emä, viittaa `horses.id`). Profiilisivua ladattaessa järjestelmä käy rekursiivisesti läpi 3 sukupolvea ja rakentaa sukutaulun dynaamisesti.
- **D-08:** Hevosella on `evm TINYINT(1)` -kenttä: `1` = hevonen ei ole olemassa virtuaalimaailmassa (ulkopuolinen sukutieto), `0` = hevosella on profiili tässä tai jossain virtuaalitallissa.
- **D-09:** Hevosella on `profile_url VARCHAR(255)` -kenttä: ulkopuolisen virtuaalitallin URL, jolloin sukutauluun voidaan linkittää sukulaiset jotka eivät asu tässä tallissa.

### Tietokantaskeema — taulut
- **D-10:** `disciplines`-taulu (lajit, esim. dressage, esteratsastus) ja `levels`-taulu (tasot, esim. alkeis, harrastaja, kilpa) ovat erilliset lookup-taulut. `horses`-taululla on `discipline_id` ja `level_id` viittauskentät.
- **D-11:** `horse_photos`-taulu sisältää `sort_order INT` -kentän. Admin voi muokata kuvien järjestystä. Kuvat näytetään `sort_order ASC` -järjestyksessä.
- **D-12:** `competitions`-taulu on yksinkertainen: yksi rivi per kilpailu. Kentät: `id`, `horse_id`, `competition_name`, `competition_date`, `placement`, `points`, `notes`, `created_at`.
- **D-13:** `foals`-taulu viittaa `horses`-tauluun ID:llä: `sire_id` (isä) ja `dam_id` (emä) ovat `horses.id`-viittauksia. Kentät: `id`, `horse_id` (tämän tallin emo/ori johon varsominen liittyy), `sire_id`, `dam_id`, `birth_year`, `gender`, `foal_name`, `status ENUM('born','expected')`, `created_at`.
- **D-14:** `admin_users`-taulu: `id`, `username`, `password` (bcrypt-tiiviste), `created_at`. Yksi rivi. Admin-tunnus luodaan asennuksen yhteydessä SQL-komennolla tai setup-skriptillä.

### Konfiguraatiosuojaus
- **D-15:** `config.php` suojataan `.htaccess`-säännöllä (`Deny from all`) suoralta HTTP-lataukselta. PHP voi silti käyttää sitä `require_once`:lla.
- **D-16:** Konfiguraatio jaetaan kahteen tiedostoon: `db.php` (tietokantayhteys PDO) ja `config.php` (muut asetukset: sivuston nimi, uploads-polku, sallitut kuvatyypit jne.).
- **D-17:** DB-tunnisteet tallennetaan PHP `define()`-vakioina (esim. `define('DB_HOST', 'localhost');`) — ei PHP-muuttujina.

### Sivupohjarakenne
- **D-18:** Sivupohja koostuu erillisistä tiedostoista: `header.php` + `footer.php`. Jokainen sivu tekee `require_once '../src/includes/header.php'` alussa ja `require_once '../src/includes/footer.php'` lopussa.
- **D-19:** Navigaatio on omassa `nav.php`-tiedostossaan, joka sisällytetään `header.php`:n kautta (`require_once 'nav.php'` header.php:ssa).
- **D-20:** Sivukohtainen `<title>`-otsikko toteutetaan `$page_title`-muuttujalla. Jokainen sivu asettaa sen ennen headerin sisällyttämistä: `$page_title = 'Hevoset'; require_once '../src/includes/header.php';`

### the agent's Discretion
- MySQL-taulujen charset/collation (suositeltava: `utf8mb4_unicode_ci` — tukee skandeja ja emojeja)
- PDO-yhteysasetukset (error mode, fetch mode -oletukset)
- `horses`-taulun tarkka kenttälista lukuun ottamatta yllä mainittuja (sire_id, dam_id, evm, profile_url, discipline_id, level_id)
- Helpers-funktion tarkka sisältö (esim. `sanitize()`, `redirect()`, `isLoggedIn()`)
- Uploads-hakemiston oikeudet Altervistassa

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Projektivaatimukset
- `.planning/REQUIREMENTS.md` — kaikki vaiheen 1 vaatimukset (DB-01–DB-05, HORSE-01–HORSE-10)
- `.planning/PROJECT.md` — projektin konteksti, rajoitteet ja Altervista-ympäristö

### Tietoturva
- OWASP Top 10 2025 (https://owasp.org/Top10/2025/) — tietoturvaperiaatteet jotka koskevat koko projektia, myös perustan rakentamista
- PHP PDO documentation — prepared statements, error handling, connection options

### No external specs
- Ei ulkoisia ADR- tai SPEC-tiedostoja — vaatimukset täysin kuvattu yllä ja REQUIREMENTS.md:ssä

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- Ei olemassa olevaa koodia — projekti rakennetaan kokonaan alusta

### Established Patterns
- Ei aiempia koodikuvioita — tämä vaihe luo ne

### Integration Points
- Tämä vaihe luo perustan johon Phase 2 (julkiset sivut) ja Phase 3 (admin) kytkeytyvät
- `db.php`:n PDO-yhteys on jaettu resurssi — kaikki myöhemmät vaiheet käyttävät sitä

</code_context>

<specifics>
## Specific Ideas

- **Sukutaulun rekursio:** Profiilisivulla PHP rakentaa sukutaulun rekursiivisesti: hae hevonen → hae sen sire_id + dam_id → hae niiden sire_id + dam_id → hae niiden sire_id + dam_id (3 sukupolvea = 14 solua). `evm=1`-hevoset näytetään nimellä + URL-linkillä mutta ilman profiilisivulinkkiä tässä tallissa.
- **Pehmeä poisto UI:** Admin-listauksen "Poista"-nappi → confirm-dialogi ("Oletko varma?") → `is_deleted=1`. Admin-listaus näyttää poistetut hevoset hälytystilassa "Poistettu" -merkinnällä + "Poista pysyvästi" -nappula → toinen confirm → `DELETE FROM`.
- **Altervista-rajoite:** Ei shell-accessia. Tietokantaskeema ajetaan Altervistan phpMyAdminissa SQL-importina. Uploads-hakemiston kirjoitusoikeudet asetetaan FTP:llä (chmod 755 tai 775).

</specifics>

<deferred>
## Deferred Ideas

- Kuvien järjestyksen drag-and-drop -UI adminissa — voidaan lisätä Phase 3:ssa tai jälkikäteen
- Useampi admin-käyttäjä — eksplisiittisesti rajattu projektin ulkopuolelle
- Tietokantamigraatiotyökalu (esim. Phinx) — turhan raskas Altervista-kontekstiin

</deferred>
