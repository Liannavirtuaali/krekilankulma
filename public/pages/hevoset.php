<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Hevoset';

$db = getDB();
$stmt = $db->prepare(
    'SELECT h.id, h.name, h.slug, h.breed, h.gender, h.birth_date,
            hp.filename
     FROM horses h
     LEFT JOIN horse_photos hp
            ON hp.horse_id = h.id
           AND hp.sort_order = (SELECT MIN(sort_order) FROM horse_photos WHERE horse_id = h.id)
     WHERE h.is_deleted = 0 AND h.evm = 0
     ORDER BY h.name ASC'
);
$stmt->execute();
$horses = $stmt->fetchAll();

require __DIR__ . '/../src/includes/header.php';

$genderFi = ['ori' => 'Ori', 'tamma' => 'Tamma', 'ruuna' => 'Ruuna', 'käkky' => 'Käkky'];
?>
<main>
  <h1>Tallin hevoset</h1>

  <?php if (empty($horses)): ?>
    <p>Tallissa ei ole vielä hevosia.</p>
  <?php else: ?>
    <div class="horse-cards">
      <?php foreach ($horses as $horse): ?>
        <div class="horse-card">
          <?php if ($horse['filename']): ?>
            <img src="<?= e(UPLOADS_URL . $horse['filename']) ?>" alt="<?= e($horse['name']) ?>">
          <?php else: ?>
            <div class="horse-card-placeholder">🐴</div>
          <?php endif; ?>
          <div class="horse-card-info">
            <h3><a href="<?= e(horseUrl($horse)) ?>"><?= e($horse['name']) ?></a></h3>
            <?php if ($horse['breed']): ?><p><?= e($horse['breed']) ?></p><?php endif; ?>
            <p><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></p>
            <?php if ($horse['birth_date']): ?>
              <p><?= e((string)calculateAge($horse['birth_date'])) ?> v.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
