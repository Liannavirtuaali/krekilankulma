# Plan 02-03 Summary: Kasvatus ja Yhteystiedot

**Phase:** 02-julkiset-sivut
**Plan:** 03
**Status:** Complete
**Completed:** 2026-06-17

## Luodut tiedostot

| Tiedosto | Kuvaus |
|----------|--------|
| `public/pages/kasvatus.php` | Varsalista foals-taulusta, ryhmiteltynä odotetut/syntyneet |
| `public/pages/yhteystiedot.php` | Staattinen yhteystietosivu |

## Toteutus

- Kasvatussivu: `FIELD(status, 'expected', 'born')` järjestys, is_deleted=0 JOIN-ehdossa
- Yhteystiedot: staattinen, SITE_NAME vakiosta, ei DB-kyselyjä
- HTML-rakennekorjaus (verifier-havainto): poistettu `<main>` header.php:stä ja `</main>` footer.php:stä

## Commit
`df7dc7a`, `f942253` — feat + fix

