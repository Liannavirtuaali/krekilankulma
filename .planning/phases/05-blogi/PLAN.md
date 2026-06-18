# Phase 05 — Blogi: Execution Plan

**Phase goal:** Tallinpitäjä voi kirjoittaa blogipostauksia adminissa; vierailijat lukevat ne julkisella puolella postauslistalta tai yksittäiseltä sivulta sticky sidebar -arkistolla.

---

## Plans overview

| # | Nimi | Aalto | Tiedostot |
|---|------|-------|-----------|
| 01 | Database — posts-taulu | 1 | `database/migrate_posts.sql` |
| 02 | CSS — blogi-tyylit | 1 | `public/assets/css/style.css` |
| 03 | Julkinen blogisivu | 2 | `public/pages/blogi.php`, `public/pages/postaus.php` |
| 04 | Admin CRUD | 2 | `public/admin/posts.php`, `public/admin/post_delete.php` |
| 05 | Integraatio — etusivu + nav | 3 | `public/pages/index.php`, `public/src/includes/nav.php` |

---

## Plan 01 — Database: posts-taulu

**Aalto:** 1  
**Riippuu:** —  
**Tiedostot:** `database/migrate_posts.sql`

### Tavoite
Luodaan `posts`-taulu migraatiotiedostona, joka on idempotent (IF NOT EXISTS) ja ajettavissa phpMyAdminista tai mysql CLI:stä.

### Tehtävät

**Tehtävä 1 — Luo migrate_posts.sql**

Luo tiedosto `database/migrate_posts.sql` seuraavalla rakenteella (seuraa olemassa olevien migraatioiden tyyliä):

```sql
-- migrate_posts.sql
-- Aja phpMyAdminissa tai: mysql -u root -p talli < database/migrate_posts.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(255) NOT NULL,
  `content`    MEDIUMTEXT   NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Ei seed-dataa — taulukko alkaa tyhjänä.

### Verifiointi

```bash
# Aja migraatio Docker-konttia vasten
docker compose exec db mysql -u root -proot talli < database/migrate_posts.sql
# Tarkista
docker compose exec db mysql -u root -proot talli -e "DESCRIBE posts;"
```

Odotettu tulos: taulu `posts` olemassa, kolumnit `id, title, slug, content, created_at, updated_at` näkyvissä.

### UAT
- [ ] Migraatio ajettavissa kahdesti ilman virheitä (idempotent)
- [ ] `SHOW CREATE TABLE posts` näyttää `utf8mb4_unicode_ci` ja UNIQUE slug-indeksin

---

## Plan 02 — CSS: blogi-tyylit

**Aalto:** 1  
**Riippuu:** —  
**Tiedostot:** `public/assets/css/style.css`

### Tavoite
Lisätään kaikki blogi-spesifiset CSS-luokat `style.css`:n loppuun. Ei muuteta olemassa olevia luokkia.

### Tehtävät

**Tehtävä 1 — Lisää CSS-säännöt style.css:n loppuun**

Lisää tiedoston `public/assets/css/style.css` loppuun seuraavat lohkot:

```css
/* ============================================================
   BLOGI — postauslista (blogi.php)
   ============================================================ */

.post-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Listakortit: horisontaalinen asettelu kuten list-card-pattern */
.post-list-card {
  display: flex;
  gap: 1.25rem;
  background: var(--color-surface-warm);
  border-radius: 8px;
  padding: 1.25rem 1.5rem;
  text-decoration: none;
  color: inherit;
  transition: box-shadow .15s;
}
.post-list-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.post-list-card__body {
  flex: 1;
}
.post-list-card__title {
  font-family: var(--font-serif);
  font-size: var(--text-lg);
  color: var(--color-dark);
  margin: 0 0 .4rem;
}
.post-list-card__date {
  font-size: var(--text-sm);
  color: var(--color-muted);
  margin-bottom: .6rem;
}
.post-list-card__excerpt {
  font-size: var(--text-base);
  color: var(--color-text);
  line-height: 1.6;
  margin: 0;
}

