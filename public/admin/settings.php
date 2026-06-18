<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();

// Load all settings into an associative array
$rows = $db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
$s = [];
foreach ($rows as $row) {
    $s[$row['setting_key']] = $row['setting_value'] ?? '';
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        $fields = [
            'owner_nickname'  => ['label' => 'Nimimerkki',  'max' => 100, 'required' => true],
            'owner_vrl_id'    => ['label' => 'VRL-tunnus',  'max' => 50,  'required' => true],
            'owner_email'     => ['label' => 'Sähköposti',  'max' => 255, 'required' => false, 'email' => true],
            'owner_forum_url' => ['label' => 'Foorumilinkki', 'max' => 500, 'required' => false, 'url' => true],
            'stable_name'     => ['label' => 'Tallin nimi', 'max' => 150, 'required' => false],
        ];

        $values = [];
        foreach ($fields as $key => $cfg) {
            $val = sanitize($_POST[$key] ?? '');

            if ($cfg['required'] && $val === '') {
                $errors[] = $cfg['label'] . ' on pakollinen.';
                continue;
            }
            if ($val !== '' && mb_strlen($val) > $cfg['max']) {
                $errors[] = $cfg['label'] . ' on liian pitkä (max ' . $cfg['max'] . ' merkkiä).';
                continue;
            }
            if (!empty($cfg['email']) && $val !== '') {
                $r = validate_email($val);
                if (!$r['valid']) { $errors[] = $cfg['label'] . ': ' . $r['error']; continue; }
                $val = $r['value'];
            }
            if (!empty($cfg['url']) && $val !== '') {
                if (filter_var($val, FILTER_VALIDATE_URL) === false) {
                    $errors[] = $cfg['label'] . ' ei ole kelvollinen URL.';
                    continue;
                }
            }
            $values[$key] = $val;
        }

        if (empty($errors)) {
            $stmt = $db->prepare(
                'INSERT INTO settings (setting_key, setting_value)
                 VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($values as $key => $val) {
                $stmt->execute([':k' => $key, ':v' => $val]);
            }
            // Reload
            $rows = $db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $s[$row['setting_key']] = $row['setting_value'] ?? '';
            }
            $success = true;
        }
    }
}

$pageTitle = 'Ylläpitäjän tiedot';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <h1>Ylläpitäjän tiedot</h1>
</div>
<div class="admin-body">

<?php if ($success): ?>
  <div class="flash-ok">Tiedot tallennettu.</div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="flash-err"><ul><?php foreach ($errors as $e_msg): ?><li><?= e($e_msg) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <h2>Tallin ylläpitäjä</h2>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">

    <div class="form-row">
      <div class="form-group">
        <label for="owner_nickname">Nimimerkki *</label>
        <input type="text" id="owner_nickname" name="owner_nickname"
               value="<?= e($s['owner_nickname'] ?? '') ?>"
               maxlength="100" required>
      </div>
      <div class="form-group">
        <label for="owner_vrl_id">VRL-tunnus *</label>
        <input type="text" id="owner_vrl_id" name="owner_vrl_id"
               value="<?= e($s['owner_vrl_id'] ?? '') ?>"
               maxlength="50" placeholder="Esim. VRL-12345" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="stable_name">Tallin nimi</label>
        <input type="text" id="stable_name" name="stable_name"
               value="<?= e($s['stable_name'] ?? '') ?>"
               maxlength="150">
      </div>
      <div class="form-group">
        <label for="owner_email">Sähköposti</label>
        <input type="email" id="owner_email" name="owner_email"
               value="<?= e($s['owner_email'] ?? '') ?>"
               maxlength="255">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="owner_forum_url">Foorumilinkki</label>
        <input type="url" id="owner_forum_url" name="owner_forum_url"
               value="<?= e($s['owner_forum_url'] ?? '') ?>"
               maxlength="500" placeholder="https://...">
      </div>
      <div class="form-group"></div>
    </div>

    <div style="margin-top:1rem">
      <button type="submit" class="btn">Tallenna</button>
    </div>
  </form>
</div>

</div><!-- /.admin-body -->
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
