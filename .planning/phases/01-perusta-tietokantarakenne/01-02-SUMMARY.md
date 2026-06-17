# Plan 01-02 Summary: MySQL-skeema & PDO-yhteys

**Phase:** 01-perusta-tietokantarakenne
**Plan:** 02
**Status:** Complete
**Completed:** 2026-06-17

## What Was Built

MySQL-tietokantaskeema kaikille 7 taululle ja PDO-tietokantayhteys.

### Luodut tiedostot

| Tiedosto | Kuvaus |
|----------|--------|
| `database/schema.sql` | MySQL-skeema: 7 taulua, InnoDB, utf8mb4_unicode_ci |
| `database/seed.sql` | Testidata: 3 hevosta sukutauluineen, kilpailu, varsa |
| `public/src/includes/db.php` | PDO singleton, turvaasetukset, session_start, helpers.php autoinclude |

### Taulurakenne

| Taulu | Tarkoitus | Tärkeät kentät |
|-------|-----------|----------------|
| `disciplines` | Lajit (lookup) | name |
| `levels` | Tasot (lookup) | name |
| `horses` | Hevoset (päätaulu) | sire_id/dam_id (rekursiivinen), evm, profile_url, is_deleted, discipline_id, level_id |
| `horse_photos` | Hevoskuvat | sort_order, filename |
| `competitions` | Kisakalenteri | competition_date, placement, points |
| `foals` | Varsomiset | sire_id, dam_id (FK horses), status (born/expected) |
| `admin_users` | Admin-kirjautuminen | username, password (bcrypt) |

## Verification Results

- ✓ 7 taulua schema.sql:ssä
- ✓ horses.sire_id, horses.dam_id itseviittaavat (rekursiivinen sukutaulu)
- ✓ horses.evm (ulkopuolinen sukutieto), horses.profile_url (ulkoinen URL)
- ✓ horses.is_deleted (pehmeä poisto)
- ✓ horse_photos.sort_order (muokattava kuvajärjestys)
- ✓ foals.sire_id/dam_id viittaavat horses.id:hen
- ✓ db.php: PDO::ERRMODE_EXCEPTION, EMULATE_PREPARES=false
- ✓ db.php: session_start() turvallisilla cookieasetuksilla
- ✓ db.php: require_once helpers.php automaattisesti

## Key Decisions Applied

- D-06: is_deleted pehmeälle poistolle
- D-07/D-08/D-09: sire_id/dam_id rekursiiviselle sukutaululle, evm-lippu, profile_url
- D-10: disciplines/levels erilliset taulut
- D-11: horse_photos.sort_order
- D-12: competitions yksinkertainen (1 rivi/kilpailu)
- D-13: foals.sire_id/dam_id FK:t horses-tauluun
- D-14: admin_users.password bcrypt-tiivisteelle

## Commit

`151ad1d` — feat(01-02): MySQL-skeema ja PDO-tietokantayhteys