/* ============================================================
   BLOGI — yksittäinen postaus (postaus.php)
   ============================================================ */

/* Kaksipalstainen layout: artikkeli + sticky sidebar */
.post-layout {
  display: grid;
  grid-template-columns: 1fr 260px;
  gap: 2.5rem;
  align-items: start;
}
@media (max-width: 860px) {
  .post-layout {
    grid-template-columns: 1fr;
  }
}

/* Artikkeli */
.post-article__title {
  font-family: var(--font-serif);
  font-size: var(--text-2xl);
  margin: 0 0 .3rem;
}
.post-article__date {
  font-size: var(--text-sm);
  color: var(--color-muted);
  margin-bottom: 1.5rem;
  display: block;
}

/* Dropcap ensimmäisessä kappaleessa */
.post-body p:first-child::first-letter {
  float: left;
  font-family: var(--font-serif);
  font-size: 3.2rem;
  line-height: .85;
  margin: .1rem .45rem 0 0;
  color: var(--color-gold);
}

.post-body p {
  line-height: 1.75;
  margin: 0 0 1.1rem;
}

/* Lainauslaatikko */
.post-body blockquote {
  border-left: 4px solid var(--color-gold);
  background: var(--color-surface-warm);
  margin: 1.25rem 0;
  padding: .85rem 1.25rem;
  font-style: italic;
  color: var(--color-dark);
}
.post-body blockquote p {
  margin: 0;
}

/* ============================================================
   BLOGI — sticky sidebar
   ============================================================ */

