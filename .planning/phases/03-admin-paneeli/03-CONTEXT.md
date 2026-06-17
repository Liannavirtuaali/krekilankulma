# Phase 3 Context — Admin-paneeli

**Phase:** 03-admin-paneeli
**Tavoite:** Täysin toimiva admin-paneeli hevosten, kuvien, kilpailujen ja kasvatustietojen hallintaan, suojattuna salasanalla.

---

## Decisions

**D-01:** Autentikaatio toteutetaan PHP-sessioilla ja bcrypt-salasanatarkistuksella (ei JWT, ei OAuth).

**D-02:** Kaikki admin-sivut kutsuvat `requireLogin()` ensimmäisenä toimintona — helpers.php:stä löytyvä valmis funktio.

**D-03:** CSRF-suojaus: `bin2hex(random_bytes(32))` sessiossa, tarkistus `hash_equals()`:llä. Kaikissa POST-lomakkeissa.

**D-04:** Tiedostolataus: MIME tarkistetaan `finfo_file()`:llä (ei `$_FILES[]['type']`). Tiedostonimi generoidaan `uniqid('img_', true) . '.' . $ext` — alkuperäistä nimeä ei käytetä polkuna.

**D-05:** Hevosten poisto on pehmeä: `UPDATE horses SET is_deleted = 1, deleted_at = NOW()`. Ei DELETE-kyselyä.

**D-06:** Admin-sivupohja (admin_header.php, admin_footer.php) on täysin erillinen julkisesta header.php:stä ja nav.php:stä.

**D-07:** Kilpailut ja varsamerkinnät tunnistetaan `$_POST['action']`-kentästä (add/edit/delete) — kaikki toiminnot samalla sivulla.

**D-08:** Max 5 kuvaa per hevonen, enforsoitu palvelinpuolella ennen move_uploaded_file():ä.

**D-09:** Session fixation estetään: `session_regenerate_id(true)` kutsutaan onnistuneen kirjautumisen jälkeen.

---

## Requirements Map

| Vaatimus | Suunnitelma | Tiedosto(t) |
|----------|-------------|-------------|
| AUTH-01 | 03-01 | public/admin/login.php |
| AUTH-02 | 03-01 | public/admin/login.php |
| AUTH-03 | 03-01 | public/admin/login.php |
| AUTH-04 | 03-01 | Kaikki admin-sivut (requireLogin()) |
| AUTH-05 | 03-01 | public/admin/logout.php |
| ADMIN-01 | 03-02 | public/admin/horse_add.php |
| ADMIN-02 | 03-02 | public/admin/horse_edit.php |
| ADMIN-03 | 03-02 | public/admin/horse_delete.php |
| ADMIN-04 | 03-02 | public/admin/horses.php |
| ADMIN-05 | 03-02 | public/admin/horse_add.php + horse_edit.php |
| PHOTO-01 | 03-03 | public/admin/photos.php |
| PHOTO-02 | 03-03 | public/admin/photos.php |
| PHOTO-03 | 03-03 | public/admin/photos.php |
| PHOTO-04 | 03-03 | public/admin/photo_delete.php |
| PHOTO-05 | 03-03 | public/admin/photos.php |
| COMP-01 | 03-04 | public/admin/competitions.php |
| COMP-02 | 03-04 | public/admin/competitions.php |
| COMP-03 | 03-04 | public/admin/competitions.php |
| BREED-01 | 03-04 | public/admin/foals.php |
| BREED-02 | 03-04 | public/admin/foals.php |
| BREED-03 | 03-04 | public/admin/foals.php |

---

## Execution Waves

| Wave | Suunnitelma | Selitys |
|------|-------------|---------|
| 1 | 03-01 | Autentikaatio — kaiken muu perusta |
| 2 | 03-02 | Hevosten CRUD — riippuu 03-01:stä |
| 3 | 03-03, 03-04 | Kuvat + Kilpailut/kasvatus — rinnakkain, molemmat riippuvat 03-01:stä ja 03-02:sta |

---

## Deferred Ideas

- Admin-käyttäjien hallinta (lisää/poista admin-käyttäjiä) — ei tässä vaiheessa
- Kuvien järjestyksen drag-and-drop muuttaminen — ei tässä vaiheessa
- Kilpailutulosraportti / tilastot — ei tässä vaiheessa
- Hevosten massatuonti CSV:stä — ei tässä vaiheessa

---

## Security Checklist (pakollinen jokaiselle admin-sivulle)

```
[ ] requireLogin() sivun ensimmäinen kutsu (paitsi login.php ja logout.php)
[ ] CSRF-token kaikissa POST-lomakkeissa (bin2hex(random_bytes(32)) + hash_equals())
[ ] PDO prepared statements kaikissa DB-kyselyissä
[ ] e() kaikessa DB-datasta peräisin olevassa HTML-outputissa
[ ] (int) cast kaikille ID-parametreille (?id=, ?horse_id= jne.)
[ ] Tiedostolatauksessa: finfo_file() MIME-tarkistus + ALLOWED_EXTENSIONS + MAX_UPLOAD_SIZE
[ ] Tiedostonimet: uniqid('img_', true) + ext — ei alkuperäistä nimeä
[ ] Omistajuustarkistus kilpailuille ja varsoille (WHERE horse_id = :horse_id)
```

---

## File Structure (luotavat tiedostot)

```
public/admin/
  .htaccess                    ← hakemistosuojaus
  index.php                    ← dashboard
  login.php                    ← kirjautuminen
  logout.php                   ← uloskirjautuminen
  horses.php                   ← hevosten lista
  horse_add.php                ← uuden hevosen lisäys
  horse_edit.php               ← hevosen muokkaus
  horse_delete.php             ← pehmeä poisto
  photos.php                   ← kuvien hallinta
  photo_delete.php             ← kuvan poisto
  competitions.php             ← kilpailujen hallinta
  foals.php                    ← kasvatustietojen hallinta
  includes/
    admin_header.php           ← admin-sivupohja: ylätunniste
    admin_footer.php           ← admin-sivupohja: alatunniste
```
