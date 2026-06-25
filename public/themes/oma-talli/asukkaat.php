<?php
require_once __DIR__ . '/../../src/includes/db.php';
header('Content-Type: text/html; charset=UTF-8');

$db = getDB();
$stmt = $db->prepare(
    'SELECT h.id, h.name, h.slug, h.call_name, h.gender,
            b.name AS breed_name,
            hp.filename
     FROM horses h
     LEFT JOIN breeds b ON b.id = h.breed_id
     LEFT JOIN horse_photos hp
            ON hp.horse_id = h.id
           AND hp.sort_order = (SELECT MIN(sort_order) FROM horse_photos WHERE horse_id = h.id)
     WHERE h.is_deleted = 0 AND h.evm = 0 AND h.ancestor = 0
     ORDER BY h.name ASC'
);
$stmt->execute();
$horses = $stmt->fetchAll();
?>
<?php include_once("header.php"); ?>



<h2>Tallin hevoset</h2>

	<p><table width="100%"><tr><td>

<?php if (empty($horses)): ?>

	<p class="infotxt">Tallissa ei ole vielä hevosia.</p>

<?php else: ?>
<?php foreach ($horses as $horse):
    $slug = $horse['slug'] ?: slugify($horse['name']);
?>

	<table id="hevoslista"><tr><td>

<?php if ($horse['filename']): ?>
<img src="<?= e(UPLOADS_URL . $horse['filename']) ?>">
<?php endif; ?>

<a href="<?= e(horseUrl($horse)) ?>"><?= e($horse['name']) ?></a><br>
<?php if ($horse['call_name']): ?>kutsumanimeltään <?= e($horse['call_name']) ?> <br><?php endif; ?>
<?= e($horse['breed_name'] ?? '') ?> <?= e($horse['gender']) ?> <br>

	</td></tr></table>

<?php endforeach; ?>
<?php endif; ?>

	</td></tr></table></p>




<?php include_once("footer.php"); ?>
