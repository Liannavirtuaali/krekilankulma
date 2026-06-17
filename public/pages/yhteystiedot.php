<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Yhteystiedot';

require __DIR__ . '/../src/includes/header.php';
?>
<main>
  <h1>Yhteystiedot</h1>

  <p>Tervetuloa ottamaan yhteyttä <?= e(SITE_NAME) ?>iin!</p>
  <p>Ota yhteyttä sähköpostitse — vastaamme mahdollisimman pian.</p>

  <dl class="horse-profile-info" style="margin-top:1.5rem;max-width:400px;">
    <dt>Talli</dt>
    <dd><?= e(SITE_NAME) ?></dd>
    <dt>Sähköposti</dt>
    <dd><a href="mailto:talli@example.com">talli@example.com</a></dd>
  </dl>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