.post-sidebar {
  position: sticky;
  top: 68px;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* Edellinen / seuraava navigaatio */
.post-prevnext {
  background: var(--color-surface-warm);
  border-radius: 8px;
  padding: .85rem 1rem;
  font-size: var(--text-sm);
  display: flex;
  flex-direction: column;
  gap: .5rem;
}
.post-prevnext a {
  color: var(--color-gold);
  text-decoration: none;
}
.post-prevnext a:hover { text-decoration: underline; }

/* Arkisto */
.archive-sidebar {
  background: var(--color-dark);
  border-radius: 8px;
  overflow: hidden;
}
.archive-sidebar__header {
  background: var(--color-dark);
  color: var(--color-gold);
  font-family: var(--font-serif);
  font-size: var(--text-base);
  padding: .75rem 1rem;
  border-bottom: 2px solid var(--color-gold);
  margin: 0;
}

/* Accordion: <details>/<summary> ilman JS */
.archive-sidebar details {
  border-bottom: 1px solid rgba(255,255,255,.08);
}
.archive-sidebar details:last-child { border-bottom: none; }
.archive-sidebar summary {
  padding: .6rem 1rem;
  cursor: pointer;
  font-weight: 600;
  font-size: var(--text-sm);
  color: var(--color-cream);
  list-style: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
}
.archive-sidebar summary::-webkit-details-marker { display: none; }
.archive-sidebar summary::after {
  content: '▸';
  font-size: .75rem;
  color: var(--color-gold);
  transition: transform .2s;
}
.archive-sidebar details[open] summary::after { transform: rotate(90deg); }
.archive-sidebar__months {
  list-style: none;
  margin: 0;
  padding: 0 0 .4rem;
}
.archive-sidebar__months li a {
  display: block;
  padding: .3rem 1.5rem;
  font-size: var(--text-sm);
  color: var(--color-cream);
  text-decoration: none;
  opacity: .8;
}
.archive-sidebar__months li a:hover {
  opacity: 1;
  color: var(--color-gold);
}

/* ============================================================
   BLOGI — admin-lomake (posts.php)
   ============================================================ */

.post-admin-form textarea {
  width: 100%;
  min-height: 260px;
  resize: vertical;
  font-family: var(--font-sans);
  font-size: var(--text-base);
  padding: .5rem .75rem;
  border: 1px solid var(--color-border, #ccc);
  border-radius: 4px;
  background: #fff;
}
```

### Verifiointi
- Selaa `public/assets/css/style.css` — uudet luokat löytyvät tiedoston lopusta
- Ei syntax-virheitä: `grep -c '{' public/assets/css/style.css` ja `grep -c '}' public/assets/css/style.css` palauttavat saman luvun

### UAT
- [ ] Luokat `post-layout`, `post-body`, `archive-sidebar`, `post-prevnext`, `post-list-card` ovat tiedostossa
- [ ] Muihin sivuihin ei tullut visuaalisia regressioita

---

## Plan 03 — Julkinen blogisivu

**Aalto:** 2  
**Riippuu:** Plan 01 (posts-taulu), Plan 02 (CSS)  
**Tiedostot:** `public/pages/blogi.php`, `public/pages/postaus.php`

### Tavoite
Luodaan kaksi julkista sivua: postauslista ja yksittäinen artikkeli sticky sidebar -arkistolla.

---

### Tehtävä 1 — blogi.php: postauslista

Luo `public/pages/blogi.php`. Seuraa `hevoset.php`-sivun rakennetta (require db.php, $page_title, header/footer).

```php
<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Blogi';
$db = getDB();

$stmt = $db->query(
    'SELECT id, title, slug, content, created_at
     FROM posts
     ORDER BY created_at DESC'
);
$posts = $stmt->fetchAll();
```

HTML-rakenne (`require header.php` jälkeen):

```html
<main class="container" style="padding: 2rem 1rem;">
  <h1>Blogi</h1>

  <?php if (empty($posts)): ?>
    <p>Ei vielä postauksia.</p>
  <?php else: ?>
    <ul class="post-list">
      <?php foreach ($posts as $post):
        // Näytä ensimmäiset ~200 merkkiä tekstistä
        $excerpt = mb_substr($post['content'], 0, 200, 'UTF-8');
        if (mb_strlen($post['content'], 'UTF-8') > 200) $excerpt .= '…';
      ?>
      <li>
        <a class="post-list-card"
           href="<?= e(SITE_URL) ?>/pages/postaus.php?slug=<?= rawurlencode($post['slug']) ?>">
          <div class="post-list-card__body">
            <h2 class="post-list-card__title"><?= e($post['title']) ?></h2>
            <span class="post-list-card__date"><?= formatDate($post['created_at']) ?></span>
            <p class="post-list-card__excerpt"><?= e($excerpt) ?></p>
          </div>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</main>
```

---

### Tehtävä 2 — postaus.php: yksittäinen artikkeli + sticky sidebar

Luo `public/pages/postaus.php`. Noudattaa `hevonen.php`-sivun slug/id-logiikkaa.

**Slug-käsittely (turvallistettu):**
```php
if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])));
    $stmt = $db->prepare('SELECT * FROM posts WHERE slug = :slug');
    $stmt->execute([':slug' => $slug]);
} elseif (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
} else {
    // 404
}
$post = $stmt->fetch();
if (!$post) { /* 404 */ }
```

**Prev/next (kaksi erillistä prepared statement):**
```php
$stmtPrev = $db->prepare(
    'SELECT id, title, slug FROM posts
     WHERE created_at < :created_at ORDER BY created_at DESC LIMIT 1'
);
$stmtPrev->execute([':created_at' => $post['created_at']]);
$prev = $stmtPrev->fetch();

$stmtNext = $db->prepare(
    'SELECT id, title, slug FROM posts
     WHERE created_at > :created_at ORDER BY created_at ASC LIMIT 1'
);
$stmtNext->execute([':created_at' => $post['created_at']]);
$next = $stmtNext->fetch();
```

**Arkistokyselty:**
```php
$stmtArchive = $db->query(
    'SELECT YEAR(created_at) AS yr, MONTH(created_at) AS mo, COUNT(*) AS cnt
     FROM posts
     GROUP BY YEAR(created_at), MONTH(created_at)
     ORDER BY yr DESC, mo DESC'
);
$archiveRows = $stmtArchive->fetchAll();

// Rakenna nested array: $archive[$yr][$mo] = $cnt
$archive = [];
foreach ($archiveRows as $row) {
    $archive[$row['yr']][$row['mo']] = (int)$row['cnt'];
}

