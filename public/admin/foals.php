<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$horse_id = (int)($_GET['horse_id'] ?? 0);
if ($horse_id <= 0) {
    redirect(SITE_URL . '/admin/horses.php');
}

$db = getDB();
$horseStmt = $db->prepare('SELECT id, name FROM horses WHERE id = :id AND is_deleted = 0');
$horseStmt->execute([':id' => $horse_id]);
$horse = $horseStmt->fetch();
if (!$horse) {
    redirect(SITE_URL . '/admin/horses.php');
}

$allHorses = $db->query('SELECT id, name FROM horses WHERE is_deleted = 0 ORDER BY name')->fetchAll();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$edit_id = (int)($_GET['edit'] ?? 0);
$errors  = [];
$flash   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $foal_id = (int)($_POST['foal_id'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        if ($action === 'add') {
            $foal_name = sanitize($_POST['foal_name'] ?? '');
            if ($foal_name === '') {
                $errors[] = 'Varsan nimi on pakollinen.';
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO foals (horse_id, foal_name, sire_id, dam_id, birth_year, gender, status)
                     VALUES (:horse_id, :foal_name, :sire_id, :dam_id, :birth_year, :gender, :status)'
                );
                $stmt->execute([
                    ':horse_id'  => $horse_id,
                    ':foal_name' => $foal_name,
                    ':sire_id'   => $_POST['sire_id'] !== '' ? (int)$_POST['sire_id'] : null,
                    ':dam_id'    => $_POST['dam_id']  !== '' ? (int)$_POST['dam_id']  : null,
                    ':birth_year'=> $_POST['birth_year'] !== '' ? (int)$_POST['birth_year'] : null,
                    ':gender'    => sanitize($_POST['gender'] ?? '') ?: null,
                    ':status'    => sanitize($_POST['status'] ?? 'born'),
                ]);
                redirect(SITE_URL . '/admin/foals.php?horse_id=' . $horse_id . '&added=1');
            }
        } elseif ($action === 'edit' && $foal_id > 0) {
            $own = $db->prepare('SELECT id FROM foals WHERE id = :foal_id AND horse_id = :horse_id');
            $own->execute([':foal_id' => $foal_id, ':horse_id' => $horse_id]);
            if ($own->fetch()) {
                $foal_name = sanitize($_POST['foal_name'] ?? '');
                if ($foal_name === '') {
                    $errors[] = 'Varsan nimi on pakollinen.';
                } else {
                    $stmt = $db->prepare(
                        'UPDATE foals SET foal_name=:foal_name, sire_id=:sire_id, dam_id=:dam_id,
                         birth_year=:birth_year, gender=:gender, status=:status
                         WHERE id=:foal_id'
                    );
                    $stmt->execute([
                        ':foal_name'  => $foal_name,
                        ':sire_id'    => $_POST['sire_id'] !== '' ? (int)$_POST['sire_id'] : null,
                        ':dam_id'     => $_POST['dam_id']  !== '' ? (int)$_POST['dam_id']  : null,
                        ':birth_year' => $_POST['birth_year'] !== '' ? (int)$_POST['birth_year'] : null,
                        ':gender'     => sanitize($_POST['gender'] ?? '') ?: null,
                        ':status'     => sanitize($_POST['status'] ?? 'born'),
                        ':foal_id'    => $foal_id,
                    ]);
                    redirect(SITE_URL . '/admin/foals.php?horse_id=' . $horse_id . '&updated=1');
                }
            }
        } elseif ($action === 'delete' && $foal_id > 0) {
            $own = $db->prepare('SELECT id FROM foals WHERE id = :foal_id AND horse_id = :horse_id');
            $own->execute([':foal_id' => $foal_id, ':horse_id' => $horse_id]);
            if ($own->fetch()) {
                $db->prepare('DELETE FROM foals WHERE id = :foal_id')->execute([':foal_id' => $foal_id]);
            }
            redirect(SITE_URL . '/admin/foals.php?horse_id=' . $horse_id . '&deleted=1');
        }
    }
}

// Hae varsamerkinnät — JOIN isä + emä nimineen
$foalsStmt = $db->prepare(
    'SELECT f.*, s.name AS sire_name, d.name AS dam_name
     FROM foals f
     LEFT JOIN horses s ON s.id = f.sire_id AND s.is_deleted = 0
     LEFT JOIN horses d ON d.id = f.dam_id  AND d.is_deleted = 0
     WHERE f.horse_id = :horse_id
     ORDER BY f.birth_year DESC, f.foal_name ASC'
);
$foalsStmt->execute([':horse_id' => $horse_id]);
$foals = $foalsStmt->fetchAll();

