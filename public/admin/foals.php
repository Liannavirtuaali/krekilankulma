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

$edit_id = (int)($_GET['edit'] ?? 0);
$errors  = [];
$flash   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $foal_id = (int)($_POST['foal_id'] ?? 0);

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
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
<div class="admin-page-header">
  <a href="<?= e(SITE_URL) ?>/admin/horses.php" class="back-link">← Hevoset</a>
  <h1>Kasvatus</h1>
  <div class="page-actions">
    <button class="btn" onclick="adminOpenSlide('foal')">+ Lisää varsa</button>
  </div>
</div>

<div class="horse-ctx-banner">
  <span class="hcb-name">🌱 <?= e($horse['name']) ?></span>
  <span class="hcb-meta"><?= count($foals) ?> varsamerkintää</span>
  <a href="<?= e(SITE_URL) ?>/admin/horses.php" class="hcb-back">← Hevoslistaan</a>
</div>

<div class="admin-body">
<?php if ($errors): ?>
  <div class="flash-err"><ul><?php foreach ($errors as $emsg): ?><li><?= e($emsg) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<?= $flash ?>

<?php if ($foals): ?>
<div class="compact-list">
  <div class="compact-list-header" style="grid-template-columns:2fr 1fr 1fr 70px 80px 28px">
    <div>Nimi</div><div>Isä</div><div>Emä</div><div>Synt.v.</div><div>Status</div><div></div>
  </div>
  <?php foreach ($foals as $fo):
    $sClass = $fo['status'] === 'expected' ? 'sbadge-expected' : 'sbadge-born';
  ?>
  <div class="compact-list-row" style="grid-template-columns:2fr 1fr 1fr 70px 80px 28px"
       onclick="adminToggleExpand('f<?= (int)$fo['id'] ?>')">
    <div class="cl-name"><?= e($fo['foal_name']) ?></div>
    <div class="cl-meta"><?= e($fo['sire_name'] ?? '—') ?></div>
    <div class="cl-meta"><?= e($fo['dam_name']  ?? '—') ?></div>
    <div class="cl-mono"><?= $fo['birth_year'] ? (int)$fo['birth_year'] : '—' ?></div>
    <div><span class="sbadge <?= $sClass ?>"><?= e($statusLabels[$fo['status']] ?? $fo['status']) ?></span></div>
    <div>
      <button class="cl-expand-btn" id="cl-btn-f<?= (int)$fo['id'] ?>"
              onclick="event.stopPropagation();adminToggleExpand('f<?= (int)$fo['id'] ?>')">▸</button>
    </div>
  </div>
  <div class="cl-expanded" id="cl-exp-f<?= (int)$fo['id'] ?>">
    <div class="cl-expanded-actions">
      <button class="btn-sm btn-edit" onclick="openEditFoal(<?= (int)$fo['id'] ?>, <?= htmlspecialchars(json_encode($fo), ENT_QUOTES) ?>)">✏️ Muokkaa</button>
      <form method="post" action="?horse_id=<?= $horse_id ?>" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="action"  value="delete">
        <input type="hidden" name="foal_id" value="<?= (int)$fo['id'] ?>">
        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Poistetaanko varsamerkintä?')">🗑 Poista</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
  <p style="color:var(--color-text-muted);margin:1rem 0">Ei varsamerkintöjä. Lisää ensimmäinen varsa painamalla "+ Lisää varsa".</p>
<?php endif; ?>
</div><!-- /.admin-body -->

<!-- ── SLIDE PANEL: Lisää/muokkaa varsa ── -->
<div class="admin-slide-overlay" id="slide-overlay-foal" onclick="adminCloseSlide('foal')"></div>
<div class="admin-slide-panel" id="slide-panel-foal">
  <div class="admin-slide-header">
    <h2 id="slide-foal-title">Lisää varsamerkintä</h2>
    <button class="admin-slide-close" onclick="adminCloseSlide('foal')">×</button>
  </div>
  <form method="post" action="?horse_id=<?= $horse_id ?>">
    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
    <input type="hidden" name="action"  id="slide-action"  value="add">
    <input type="hidden" name="foal_id" id="slide-foal-id" value="">
    <div class="admin-slide-body">
      <div class="form-row">
        <div class="form-group">
          <label for="foal_name">Varsan nimi *</label>
          <input type="text" id="foal_name" name="foal_name" required>
        </div>
        <div class="form-group">
          <label for="birth_year">Syntymävuosi</label>
          <input type="number" id="birth_year" name="birth_year" min="1900" max="2100">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="sire_id">Isä (ori)</label>
          <select id="sire_id" name="sire_id">
            <option value="">— ei valittu —</option>
            <?php foreach ($allHorses as $ah): ?>
              <option value="<?= (int)$ah['id'] ?>"><?= e($ah['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="dam_id">Emä (tamma)</label>
          <select id="dam_id" name="dam_id">
            <option value="">— ei valittu —</option>
            <?php foreach ($allHorses as $ah): ?>
              <option value="<?= (int)$ah['id'] ?>"><?= e($ah['name']) ?></option>
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
              <option value="<?= e($g) ?>"><?= ucfirst(e($g)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statusLabels as $val => $label): ?>
              <option value="<?= e($val) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div><!-- /.admin-slide-body -->
    <div class="admin-slide-footer">
      <button type="submit" class="btn" id="slide-submit-btn">Lisää varsamerkintä</button>
      <button type="button" class="btn-ghost" onclick="adminCloseSlide('foal')">Peruuta</button>
    </div>
  </form>
</div>

<script>
function openEditFoal(id, data) {
  document.getElementById('slide-foal-title').textContent = 'Muokkaa varsamerkintää';
  document.getElementById('slide-action').value = 'edit';
  document.getElementById('slide-foal-id').value = id;
  document.getElementById('foal_name').value   = data.foal_name  || '';
  document.getElementById('birth_year').value  = data.birth_year || '';
  document.getElementById('sire_id').value     = data.sire_id    || '';
  document.getElementById('dam_id').value      = data.dam_id     || '';
  document.getElementById('gender').value      = data.gender     || '';
  document.getElementById('status').value      = data.status     || 'born';
  document.getElementById('slide-submit-btn').textContent = 'Tallenna muutokset';
  adminOpenSlide('foal');
}
</script>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