// Suomalaiset kuukaudet — ei strftime() (deprecated PHP 8.1+)
$MONTHS_FI = [
    1=>'Tammikuu',2=>'Helmikuu',3=>'Maaliskuu',4=>'Huhtikuu',
    5=>'Toukokuu',6=>'Kesäkuu',7=>'Heinäkuu',8=>'Elokuu',
    9=>'Syyskuu',10=>'Lokakuu',11=>'Marraskuu',12=>'Joulukuu'
];
```

**HTML-layout (post-layout grid):**
```html
<main class="container" style="padding: 2rem 1rem;">
  <div class="post-layout">

    <!-- Artikkeli -->
    <article>
      <h1 class="post-article__title"><?= e($post['title']) ?></h1>
      <span class="post-article__date"><?= formatDate($post['created_at']) ?></span>
      <div class="post-body">
        <?= nl2br(e($post['content'])) ?>
      </div>
    </article>

    <!-- Sticky sidebar -->
    <aside class="post-sidebar">

      <!-- Prev/next -->
      <nav class="post-prevnext" aria-label="Postausnavigaatio">
        <?php if ($prev): ?>
          <a href="...?slug=...">← <?= e($prev['title']) ?></a>
        <?php endif; ?>
        <?php if ($next): ?>
          <a href="...?slug=...">→ <?= e($next['title']) ?></a>
        <?php endif; ?>
        <?php if (!$prev && !$next): ?>
          <span style="color:var(--color-muted)">Ei muita postauksia</span>
        <?php endif; ?>
      </nav>

      <!-- Arkisto -->
      <div class="archive-sidebar">
        <h2 class="archive-sidebar__header">Arkisto</h2>
        <?php foreach ($archive as $yr => $months): ?>
          <details>
            <summary><?= (int)$yr ?></summary>
            <ul class="archive-sidebar__months">
              <?php foreach ($months as $mo => $cnt): ?>
                <li>
                  <a href="<?= e(SITE_URL) ?>/pages/blogi.php?year=<?= $yr ?>&amp;month=<?= $mo ?>">
                    <?= e($MONTHS_FI[$mo]) ?> (<?= $cnt ?>)
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </details>
        <?php endforeach; ?>
      </div>

    </aside>
  </div>
</main>
```

> **Huom:** Arkistolinkit viittaavat `blogi.php?year=X&month=Y`. Arkistosuodatus toteutetaan Tehtävässä 3 alla.

---

### Tehtävä 3 — blogi.php: arkistosuodatus year/month-parametreilla

Lisää `blogi.php`:hen vuosi/kuukausi-suodatuslogiikka. Parametrit validoidaan `(int)`-castingilla (injektiosuojaus).

```php
$yearFilter  = isset($_GET['year'])  ? (int)$_GET['year']  : 0;
$monthFilter = isset($_GET['month']) ? (int)$_GET['month'] : 0;

if ($yearFilter > 0 && $monthFilter > 0) {
    $stmt = $db->prepare(
        'SELECT id, title, slug, content, created_at
         FROM posts
         WHERE YEAR(created_at) = :y AND MONTH(created_at) = :m
         ORDER BY created_at DESC'
    );
    $stmt->execute([':y' => $yearFilter, ':m' => $monthFilter]);
} elseif ($yearFilter > 0) {
    $stmt = $db->prepare(
        'SELECT id, title, slug, content, created_at
         FROM posts
         WHERE YEAR(created_at) = :y
         ORDER BY created_at DESC'
    );
    $stmt->execute([':y' => $yearFilter]);
} else {
    $stmt = $db->query(
        'SELECT id, title, slug, content, created_at
         FROM posts
         ORDER BY created_at DESC'
    );
}
$posts = $stmt->fetchAll();
```

Lisää myös suodatusteksti sivun yläosaan kun suodatus on aktiivinen:
```php
<?php if ($yearFilter > 0 && $monthFilter > 0): ?>
  <p>Näytetään: <?= e($MONTHS_FI[$monthFilter] ?? $monthFilter) ?> <?= $yearFilter ?>
     — <a href="blogi.php">Näytä kaikki</a></p>
