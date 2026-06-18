<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();

$competitions = $db->query(
    'SELECT c.id, c.competition_name, c.competition_date, c.placement, c.points, c.notes,
            h.id AS horse_id, h.name AS horse_name
     FROM competitions c
     JOIN horses h ON h.id = c.horse_id AND h.is_deleted = 0
     ORDER BY c.competition_date DESC, h.name ASC'
)->fetchAll();

$totalPoints = array_sum(array_column($competitions, 'points'));
$wins        = count(array_filter($competitions, fn($c) => $c['placement'] === '1'));

$pageTitle = 'Kilpailut';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <h1>Kilpailut</h1>
  <span style="font-size:0.78rem;color:var(--color-text-muted)"><?= count($competitions) ?> merkintää</span>
</div>
<div class="admin-body">

<div class="comp-stat-row">
  <div class="comp-stat-card">
    <div class="cs-num"><?= count($competitions) ?></div>
    <div class="cs-label">Kilpailua</div>
  </div>
  <div class="comp-stat-card">
    <div class="cs-num"><?= (int)$totalPoints ?></div>
    <div class="cs-label">Pistettä</div>
  </div>
  <div class="comp-stat-card">
    <div class="cs-num"><?= $wins ?></div>
    <div class="cs-label">Voittoa</div>
  </div>
</div>

<?php if (empty($competitions)): ?>
  <p style="color:var(--color-text-muted)">Ei kilpailumerkintöjä. Lisää tuloksia hevosen omalta Kilpailut-sivulta.</p>
<?php else: ?>
<div class="compact-list">
  <div class="compact-list-header" style="grid-template-columns:2fr 1.5fr 100px 60px 70px">
    <div>Kilpailu</div><div>Hevonen</div><div>Päivämäärä</div><div>Sij.</div><div>Pisteet</div>
  </div>
  <?php foreach ($competitions as $c):
    $pl = $c['placement'] ?? '';
    $pbClass = match($pl) { '1' => 'pbadge-1', '2' => 'pbadge-2', '3' => 'pbadge-3', default => 'pbadge-x' };
  ?>
  <div class="compact-list-row" style="grid-template-columns:2fr 1.5fr 100px 60px 70px">
    <div>
      <div class="cl-name"><?= e($c['competition_name']) ?></div>
      <?php if ($c['notes']): ?>
        <div class="cl-meta"><?= e(mb_strimwidth($c['notes'], 0, 60, '…')) ?></div>
      <?php endif; ?>
    </div>
    <div>
      <a href="<?= e(SITE_URL) ?>/admin/competitions.php?horse_id=<?= (int)$c['horse_id'] ?>"
         class="cl-name" style="text-decoration:none"><?= e($c['horse_name']) ?></a>
    </div>
    <div class="cl-meta"><?= $c['competition_date'] ? formatDate($c['competition_date']) : '—' ?></div>
    <div><span class="pbadge <?= $pbClass ?>"><?= $pl !== '' ? e($pl) : '—' ?></span></div>
    <div class="cl-mono"><?= $c['points'] !== null ? (int)$c['points'] : '—' ?></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div><!-- /.admin-body -->
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
