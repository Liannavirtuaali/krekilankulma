<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();
$horses = $db->query(
    'SELECT h.id, h.name, h.slug, h.breed, h.gender, h.birth_date, h.vh_id
     FROM horses h
     WHERE h.is_deleted = 0
     ORDER BY h.name ASC'
)->fetchAll();

$flash = '';
if (isset($_GET['added']))   $flash = '<p class="flash-ok">Hevonen lisätty.</p>';
if (isset($_GET['updated'])) $flash = '<p class="flash-ok">Muutokset tallennettu.</p>';
if (isset($_GET['deleted'])) $flash = '<p class="flash-ok">Hevonen poistettu.</p>';

$pageTitle = 'Hevoset';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Hevoset</h1>
<p><a href="<?= e(SITE_URL) ?>/admin/horse_add.php" class="btn">+ Lisää hevonen</a></p>
<?= $flash ?>
<?php if (empty($horses)): ?>
  <p>Ei hevosia. <a href="<?= e(SITE_URL) ?>/admin/horse_add.php">Lisää ensimmäinen hevonen.</a></p>
<?php else: ?>
<table class="admin-table">
  <thead>
    <tr>
      <th>Nimi</th>
      <th>Rotu</th>
      <th>Sukupuoli</th>
      <th>Syntymäaika</th>
      <th>VH-tunnus</th>
      <th>Toiminnot</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($horses as $horse): ?>
    <tr>
      <td><?= e($horse['name']) ?></td>
      <td><?= e($horse['breed']) ?></td>
      <td><?= e($horse['gender']) ?></td>
      <td><?= $horse['birth_date'] ? formatDate($horse['birth_date']) : '' ?></td>
      <td><?= e($horse['vh_id']) ?></td>
      <td style="white-space:nowrap">
        <a href="<?= e(SITE_URL) ?>/admin/horse_edit.php?id=<?= (int)$horse['id'] ?>" class="btn-sm btn-edit">Muokkaa</a>
        <a href="<?= e(horseUrl($horse)) ?>" class="btn-sm btn-view" target="_blank">Näytä</a>
        <a href="<?= e(SITE_URL) ?>/admin/photos.php?horse_id=<?= (int)$horse['id'] ?>" class="btn-sm btn-photos">Kuvat</a>
        <form method="post" action="<?= e(SITE_URL) ?>/admin/horse_delete.php" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="id" value="<?= (int)$horse['id'] ?>">
          <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Poistetaanko hevonen <?= e(addslashes($horse['name'])) ?>?')">Poista</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
