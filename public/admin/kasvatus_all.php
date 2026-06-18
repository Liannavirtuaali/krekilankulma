<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();

$foals = $db->query(
    'SELECT f.id, f.foal_name, f.birth_year, f.gender, f.status,
            h.id AS horse_id, h.name AS horse_name,
            s.name AS sire_name, d.name AS dam_name
     FROM foals f
     JOIN horses h ON h.id = f.horse_id AND h.is_deleted = 0
     LEFT JOIN horses s ON s.id = f.sire_id AND s.is_deleted = 0
     LEFT JOIN horses d ON d.id = f.dam_id  AND d.is_deleted = 0
     ORDER BY f.birth_year DESC, h.name ASC, f.foal_name ASC'
)->fetchAll();

$statusLabels = ['expected' => 'Odotettu', 'born' => 'Syntynyt'];

$pageTitle = 'Kasvatus';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <h1>Kasvatus</h1>
  <span style="font-size:0.78rem;color:var(--color-text-muted)"><?= count($foals) ?> varsamerkintää</span>
</div>
<div class="admin-body">
<?php if (empty($foals)): ?>
  <p style="color:var(--color-text-muted)">Ei varsamerkintöjä. Lisää varsia hevosen omalta Kasvatus-sivulta.</p>
<?php else: ?>
<div class="compact-list">
  <div class="compact-list-header" style="grid-template-columns:2fr 1.5fr 1fr 1fr 70px 80px">
    <div>Varsa</div><div>Hevonen</div><div>Isä</div><div>Emä</div><div>Synt.v.</div><div>Status</div>
  </div>
  <?php foreach ($foals as $fo):
    $sClass = $fo['status'] === 'expected' ? 'sbadge-expected' : 'sbadge-born';
    $gClass = match($fo['gender']) { 'ori' => 'gbadge-ori', 'tamma' => 'gbadge-tamma', default => 'gbadge-ruuna' };
  ?>
  <div class="compact-list-row" style="grid-template-columns:2fr 1.5fr 1fr 1fr 70px 80px">
    <div>
      <div class="cl-name"><?= e($fo['foal_name'] ?? '—') ?></div>
      <?php if ($fo['gender']): ?>
        <span class="gbadge <?= $gClass ?>"><?= e($fo['gender']) ?></span>
      <?php endif; ?>
    </div>
    <div>
      <a href="<?= e(SITE_URL) ?>/admin/foals.php?horse_id=<?= (int)$fo['horse_id'] ?>"
         class="cl-name" style="text-decoration:none"><?= e($fo['horse_name']) ?></a>
    </div>
    <div class="cl-meta"><?= e($fo['sire_name'] ?? '—') ?></div>
    <div class="cl-meta"><?= e($fo['dam_name']  ?? '—') ?></div>
    <div class="cl-mono"><?= $fo['birth_year'] ? (int)$fo['birth_year'] : '—' ?></div>
    <div><span class="sbadge <?= $sClass ?>"><?= e($statusLabels[$fo['status']] ?? $fo['status']) ?></span></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div><!-- /.admin-body -->
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
