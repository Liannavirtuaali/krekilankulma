# Virtuaalitalli

PHP/MySQL-pohjainen virtuaalitallin hallintajärjestelmä. Kehitysympäristö toimii Dockerissa ja vastaa Altervistan tuotantoympäristöä (PHP 8.2.31).

## Vaatimukset

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) asennettuna ja käynnissä

## Paikallinen kehitys (Docker)

### 1. Kloonaa repositorio ja siirry hakemistoon

```bash
git clone <repo-url>
cd GSD-test02
```

### 2. Luo ympäristömuuttujatiedosto

```bash
cp .env.example .env
```

Tiedostoa ei tarvitse muuttaa paikallisessa kehityksessä — oletusarvot toimivat suoraan.

### 3. Käynnistä kontit

```bash
docker compose up -d --build
```

Ensimmäinen käynnistys lataa ja rakentaa Docker-imagen (~2–5 min). Seuraavat käynnistykset ovat nopeampia.

### 4. Avaa selaimessa

| Palvelu | Osoite |
|---------|--------|
| Sivusto | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

Tietokanta (schema + testidata) ajetaan automaattisesti sisään ensimmäisellä käynnistyksellä.

### 5. Pysäytä kontit

```bash
docker compose down
```

Tietokanta säilyy pysäyttämisen jälkeen Docker-volumessa.

### Tietokannan nollaus (tyhjennä ja aja uudelleen)

```bash
docker compose down -v   # poistaa myös db-volumen
docker compose up -d
```

---

## Hyödyllisiä komentoja

```bash
# Näytä konttien loki (seuraa virheilmoituksia)
docker compose logs -f web

# Avaa PHP-konttiin komentotulkki
docker compose exec web bash

# Tarkista PHP-versio ja laajennukset
docker compose exec web php -v
docker compose exec web php -m

# Käynnistä uudelleen (esim. php.ini muutoksen jälkeen)
docker compose restart web
```

---

## Projektirakenne

```
.
├── docker/
│   ├── Dockerfile      # PHP 8.2 Apache -image
│   └── php.ini         # PHP-asetukset (vastaa Altervistaa)
├── database/
│   ├── schema.sql      # Tietokantarakenne (7 taulua)
│   └── seed.sql        # Testidata kehitykseen
├── public/             # Web-juuri (tämä hakemisto Apache-palvelimelle)
│   ├── index.php       # 301-uudelleenohjaus
│   ├── pages/          # Sivut
│   ├── assets/         # CSS, kuvat
│   └── src/
│       └── includes/   # Yhteiset PHP-tiedostot (config, db, helpers, header…)
├── .env.example        # Ympäristömuuttujien malli
├── docker-compose.yml  # Docker-palvelumääritys
└── README.md
```

---

## Altervistaan siirtäminen (tuotanto)

1. **Lataa tiedostot FTP:llä** — siirrä `public/`-kansion sisältö Altervistan `public_html/`-hakemistoon
2. **Aja tietokantaskeema** — `database/schema.sql` phpMyAdminilla Altervistalla
3. **Päivitä tunnisteet** — avaa `public/src/includes/db.php` ja muuta `define()`-vakiot:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'altervistan_db_nimi');
   define('DB_USER', 'altervistan_kayttaja');
   define('DB_PASS', 'altervistan_salasana');
   ```
4. **Tarkista `SITE_URL`** — `public/src/includes/config.php`:ssä oikea domain
5. **Poista tai suojaa** testidata (älä aja `seed.sql` tuotantoon)

> **Huom.** Altervista käyttää FastCGI:tä (ei mod_php), mutta .htaccess ja mod_rewrite toimivat normaalisti.

---

## Docker vs. Altervista — vastaavuudet

| Ominaisuus | Docker (kehitys) | Altervista (tuotanto) |
|------------|-----------------|----------------------|
| PHP-versio | 8.2 | 8.2.31 |
| Tietokanta | MySQL 8.0 | MySQL 8 |
| Aikavyöhyke | Europe/Rome | Europe/Rome |
| GD-kirjasto | JPEG, PNG, WebP, FreeType | JPEG, PNG, WebP, FreeType |
| ImageMagick | imagick 3.x | imagick 3.8.1 |
| PDO | mysql, sqlite | mysql, sqlite |
| OPcache | päällä, JIT pois | päällä, JIT pois |
| Laajennukset | bcmath, intl, gettext, gmp, exif, sodium | sama |
| PHP-suoritustapa | mod_php (Apache) | FastCGI | 
| `display_errors` | On | Off *(Altervistalla kytke pois)* |
