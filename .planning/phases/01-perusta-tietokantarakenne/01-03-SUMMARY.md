# Plan 01-03 Summary: Integraatiotesti & Docker-ympäristö

**Phase:** 01-perusta-tietokantarakenne
**Plan:** 03
**Status:** Complete
**Completed:** 2026-06-17

## What Was Built

Testisivu, juuren uudelleenohjaus ja Docker-kehitysympäristö.

### Luodut tiedostot

| Tiedosto | Kuvaus |
|----------|--------|
| `public/pages/index.php` | Väliaikainen testisivu (Phase 2 korvaa), testaa DB-yhteyttä |
| `public/index.php` | 301-uudelleenohjaus pages/index.php:hen |
| `docker-compose.yml` | PHP 8.2 + MySQL 8 + phpMyAdmin |
| `docker/Dockerfile` | PHP 8.2-apache, pdo_mysql, mod_rewrite, GD |
| `docker/php.ini` | PHP-asetukset (kehitys: errors On, upload 10M) |
| `.env.example` | Ympäristömuuttujat Docker-testaukseen |
| `.gitignore` | .env, uploads/, .DS_Store |

### Docker-käyttöohjeet

```bash
# 1. Kopioi ympäristömuuttujat
cp .env.example .env

# 2. Käynnistä Docker-kontit
docker compose up -d --build

# 3. Avaa selaimessa:
#   Sivusto: http://localhost:8080
#   phpMyAdmin: http://localhost:8081

# 4. Pysäytä
docker compose down
```

## Checkpoint: Altervista → Docker

Käyttäjä pyysi Docker-ympäristöä paikallista testausta varten ennen Altervistaan siirtoa.
DB-tunnisteet luetaan `getenv()`:llä Docker-kehityksessä ja fallback-arvoilla Altervistalla.
Altervistalle siirrettäessä: päivitä `public/src/includes/db.php` define()-vakioihin oikeat tunnisteet.

## Verification Results

- ✓ public/pages/index.php käyttää db.php + header.php + footer.php include-ketjua
- ✓ public/index.php tekee 301-uudelleenohjauksen
- ✓ getHorsePedigree() olemassa helpers.php:ssä käyttäen PDO prepared statements
- ✓ Docker-ympäristö vastaa PHP 8.2 + MySQL = Altervistaa
- ✓ .env ei versioidu (.gitignore)
- ✓ Checkpoint: Docker-testaus mahdollista ennen tuotantoon siirtoa

## Commit

`3f42640` — feat(01-03): Docker-kehitysympäristö Altervistan vastine