$editFoal = null;
if ($edit_id > 0) {
    $editStmt = $db->prepare('SELECT * FROM foals WHERE id = :id AND horse_id = :horse_id');
    $editStmt->execute([':id' => $edit_id, ':horse_id' => $horse_id]);
    $editFoal = $editStmt->fetch();
}

if (isset($_GET['added']))   $flash = '<p class="flash-ok">Varsamerkintä lisätty.</p>';
if (isset($_GET['updated'])) $flash = '<p class="flash-ok">Varsamerkintä päivitetty.</p>';
if (isset($_GET['deleted'])) $flash = '<p class="flash-ok">Varsamerkintä poistettu.</p>';

$statusLabels = ['expected' => 'Odotettu', 'born' => 'Syntynyt'];

$pageTitle = 'Kasvatus';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Kasvatus — <?= e($horse['name']) ?></h1>
<p><a href="<?= e(SITE_URL) ?>/admin/horses.php">← Takaisin hevoslistaan</a></p>

<?php if ($errors): ?>
  <ul class="flash-err"><?php foreach ($errors as $emsg): ?><li><?= e($emsg) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<?= $flash ?>

<?php if ($foals): ?>
<table class="admin-table">
  <thead>
    <tr><th>Nimi</th><th>Isä</th><th>Emä</th><th>Synt.v.</th><th>Sukupuoli</th><th>Status</th><th>Toiminnot</th></tr>
  </thead>
  <tbody>
  <?php foreach ($foals as $fo): ?>
    <tr>
      <td><?= e($fo['foal_name']) ?></td>
      <td><?= e($fo['sire_name'] ?? '—') ?></td>
      <td><?= e($fo['dam_name']  ?? '—') ?></td>
      <td><?= $fo['birth_year'] ? (int)$fo['birth_year'] : '' ?></td>
      <td><?= e($fo['gender'] ?? '') ?></td>
      <td><?= e($statusLabels[$fo['status']] ?? $fo['status']) ?></td>
      <td style="white-space:nowrap">
        <a href="?horse_id=<?= $horse_id ?>&edit=<?= (int)$fo['id'] ?>" class="btn-sm btn-edit">Muokkaa</a>
        <form method="post" action="" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action"  value="delete">
          <input type="hidden" name="foal_id" value="<?= (int)$fo['id'] ?>">
          <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Poistetaanko varsamerkintä?')">Poista</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <p>Ei varsamerkintöjä.</p>
<?php endif; ?>

<h3><?= $editFoal ? 'Muokkaa varsamerkintää' : 'Lisää varsamerkintä' ?></h3>
<form method="post" action="?horse_id=<?= $horse_id ?>">
  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
  <input type="hidden" name="action"  value="<?= $editFoal ? 'edit' : 'add' ?>">
  <?php if ($editFoal): ?><input type="hidden" name="foal_id" value="<?= (int)$editFoal['id'] ?>"><?php endif; ?>

  <div class="form-row">
    <div class="form-group">
      <label for="foal_name">Varsan nimi *</label>
      <input type="text" id="foal_name" name="foal_name" value="<?= e($editFoal['foal_name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label for="birth_year">Syntymävuosi</label>
      <input type="number" id="birth_year" name="birth_year" min="1900" max="2100" value="<?= $editFoal['birth_year'] ?? '' ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="sire_id">Isä (ori)</label>
      <select id="sire_id" name="sire_id">
        <option value="">— ei valittu —</option>
        <?php foreach ($allHorses as $ah): ?>
          <option value="<?= (int)$ah['id'] ?>" <?= (int)($editFoal['sire_id'] ?? 0) === (int)$ah['id'] ? 'selected' : '' ?>><?= e($ah['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="dam_id">Emä (tamma)</label>
      <select id="dam_id" name="dam_id">
        <option value="">— ei valittu —</option>
        <?php foreach ($allHorses as $ah): ?>
          <option value="<?= (int)$ah['id'] ?>" <?= (int)($editFoal['dam_id'] ?? 0) === (int)$ah['id'] ? 'selected' : '' ?>><?= e($ah['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="gender">Sukupuoli</label>
      <select id="gender" name="gender">
        <option value="">— valitse —</option>
        <?php foreach (['ori', 'tamma', 'ruuna', 'tuntematon'] as $g): ?>
          <option value="<?= e($g) ?>" <?= ($editFoal['gender'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst(e($g)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach ($statusLabels as $val => $label): ?>
          <option value="<?= e($val) ?>" <?= ($editFoal['status'] ?? 'born') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <p>
    <button type="submit" class="btn"><?= $editFoal ? 'Tallenna muutokset' : 'Lisää varsamerkintä' ?></button>
    <?php if ($editFoal): ?><a href="?horse_id=<?= $horse_id ?>" style="margin-left:1rem">Peruuta</a><?php endif; ?>
  </p>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
