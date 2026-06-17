<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();
$horseCount = $db->query('SELECT COUNT(*) FROM horses WHERE is_deleted = 0')->fetchColumn();
$photoCount = $db->query('SELECT COUNT(*) FROM horse_photos')->fetchColumn();
$compCount  = $db->query('SELECT COUNT(*) FROM competitions')->fetchColumn();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Tervetuloa, <?= e($_SESSION['admin_username']) ?>!</h1>

<div class="stat-cards">
  <div class="stat-card">
    <div class="stat-num"><?= (int)$horseCount ?></div>
    <div class="stat-label">Hevosta</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int)$photoCount ?></div>
    <div class="stat-label">Kuvaa</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int)$compCount ?></div>
    <div class="stat-label">Kilpailua</div>
  </div>
</div>

<p>
  <a href="<?= e(SITE_URL) ?>/admin/horses.php" class="btn">Hevosten hallinta</a>
  <a href="<?= e(SITE_URL) ?>/admin/horse_add.php" class="btn" style="margin-left:0.5rem">+ Lisää hevonen</a>
</p>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
