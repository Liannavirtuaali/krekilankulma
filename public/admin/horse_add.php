<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();
$allHorses    = $db->query('SELECT id, name FROM horses WHERE is_deleted = 0 ORDER BY name')->fetchAll();
$disciplines  = $db->query('SELECT id, name FROM disciplines ORDER BY name')->fetchAll();
$levels       = $db->query('SELECT id, name FROM levels ORDER BY name')->fetchAll();
$breeds       = $db->query('SELECT id, name FROM breeds ORDER BY name')->fetchAll();
$colors       = $db->query('SELECT id, name FROM colors ORDER BY name')->fetchAll();

$errors = [];
$f = [
    'name' => '', 'call_name' => '', 'vh_id' => '', 'breed_id' => '',
    'birth_date' => '', 'gender' => '', 'color_id' => '', 'height_cm' => '',
    'discipline_id' => '', 'level_id' => '', 'owner_name' => '', 'owner_email' => '',
    'breeder_name' => '', 'breeder_email' => '', 'importer_name' => '', 'importer_email' => '',
    'sire_id' => '', 'dam_id' => '', 'evm' => '', 'profile_url' => '',
    'description' => '', 'pedigree_notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        foreach ($f as $k => $_) {
            $f[$k] = sanitize($_POST[$k] ?? '');
        }
        if ($f['name'] === '') {
            $errors[] = 'Nimi on pakollinen.';
        }
        if ($f['owner_email'] !== '') {
            $r = validate_email($f['owner_email']);
            if (!$r['valid']) $errors[] = 'Omistajan ' . strtolower($r['error']);
            else $f['owner_email'] = $r['value'];
        }
        if ($f['breeder_email'] !== '') {
            $r = validate_email($f['breeder_email']);
            if (!$r['valid']) $errors[] = 'Kasvattajan ' . strtolower($r['error']);
            else $f['breeder_email'] = $r['value'];
        }
        if ($f['importer_email'] !== '') {
            $r = validate_email($f['importer_email']);
            if (!$r['valid']) $errors[] = 'Tuojan ' . strtolower($r['error']);
            else $f['importer_email'] = $r['value'];
        }
        if ($f['profile_url'] !== '' && filter_var($f['profile_url'], FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Profiililinkki ei ole kelvollinen URL.';
        }

        if (empty($errors)) {
            // Generoi slug
            $slug = slugify($f['name']);
            $base = $slug;
            $n = 2;
            while (true) {
                $chk = $db->prepare('SELECT id FROM horses WHERE slug = :slug');
                $chk->execute([':slug' => $slug]);
                if (!$chk->fetch()) break;
                $slug = $base . '-' . $n++;
            }

            $stmt = $db->prepare(
                'INSERT INTO horses
                 (name, call_name, vh_id, breed_id, birth_date, gender, color_id, height_cm,
                  discipline_id, level_id, owner_name, owner_email, breeder_name, breeder_email,
                  importer_name, importer_email, sire_id, dam_id, evm, profile_url,
                  description, pedigree_notes, slug)
                 VALUES
                 (:name, :call_name, :vh_id, :breed_id, :birth_date, :gender, :color_id, :height_cm,
                  :discipline_id, :level_id, :owner_name, :owner_email, :breeder_name, :breeder_email,
                  :importer_name, :importer_email, :sire_id, :dam_id, :evm, :profile_url,
                  :description, :pedigree_notes, :slug)'
            );
            $stmt->execute([
                ':name'           => $f['name'],
                ':call_name'      => $f['call_name'] ?: null,
                ':vh_id'          => $f['vh_id'] ?: null,
                ':breed_id'       => $f['breed_id'] !== '' ? (int)$f['breed_id'] : null,
                ':birth_date'     => $f['birth_date'] ?: null,
                ':gender'         => $f['gender'] ?: null,
                ':color_id'       => $f['color_id'] !== '' ? (int)$f['color_id'] : null,
                ':height_cm'      => $f['height_cm'] !== '' ? (int)$f['height_cm'] : null,
                ':discipline_id'  => $f['discipline_id'] !== '' ? (int)$f['discipline_id'] : null,
                ':level_id'       => $f['level_id'] !== '' ? (int)$f['level_id'] : null,
                ':owner_name'     => $f['owner_name'] ?: null,
                ':owner_email'    => $f['owner_email'] ?: null,
                ':breeder_name'   => $f['breeder_name'] ?: null,
                ':breeder_email'  => $f['breeder_email'] ?: null,
                ':importer_name'  => $f['importer_name'] ?: null,
                ':importer_email' => $f['importer_email'] ?: null,
                ':sire_id'        => $f['sire_id'] !== '' ? (int)$f['sire_id'] : null,
                ':dam_id'         => $f['dam_id'] !== '' ? (int)$f['dam_id'] : null,
                ':evm'            => $f['evm'] !== '' ? (int)$f['evm'] : 0,
                ':profile_url'    => $f['profile_url'] ?: null,
                ':description'    => $f['description'] ?: null,
                ':pedigree_notes' => $f['pedigree_notes'] ?: null,
                ':slug'           => $slug,
            ]);
            redirect(SITE_URL . '/admin/horses.php?added=1');
        }
    }
}

