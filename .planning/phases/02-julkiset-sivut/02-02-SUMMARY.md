# Plan 02-02 Summary: Hevosen profiilisivu

**Phase:** 02-julkiset-sivut
**Plan:** 02
**Status:** Complete
**Completed:** 2026-06-17

## Luodut tiedostot

| Tiedosto | Kuvaus |
|----------|--------|
| `public/pages/hevonen.php` | Hevosen profiilisivu: perustiedot, sukutaulu, kisakalenteri, kuvagalleria |

## Toteutus

- GET `?id=` validointi: `(int)$_GET['id']` + `> 0`, kaksi 404-polkua (ei id / ei löydy)
- Sukutaulu: `getHorsePedigree()` 3 sukupolveen, taulukkoesitys
- evm-hevoset: `profile_url` validoitu `preg_match('#^https?://#i')` (javascript:-esto)
- Kisakalenteri: `ORDER BY competition_date DESC`
- Kuvagalleria: `ORDER BY sort_order ASC LIMIT 5`

## Commit
`df7dc7a` — feat(02-02/03): profiilisivu, kasvatus, yhteystiedot