<?php endif; ?>
```

### Verifiointi
```bash
# Lisää testipostaus suoraan kantaan
docker compose exec db mysql -u root -proot talli -e \
  "INSERT INTO posts (title, slug, content) VALUES ('Testijuttu', 'testijuttu', 'Sisältöä tässä.');"

# Tarkista listasivu
curl -s http://localhost:8080/pages/blogi.php | grep 'Testijuttu'

# Tarkista artikkeli
curl -s "http://localhost:8080/pages/postaus.php?slug=testijuttu" | grep 'post-layout'
```

### UAT
- [ ] Postauslista näyttää postaukset uusimmasta vanhimpaan
- [ ] Tyhjä lista näyttää "Ei vielä postauksia" -viestin
- [ ] Postaussivu avautuu slugilla ja id:llä
- [ ] 404-virhe tuntemattomalla slugilla
- [ ] Sidebar on sticky ja pysyy näkyvissä vierittäessä
- [ ] Arkisto-accordion toimii ilman JavaScriptiä
- [ ] Dropcap näkyy ensimmäisen kappaleen ensimmäisellä kirjaimella
- [ ] `nl2br(e(...))` — HTML-entiteetit näkyvät oikein, ei XSS

---

## Plan 04 — Admin CRUD: postausten hallinta

**Aalto:** 2  
**Riippuu:** Plan 01 (posts-taulu)  
**Tiedostot:** `public/admin/posts.php`, `public/admin/post_delete.php`

### Tavoite
Admin voi listata, lisätä, muokata ja poistaa postauksia. Seuraa olemassa olevaa `horse_add.php`-konventiota: CSRF-tokenin tarkistus, `requireLogin()`, `sanitize()`, slug-törmäyksen hallinta.

---

### Tehtävä 1 — posts.php: lista + lisää/muokkaa-lomake

Luo `public/admin/posts.php`. Yksi sivu hoitaa sekä listan että lomakkeen (sama tiedosto, URL-parametri `?action=edit&id=N` tai `?action=new`).

**Rakenne:**
1. `requireLogin()` ja CSRF-token heti tiedoston alussa
2. POST-käsittelijä: tarkista `csrf_token`, `sanitize()` kaikille kentille, slug-logiikka
3. GET-renderöinti: lista, tai lomake `action=new`/`action=edit`

**POST — lisää/muokkaa:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        $title   = sanitize($_POST['title']   ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $edit_id = (int)($_POST['edit_id'] ?? 0);

        if ($title === '') $errors[] = 'Otsikko on pakollinen.';
        if ($content === '') $errors[] = 'Sisältö on pakollinen.';

        if (empty($errors)) {
            // Generoi slug (sama logiikka kuin horse_add.php)
            $slug = slugify($title);
            $base = $slug;
            $n = 2;
            while (true) {
                $chk = $db->prepare(
                    'SELECT id FROM posts WHERE slug = :slug' .
                    ($edit_id ? ' AND id != :id' : '')
                );
                $params = [':slug' => $slug];
                if ($edit_id) $params[':id'] = $edit_id;
                $chk->execute($params);
                if (!$chk->fetch()) break;
                $slug = $base . '-' . $n++;
            }

            if ($edit_id > 0) {
                $db->prepare('UPDATE posts SET title=:t, slug=:s, content=:c WHERE id=:id')
                   ->execute([':t'=>$title, ':s'=>$slug, ':c'=>$content, ':id'=>$edit_id]);
            } else {
                $db->prepare('INSERT INTO posts (title, slug, content) VALUES (:t, :s, :c)')
                   ->execute([':t'=>$title, ':s'=>$slug, ':c'=>$content]);
            }
            redirect(SITE_URL . '/admin/posts.php');
        }
    }
}
```

**Lomake:**
- Kentät: `title` (text, required), `content` (textarea, required), piilotettu `edit_id` muokkauksessa
- CSRF-token piilotettu input `<input type="hidden" name="csrf_token" value="...">`
- Luokka `post-admin-form` textarea-elementille
- Peruuta-linkki takaisin listaan

