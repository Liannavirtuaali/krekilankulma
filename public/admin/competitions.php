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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$edit_id = (int)($_GET['edit'] ?? 0);
$errors  = [];
$flash   = '';

// POST-käsittely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $comp_id = (int)($_POST['comp_id'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        if ($action === 'add') {
            $comp_name = sanitize($_POST['competition_name'] ?? '');
            if ($comp_name === '') {
                $errors[] = 'Kilpailun nimi on pakollinen.';
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO competitions (horse_id, competition_name, competition_date, placement, points, notes)
                     VALUES (:horse_id, :competition_name, :competition_date, :placement, :points, :notes)'
                );
                $stmt->execute([
                    ':horse_id'        => $horse_id,
                    ':competition_name'=> $comp_name,
                    ':competition_date'=> sanitize($_POST['competition_date'] ?? '') ?: null,
                    ':placement'       => sanitize($_POST['placement'] ?? '') ?: null,
                    ':points'          => $_POST['points'] !== '' ? (int)$_POST['points'] : null,
                    ':notes'           => sanitize($_POST['notes'] ?? '') ?: null,
                ]);
                redirect(SITE_URL . '/admin/competitions.php?horse_id=' . $horse_id . '&added=1');
            }
        } elseif ($action === 'edit' && $comp_id > 0) {
            // Omistajuustarkistus
            $own = $db->prepare('SELECT id FROM competitions WHERE id = :comp_id AND horse_id = :horse_id');
            $own->execute([':comp_id' => $comp_id, ':horse_id' => $horse_id]);
            if ($own->fetch()) {
                $comp_name = sanitize($_POST['competition_name'] ?? '');
                if ($comp_name === '') {
                    $errors[] = 'Kilpailun nimi on pakollinen.';
                } else {
                    $stmt = $db->prepare(
                        'UPDATE competitions SET competition_name=:competition_name, competition_date=:competition_date,
                         placement=:placement, points=:points, notes=:notes WHERE id=:comp_id'
                    );
                    $stmt->execute([
                        ':competition_name'=> $comp_name,
                        ':competition_date'=> sanitize($_POST['competition_date'] ?? '') ?: null,
                        ':placement'       => sanitize($_POST['placement'] ?? '') ?: null,
                        ':points'          => $_POST['points'] !== '' ? (int)$_POST['points'] : null,
                        ':notes'           => sanitize($_POST['notes'] ?? '') ?: null,
                        ':comp_id'         => $comp_id,
                    ]);
                    redirect(SITE_URL . '/admin/competitions.php?horse_id=' . $horse_id . '&updated=1');
                }
            }
        } elseif ($action === 'delete' && $comp_id > 0) {
            $own = $db->prepare('SELECT id FROM competitions WHERE id = :comp_id AND horse_id = :horse_id');
            $own->execute([':comp_id' => $comp_id, ':horse_id' => $horse_id]);
            if ($own->fetch()) {
                $db->prepare('DELETE FROM competitions WHERE id = :comp_id')->execute([':comp_id' => $comp_id]);
            }
            redirect(SITE_URL . '/admin/competitions.php?horse_id=' . $horse_id . '&deleted=1');
        }
    }
}

// Hae kilpailut
$compsStmt = $db->prepare('SELECT * FROM competitions WHERE horse_id = :horse_id ORDER BY competition_date DESC');
$compsStmt->execute([':horse_id' => $horse_id]);
$competitions = $compsStmt->fetchAll();

// Muokkaustila
$editComp = null;
if ($edit_id > 0) {
    $editStmt = $db->prepare('SELECT * FROM competitions WHERE id = :id AND horse_id = :horse_id');
    $editStmt->execute([':id' => $edit_id, ':horse_id' => $horse_id]);
    $editComp = $editStmt->fetch();
}

if (isset($_GET['added']))   $flash = '<p class="flash-ok">Kilpailu lisätty.</p>';
if (isset($_GET['updated'])) $flash = '<p class="flash-ok">Kilpailu päivitetty.</p>';
if (isset($_GET['deleted'])) $flash = '<p class="flash-ok">Kilpailu poistettu.</p>';

$pageTitle = 'Kilpailut';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Kilpailut — <?= e($horse['name']) ?></h1>
<p><a href="<?= e(SITE_URL) ?>/admin/horses.php">← Takaisin hevoslistaan</a></p>

<?php if ($errors): ?>
  <ul class="flash-err"><?php foreach ($errors as $emsg): ?><li><?= e($emsg) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<?= $flash ?>

<?php if ($competitions): ?>
<table class="admin-table">
  <thead>
    <tr><th>Kilpailu</th><th>Päivämäärä</th><th>Sijoitus</th><th>Pisteet</th><th>Muistiinpanot</th><th>Toiminnot</th></tr>
  </thead>
  <tbody>
  <?php foreach ($competitions as $c): ?>
    <tr>
      <td><?= e($c['competition_name']) ?></td>
      <td><?= $c['competition_date'] ? formatDate($c['competition_date']) : '' ?></td>
      <td><?= e($c['placement'] ?? '') ?></td>
      <td><?= $c['points'] !== null ? (int)$c['points'] : '' ?></td>
      <td><?= e($c['notes'] ?? '') ?></td>
      <td style="white-space:nowrap">
        <a href="?horse_id=<?= $horse_id ?>&edit=<?= (int)$c['id'] ?>" class="btn-sm btn-edit">Muokkaa</a>
        <form method="post" action="" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action"   value="delete">
          <input type="hidden" name="comp_id"  value="<?= (int)$c['id'] ?>">
          <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Poistetaanko kilpailu?')">Poista</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <p>Ei kilpailumerkintöjä.</p>
<?php endif; ?>

<h3><?= $editComp ? 'Muokkaa kilpailua' : 'Lisää kilpailu' ?></h3>
<form method="post" action="?horse_id=<?= $horse_id ?>">
  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
  <input type="hidden" name="action"  value="<?= $editComp ? 'edit' : 'add' ?>">
  <?php if ($editComp): ?><input type="hidden" name="comp_id" value="<?= (int)$editComp['id'] ?>"><?php endif; ?>

  <div class="form-row">
    <div class="form-group">
      <label for="competition_name">Kilpailun nimi *</label>
      <input type="text" id="competition_name" name="competition_name" value="<?= e($editComp['competition_name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label for="competition_date">Päivämäärä</label>
      <input type="date" id="competition_date" name="competition_date" value="<?= e($editComp['competition_date'] ?? '') ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="placement">Sijoitus</label>
      <input type="text" id="placement" name="placement" value="<?= e($editComp['placement'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="points">Pisteet</label>
      <input type="number" id="points" name="points" value="<?= $editComp['points'] !== null ? (int)$editComp['points'] : '' ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="notes">Muistiinpanot</label>
    <textarea id="notes" name="notes"><?= e($editComp['notes'] ?? '') ?></textarea>
  </div>
  <p>
    <button type="submit" class="btn"><?= $editComp ? 'Tallenna muutokset' : 'Lisää kilpailu' ?></button>
    <?php if ($editComp): ?><a href="?horse_id=<?= $horse_id ?>" style="margin-left:1rem">Peruuta</a><?php endif; ?>
  </p>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
