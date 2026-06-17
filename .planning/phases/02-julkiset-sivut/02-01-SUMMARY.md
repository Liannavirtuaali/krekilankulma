# Plan 02-01 Summary: Etusivu ja Hevoset-listaus

**Phase:** 02-julkiset-sivut
**Plan:** 01
**Status:** Complete
**Completed:** 2026-06-17

## Luodut / muutetut tiedostot

| Tiedosto | Toimenpide | Kuvaus |
|----------|-----------|--------|
| `public/pages/index.php` | Korvattu | Oikea etusivu: tallin esittely + 3 viimeisintä hevosta korteissa |
| `public/pages/hevoset.php` | Luotu | Kaikki tallin hevoset listauksena (nimi linkkinä, rotu, sukupuoli, ikä, kuva) |
| `public/assets/css/style.css` | Laajennettu | Hevoskortit, hero, taulukot, galleria, profiilisivu, nappi |

## Tärkeät päätökset

- `evm=0`-suodatus: listaussivut näyttävät vain oikeat tallin hevoset (ei sukutauluviittauksia)
- Kuvaplaceholder: 🐴-emoji jos hevosella ei ole kuvia
- nav.php oli jo oikein — ei muutoksia

## Commit
`7e06dbf` — feat(02-01): etusivu ja hevoset-listaussivu