**Lista:**
- Taulukko: Otsikko | Luotu | Muokkaa | Poista
- Poista: lomake jossa `action=post_delete.php`, `method=POST`, CSRF-token, `id`, confirm `onclick="return confirm('Poistetaanko postaus?')"`

---

### Tehtävä 2 — post_delete.php: poisto

Luo `public/admin/post_delete.php`. Seuraa vastaavaa `horse_delete.php`-konventiota.

```php
<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/posts.php');
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect(SITE_URL . '/admin/posts.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $db = getDB();
    $db->prepare('DELETE FROM posts WHERE id = :id')->execute([':id' => $id]);
}

redirect(SITE_URL . '/admin/posts.php');
```

> **Ei soft-deletea** — posts-taulukossa ei ole `is_deleted`-kolumnia (toisin kuin horses).

---

### Tehtävä 3 — admin_header.php: Postaukset-navigaatiolinkki

Muokkaa `public/admin/includes/admin_header.php`. Lisää sivupalkkiin "📝 Postaukset" -linkki muiden admin-linkkien joukkoon.

Etsi kohta jossa on muut sivupalkkilinkit (esim. Hevoset, Kilpailut, Kuvat) ja lisää:
```html
<a class="admin-nav-item <?= strpos($_SERVER['PHP_SELF'], '/admin/posts') !== false ? 'active' : '' ?>"
   href="<?= SITE_URL ?>/admin/posts.php">
  📝 Postaukset
</a>
```

Ilman tätä linkkiä admin löytää sivun vain kirjoittamalla URL:n käsin — ei täytä "admin voi hallita postauksia" -tavoitetta käytännössä.

### Verifiointi
```bash
# Kirjaudu admin-sivulle ja testaa manuaalisesti:
# 1. http://localhost:8080/admin/posts.php
# 2. Lisää postaus → uudelleenohjaus listaan
# 3. Muokkaa postaus → uudelleenohjaus listaan
# 4. Poista postaus → confirm-dialog, sitten poisto
```

### UAT
- [ ] Kirjautumaton käyttäjä ohjataan `/admin/login.php`
- [ ] Lomake ilman CSRF-tokenia hylätään virheilmoituksella
- [ ] Tyhjä otsikko tai sisältö → lomake palaa virheineen (POST-data säilyy)
- [ ] Kaksi postausta samalla otsikolla → jälkimmäinen saa `-2`-liitteen slugiin
- [ ] Muokkaus päivittää `updated_at`-kentän automaattisesti (ON UPDATE CURRENT_TIMESTAMP)
- [ ] Poisto: confirm-dialog näkyy, poistaminen onnistuu
- [ ] Admin-sivupalkissa näkyy "📝 Postaukset" -linkki joka johtaa posts.php:hen

---

## Plan 05 — Integraatio: etusivu + navigaatio

**Aalto:** 3  
**Riippuu:** Plan 01 (posts-taulu), Plan 03 (blogi.php olemassa)  
**Tiedostot:** `public/pages/index.php`, `public/src/includes/nav.php`

### Tavoite
1. Etusivun overlay-kortti (`overlay-card--news`) linkkaa uusimpaan postaukseen kannasta
2. Navigaatioon lisätään "Ajankohtaista" -linkki

---

### Tehtävä 1 — index.php: uusin postaus overlay-korttiin

Muokkaa `public/pages/index.php`. Lisää uusi tietokantakysely olemassa olevien kyselyjen jälkeen (ennen `require header.php`):

```php
// Uusin postaus etusivun korttia varten
$latestPost = null;
try {
    $stmtPost = $db->query(
        'SELECT title, slug, content, created_at FROM posts ORDER BY created_at DESC LIMIT 1'
    );
    $latestPost = $stmtPost->fetch() ?: null;
} catch (PDOException $e) {
    // Taulu ei vielä olemassa — näytetään placeholder
    $latestPost = null;
}
```

Muokkaa `overlay-card--news`-kortin HTML: korvaa staattinen `href="#"` ja kovakoodattu sisältö dynaamisella datalla:

