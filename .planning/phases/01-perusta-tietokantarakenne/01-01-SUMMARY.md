# Plan 01-01 Summary: Hakemistorakenne & PHP Sivupohja

**Phase:** 01-perusta-tietokantarakenne
**Plan:** 01
**Status:** Complete
**Completed:** 2026-06-17

## What Was Built

Virtuaalitallin koko hakemistorakenne ja PHP include -sivupohja.

### Luodut tiedostot

| Tiedosto | Kuvaus |
|----------|--------|
| `public/src/includes/config.php` | Sovelluksen konfiguraatio define()-vakioina |
| `public/src/includes/header.php` | HTML-sivupohjan yläosa, $page_title XSS-suojaus, nav.php include |
| `public/src/includes/nav.php` | Navigaatio aktiivi-linkin tunnistuksella |
| `public/src/includes/footer.php` | HTML-sivupohjan alaosa, copyright |
| `public/src/includes/helpers.php` | Apufunktiot: e(), sanitize(), redirect(), isLoggedIn(), requireLogin(), formatDate(), calculateAge(), getHorsePedigree() |
| `public/.htaccess` | Options -Indexes, src/-kansion esto |
| `public/src/includes/.htaccess` | Deny from all config.php + db.php |
| `public/assets/css/style.css` | Perustyylit (ruskea teema) |

### Hakemistorakenne

```
public/
  pages/          ← julkiset sivut (Phase 2)
  admin/          ← admin-paneeli (Phase 3)
  assets/css/     ← CSS
  assets/js/      ← JavaScript
  assets/img/     ← staattiset kuvat
  uploads/        ← hevosten kuvat (Phase 3)
  src/includes/   ← PHP-komponentit
database/         ← SQL-tiedostot
```

## Verification Results

- ✓ Hakemistorakenne luotu oikein
- ✓ config.php sisältää define()-vakiot
- ✓ header.php käyttää htmlspecialchars() $page_title:lle
- ✓ header.php sisällyttää nav.php:n
- ✓ helpers.php sisältää kaikki 8 apufunktiota (ml. getHorsePedigree())
- ✓ .htaccess Deny from all config.php + db.php:lle
- ✓ PHP ei ole asennettuna lokaalisti — tarkistukset tehty grep:illä (Altervista-projekti)

## Key Decisions Applied

- D-01/D-02/D-03: src/public-jako, sivut public/pages/
- D-15/D-16: .htaccess Deny + erilliset config.php ja db.php
- D-17: define()-vakiot
- D-18/D-19/D-20: header+footer+nav, $page_title-muuttuja
- D-07/D-08/D-09: getHorsePedigree() mukana helpers.php:ssä jo tässä vaiheessa

## Commit

`936af99` — feat(01-01): hakemistorakenne ja PHP sivupohja