$pageTitle = 'Lisää hevonen';
$breedsJson = json_encode(array_map(fn($b) => ['id' => $b['id'], 'label' => $b['name']], $breeds), JSON_UNESCAPED_UNICODE);
$colorsJson = json_encode(array_map(fn($c) => ['id' => $c['id'], 'label' => $c['name']], $colors), JSON_UNESCAPED_UNICODE);
$disciplinesJson = json_encode(array_map(fn($d) => ['id' => $d['id'], 'label' => $d['name']], $disciplines), JSON_UNESCAPED_UNICODE);
$horsesJson = json_encode(array_map(fn($h) => ['id' => $h['id'], 'label' => $h['name']], $allHorses), JSON_UNESCAPED_UNICODE);
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <a href="<?= e(SITE_URL) ?>/admin/horses.php" class="back-link">← Hevoset</a>
  <h1>Lisää hevonen</h1>
</div>
<div class="admin-body">
<?php if ($errors): ?>
  <div class="flash-err"><ul><?php foreach ($errors as $e_msg): ?><li><?= e($e_msg) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="admin-card">
<form method="post" action="">
  <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">

  <div class="form-row">
    <div class="form-group">
      <label for="name">Nimi *</label>
      <input type="text" id="name" name="name" value="<?= e($f['name']) ?>" required>
    </div>
    <div class="form-group">
      <label for="call_name">Kutsumanimi</label>
      <input type="text" id="call_name" name="call_name" value="<?= e($f['call_name']) ?>">
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="vh_id">VH-tunnus</label>
      <input type="text" id="vh_id" name="vh_id" value="<?= e($f['vh_id']) ?>">
    </div>
    <div class="form-group">
      <label for="breed_id_text">Rotu</label>
      <div class="ac-wrap"
           data-items='<?= htmlspecialchars($breedsJson, ENT_QUOTES) ?>'
           data-input-id="breed_id"
           data-hidden-name="breed_id"
           data-current-id=""
           data-current-label=""
           data-placeholder="Hae rotua..."></div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="birth_date">Syntymäaika</label>
      <input type="date" id="birth_date" name="birth_date" value="<?= e($f['birth_date']) ?>">
    </div>
    <div class="form-group">
      <label for="gender">Sukupuoli</label>
      <select id="gender" name="gender">
        <option value="">— valitse —</option>
        <?php foreach (['ori', 'tamma', 'ruuna', 'tuntematon'] as $g): ?>
          <option value="<?= e($g) ?>" <?= $f['gender'] === $g ? 'selected' : '' ?>><?= ucfirst(e($g)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="color_id_text">Väri</label>
      <div class="ac-wrap"
           data-items='<?= htmlspecialchars($colorsJson, ENT_QUOTES) ?>'
           data-input-id="color_id"
           data-hidden-name="color_id"
           data-current-id=""
           data-current-label=""
           data-placeholder="Hae väriä..."></div>
    </div>
    <div class="form-group">
      <label for="height_cm">Säkäkorkeus (cm)</label>
      <input type="number" id="height_cm" name="height_cm" min="100" max="200" value="<?= e($f['height_cm']) ?>">
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="discipline_id_text">Laji</label>
      <div class="ac-wrap"
           data-items='<?= htmlspecialchars($disciplinesJson, ENT_QUOTES) ?>'
           data-input-id="discipline_id"
           data-hidden-name="discipline_id"
           data-current-id=""
           data-current-label=""
           data-placeholder="Hae lajia..."></div>
    </div>
    <div class="form-group">
      <label for="level_id">Taso</label>
      <select id="level_id" name="level_id">
        <option value="">— ei valittu —</option>
        <?php foreach ($levels as $lv): ?>
          <option value="<?= (int)$lv['id'] ?>" <?= (int)$f['level_id'] === (int)$lv['id'] ? 'selected' : '' ?>><?= e($lv['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <h3 style="margin-top:1.5rem">Sukutaulu</h3>
  <div class="form-row">
    <div class="form-group">
      <label for="sire_id_text">Isä (ori)</label>
      <div class="ac-wrap"
           data-items='<?= htmlspecialchars($horsesJson, ENT_QUOTES) ?>'
           data-input-id="sire_id"
           data-hidden-name="sire_id"
           data-current-id=""
           data-current-label=""
           data-placeholder="Hae ori..."></div>
    </div>
    <div class="form-group">
      <label for="dam_id_text">Emä (tamma)</label>
      <div class="ac-wrap"
           data-items='<?= htmlspecialchars($horsesJson, ENT_QUOTES) ?>'
           data-input-id="dam_id"
           data-hidden-name="dam_id"
           data-current-id=""
           data-current-label=""
           data-placeholder="Hae tamma..."></div>
    </div>
  </div>

  <h3 style="margin-top:1.5rem">Omistaja &amp; kasvattaja</h3>
  <div class="form-row">
    <div class="form-group">
      <label for="owner_name">Omistajan nimi</label>
      <input type="text" id="owner_name" name="owner_name" value="<?= e($f['owner_name']) ?>">
    </div>
    <div class="form-group">
      <label for="owner_email">Omistajan sähköposti</label>
      <input type="email" id="owner_email" name="owner_email" value="<?= e($f['owner_email']) ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="breeder_name">Kasvattajan nimi</label>
      <input type="text" id="breeder_name" name="breeder_name" value="<?= e($f['breeder_name']) ?>">
    </div>
    <div class="form-group">
      <label for="breeder_email">Kasvattajan sähköposti</label>
      <input type="email" id="breeder_email" name="breeder_email" value="<?= e($f['breeder_email']) ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="importer_name">Tuojan nimi</label>
      <input type="text" id="importer_name" name="importer_name" value="<?= e($f['importer_name']) ?>">
    </div>
    <div class="form-group">
      <label for="importer_email">Tuojan sähköposti</label>
      <input type="email" id="importer_email" name="importer_email" value="<?= e($f['importer_email']) ?>">
    </div>
  </div>

  <h3 style="margin-top:1.5rem">Lisätiedot</h3>
  <div class="form-row">
    <div class="form-group">
      <label for="evm">EVM (1=evm, 0=ei)</label>
      <select id="evm" name="evm">
        <option value="0" <?= $f['evm'] !== '1' ? 'selected' : '' ?>>Ei</option>
        <option value="1" <?= $f['evm'] === '1' ? 'selected' : '' ?>>Kyllä</option>
      </select>
    </div>
    <div class="form-group">
      <label for="profile_url">Profiililinkki (ulkoinen)</label>
      <input type="url" id="profile_url" name="profile_url" value="<?= e($f['profile_url']) ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="description">Kuvaus</label>
    <textarea id="description" name="description"><?= e($f['description']) ?></textarea>
  </div>
  <div class="form-group">
    <label for="pedigree_notes">Sukutaulun lisätiedot</label>
    <textarea id="pedigree_notes" name="pedigree_notes"><?= e($f['pedigree_notes']) ?></textarea>
  </div>

  <div style="display:flex;gap:0.75rem;align-items:center;margin-top:1rem">
    <button type="submit" class="btn">Tallenna hevonen</button>
    <a href="<?= e(SITE_URL) ?>/admin/horses.php" class="btn-ghost">Peruuta</a>
  </div>
</form>
</div><!-- /.admin-card -->
</div><!-- /.admin-body -->
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