```php
<?php
$newsHref = $latestPost
    ? e(SITE_URL) . '/pages/postaus.php?slug=' . rawurlencode($latestPost['slug'])
    : e(SITE_URL) . '/pages/blogi.php';
$newsTitle   = $latestPost ? e($latestPost['title']) : 'Ajankohtaista';
$newsExcerpt = $latestPost
    ? e(mb_substr($latestPost['content'], 0, 120, 'UTF-8')) . '…'
    : 'Lue uusimmat kuulumiset tallin blogista.';
$newsDate    = $latestPost ? formatDate($latestPost['created_at']) : '';
?>
<a class="overlay-card overlay-card--news" href="<?= $newsHref ?>">
  <img src="https://picsum.photos/seed/winter1/320/160" alt="Ajankohtaista">
  <div class="uutinen-tag" style="margin-bottom:.5rem;">📰 Ajankohtaista</div>
  <h3><?= $newsTitle ?></h3>
  <p><?= $newsExcerpt ?></p>
  <div class="uutinen-footer" style="margin-top:auto;padding-top:.75rem;">
    <span class="card-date"><?= $newsDate ?></span>
    <span class="overlay-card-link">Lue lisää →</span>
  </div>
</a>
```

> `try/catch (PDOException)` varmistaa, ettei etusivu hajoa jos posts-taulua ei ole ajettu (graceful degradation).

---

### Tehtävä 2 — nav.php: Ajankohtaista-linkki

Muokkaa `public/src/includes/nav.php`. Lisää uusi `<li>` navigaation loppuun (ennen `</ul>`):

```php
<li><a href="<?= SITE_URL ?>/pages/blogi.php"
       <?= (strpos($uri, '/pages/blogi') === 0 || strpos($uri, '/pages/postaus') === 0)
           ? ' class="active"' : '' ?>>Ajankohtaista</a></li>
```

Aktiivinen luokka aktivoituu sekä `blogi.php`- että `postaus.php`-sivuilla.

### Verifiointi
```bash
# Etusivu — lataa ja tarkista
curl -s http://localhost:8080/ | grep 'postaus.php'

# Navigaatio — tarkista linkki
curl -s http://localhost:8080/ | grep 'Ajankohtaista'
```

### UAT
- [ ] Etusivun kortti linkkaa uusimman postauksen slugiin (kun postaus on olemassa)
- [ ] Etusivun kortti toimii gracefully ilman postauksia (ei PHP-virhettä, placeholder-teksti näkyvissä)
- [ ] "Ajankohtaista" näkyy navigaatiossa
- [ ] Navigaatiossa aktiivinen luokka näkyy blogi.php- ja postaus.php-sivuilla

---

## Threat model

### Trust boundaries

| Raja | Kuvaus |
|------|--------|
| Selain → julkinen PHP | URL-parametrit `?slug=` ja `?id=` ovat epäluotettavaa syötettä |
| Admin-selain → admin PHP | POST-lomakkeet `title`, `content`, `csrf_token` |
| PHP → MySQL | Kaikki kyselyt käyttävät PDO prepared statements -menetelmää |

### STRIDE-uhkarekisteri

| Uhka ID | Kategoria | Komponentti | Toimenpide | Mitigointi |
|---------|-----------|-------------|------------|------------|
| T-05-01 | Tampering | `?slug=` URL-parametri | Mitigoida | `preg_replace('/[^a-z0-9\-]/', '', ...)` ennen PDO-kyselyä — kuten hevonen.php |
| T-05-02 | Injection (SQL) | Kaikki DB-kyselyt | Mitigoida | Kaikki kyselyt käyttävät PDO::prepare + named placeholders — ei raw interpolaatiota |
| T-05-03 | XSS | post.content → selain | Mitigoida | `nl2br(e($content))` — HTML kielletty, htmlspecialchars pakollinen kaikissa outputeissa |
| T-05-04 | CSRF | admin/posts.php POST, post_delete.php POST | Mitigoida | `validate_csrf_token()` — sama pattern kuin horse_add.php |
| T-05-05 | Broken Access Control | /admin/posts.php, /admin/post_delete.php | Mitigoida | `requireLogin()` heti tiedoston alussa |
| T-05-06 | Tampering | slug-generointi törmäyslogiikka | Mitigoida | UNIQUE KEY kannassa + looppi `-2`, `-3`… ennen INSERT — ei duplikaatteja |
| T-05-07 | Info Disclosure | PDOException etusivulla (posts ei olemassa) | Mitigoida | `try/catch` index.php:ssä, ei näytetä exception-viestejä käyttäjälle |
| T-05-08 | Injection | `?year=` ja `?month=` blogi.php-suodatuksessa | Mitigoida | `(int)$_GET['year']` ja `(int)$_GET['month']` — cast kokonaisluvuksi ennen kyselyä |

