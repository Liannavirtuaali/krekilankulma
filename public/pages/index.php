<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Etusivu';

// Hae 3 viimeisintä tallin hevosta ensimmäisellä kuvalla
$db = getDB();
$stmt = $db->prepare(
    'SELECT h.id, h.name, h.slug, h.breed, h.gender,
            hp.filename
     FROM horses h
     LEFT JOIN horse_photos hp
            ON hp.horse_id = h.id
           AND hp.sort_order = (SELECT MIN(sort_order) FROM horse_photos WHERE horse_id = h.id)
     WHERE h.is_deleted = 0 AND h.evm = 0
     ORDER BY h.id DESC
     LIMIT 3'
);
$stmt->execute();
$latestHorses = $stmt->fetchAll();

require __DIR__ . '/../src/includes/header.php';

$genderFi = ['ori' => 'Ori', 'tamma' => 'Tamma', 'ruuna' => 'Ruuna', 'käkky' => 'Käkky'];
?>
<main>
  <section class="hero">
    <h1>Tervetuloa <?= e(SITE_NAME) ?>on</h1>
    <p>Täällä asuvat rakkaimmat hevosemme. Tutustu talliimme ja sen asukkaisiin!</p>
  </section>

  <section class="horses-preview">
    <h2>Tallin hevoset</h2>
    <?php if (empty($latestHorses)): ?>
      <p>Tallissa ei ole vielä hevosia.</p>
    <?php else: ?>
      <div class="horse-cards">
        <?php foreach ($latestHorses as $horse): ?>
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
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p style="margin-top:1.5rem;"><a href="<?= e(SITE_URL . '/pages/hevoset.php') ?>" class="btn">Katso kaikki hevoset &rarr;</a></p>
  </section>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
