<?php
require_once __DIR__ . '/../src/includes/db.php';

$db = getDB();

// Hae postaus slugin tai id:n perusteella (T-05-01: slug sanitoitu preg_replace)
if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])));
    $stmt = $db->prepare('SELECT * FROM posts WHERE slug = :slug');
    $stmt->execute([':slug' => $slug]);
} elseif (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
} else {
    http_response_code(404);
    $page_title = 'Postausta ei löydy';
    require __DIR__ . '/../src/includes/header.php';
    echo '<main class="container" style="padding:2rem 1rem;"><p>Postausta ei löydy.</p></main>';
    require __DIR__ . '/../src/includes/footer.php';
    exit;
}

$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $page_title = 'Postausta ei löydy';
    require __DIR__ . '/../src/includes/header.php';
    echo '<main class="container" style="padding:2rem 1rem;"><p>Postausta ei löydy.</p></main>';
    require __DIR__ . '/../src/includes/footer.php';
    exit;
}

$page_title = $post['title'];

// Edellinen postaus (vanhempi)
$stmtPrev = $db->prepare(
    'SELECT id, title, slug FROM posts
     WHERE created_at < :created_at ORDER BY created_at DESC LIMIT 1'
);
$stmtPrev->execute([':created_at' => $post['created_at']]);
$prev = $stmtPrev->fetch();

// Seuraava postaus (uudempi)
$stmtNext = $db->prepare(
    'SELECT id, title, slug FROM posts
     WHERE created_at > :created_at ORDER BY created_at ASC LIMIT 1'
);
$stmtNext->execute([':created_at' => $post['created_at']]);
$next = $stmtNext->fetch();

// Arkistokysely
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
    1=>'Tammikuu', 2=>'Helmikuu', 3=>'Maaliskuu', 4=>'Huhtikuu',
    5=>'Toukokuu', 6=>'Kesäkuu',  7=>'Heinäkuu',  8=>'Elokuu',
    9=>'Syyskuu', 10=>'Lokakuu', 11=>'Marraskuu', 12=>'Joulukuu'
];

require __DIR__ . '/../src/includes/header.php';
?>

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
          <a href="<?= e(SITE_URL) ?>/pages/postaus.php?slug=<?= rawurlencode($prev['slug']) ?>">
            ← <?= e($prev['title']) ?>
          </a>
        <?php endif; ?>
        <?php if ($next): ?>
          <a href="<?= e(SITE_URL) ?>/pages/postaus.php?slug=<?= rawurlencode($next['slug']) ?>">
            → <?= e($next['title']) ?>
          </a>
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
                  <a href="<?= e(SITE_URL) ?>/pages/blogi.php?year=<?= (int)$yr ?>&amp;month=<?= (int)$mo ?>">
                    <?= e($MONTHS_FI[$mo] ?? (string)$mo) ?> (<?= (int)$cnt ?>)
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </details>
        <?php endforeach; ?>
        <?php if (empty($archive)): ?>
          <p style="padding:.75rem 1rem;color:var(--color-cream);font-size:var(--text-sm);">Ei postauksia.</p>
        <?php endif; ?>
      </div>

    </aside>
  </div>
</main>

<?php require __DIR__ . '/../src/includes/footer.php'; ?>