---

## Yleinen verifiointi (koko vaihe)

```bash
# 1. Migraatio ajettavissa
docker compose exec db mysql -u root -proot talli < database/migrate_posts.sql

# 2. Lisää testipostauksia
docker compose exec db mysql -u root -proot talli -e "
  INSERT INTO posts (title, slug, content) VALUES
  ('Talvipäivä', 'talvipaiva', 'Ensimmäinen lumi tuli tänään.'),
  ('Uusi varsa', 'uusi-varsa', 'Odotettu varsa syntyi eilen yöllä.'),
  ('Kilpailukausi auki', 'kilpailukausi-auki', 'Tänä vuonna osallistumme kuuteen kilpailuun.');
"

# 3. Tarkista julkiset sivut
curl -s http://localhost:8080/pages/blogi.php | grep 'post-list-card'
curl -s "http://localhost:8080/pages/postaus.php?slug=talvipaiva" | grep 'post-layout'
curl -s "http://localhost:8080/pages/postaus.php?slug=talvipaiva" | grep 'archive-sidebar'

# 4. Tarkista etusivu
curl -s http://localhost:8080/ | grep 'Kilpailukausi auki'

# 5. Tarkista navigaatio
curl -s http://localhost:8080/ | grep 'Ajankohtaista'

# 6. Tarkista 404
curl -o /dev/null -s -w "%{http_code}" "http://localhost:8080/pages/postaus.php?slug=ei-ole"
# Odotettu: 404
```

---

## Koko vaiheen UAT-lista

- [ ] **Plan 01:** `posts`-taulu luotu, migraatio idempotent
- [ ] **Plan 02:** Kaikki CSS-luokat lisätty, ei regressioita muilla sivuilla
- [ ] **Plan 03:** Postauslista näyttää kaikki postaukset uusimmasta vanhimpaan
- [ ] **Plan 03:** Tyhjä lista näyttää "Ei vielä postauksia"
- [ ] **Plan 03:** Postaussivu avautuu slugilla ja id:llä
- [ ] **Plan 03:** Tuntematon slug → HTTP 404
- [ ] **Plan 03:** Sidebar sticky, näkyy vierittäessä
- [ ] **Plan 03:** `<details>/<summary>` accordion ilman JS
- [ ] **Plan 03:** Dropcap ensimmäisessä kappaleessa
- [ ] **Plan 03:** Arkistolinkit suodattavat blogi.php:n kuukauden mukaan
- [ ] **Plan 04:** Kirjautumaton käyttäjä → login.php
- [ ] **Plan 04:** CSRF-tarkistus estää väärennetyn POST:n
- [ ] **Plan 04:** Tyhjä pakollinen kenttä → virheilmoitus
- [ ] **Plan 04:** Slug-törmäys → automaattinen `-2`-liite
- [ ] **Plan 04:** Muokkaus päivittää `updated_at`
- [ ] **Plan 04:** Poisto toimii confirm-dialogilla
- [ ] **Plan 05:** Etusivu-kortti → uusin postaus
- [ ] **Plan 05:** Etusivu toimii ilman postauksia (graceful degradation)
- [ ] **Plan 05:** "Ajankohtaista" navigaatiossa, aktiivinen luokka oikein
