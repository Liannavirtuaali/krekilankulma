<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Blogi';
$db = getDB();

// Suomalaiset kuukaudet — ei strftime() (deprecated PHP 8.1+)
$MONTHS_FI = [
    1=>'Tammikuu', 2=>'Helmikuu', 3=>'Maaliskuu', 4=>'Huhtikuu',
    5=>'Toukokuu', 6=>'Kesäkuu',  7=>'Heinäkuu',  8=>'Elokuu',
    9=>'Syyskuu', 10=>'Lokakuu', 11=>'Marraskuu', 12=>'Joulukuu'
];

// Arkistosuodatus — parametrit validoidaan (int)-castingilla (T-05-08)
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

require __DIR__ . '/../src/includes/header.php';
?>

<main class="container" style="padding: 2rem 1rem;">
  <h1>Blogi</h1>

  <?php if ($yearFilter > 0 && $monthFilter > 0): ?>
    <p style="margin:.75rem 0 1.25rem;">
      Näytetään: <?= e($MONTHS_FI[$monthFilter] ?? (string)$monthFilter) ?> <?= $yearFilter ?>
      — <a href="blogi.php">Näytä kaikki</a>
    </p>
  <?php elseif ($yearFilter > 0): ?>
    <p style="margin:.75rem 0 1.25rem;">
      Näytetään: <?= $yearFilter ?>
      — <a href="blogi.php">Näytä kaikki</a>
    </p>
  <?php endif; ?>

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

<?php require __DIR__ . '/../src/includes/footer.php'; ?>
