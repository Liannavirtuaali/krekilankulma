<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Yhteystiedot';

require __DIR__ . '/../src/includes/header.php';
?>

<div class="page-title-band">
  <h1>Yhteystiedot</h1>
  <div class="breadcrumb">Etusivu › Yhteystiedot</div>
</div>

<main>
  <div class="contact-card">
    <div class="contact-card-icon">👤</div>
    <h2><?= e(SITE_NAME) ?></h2>
    <div class="vrl-badge">VRL-00000</div>

    <hr class="contact-divider">

    <div class="contact-row">
      <div class="contact-icon">✉️</div>
      <div>
        <div class="contact-label">Sähköposti</div>
        <div class="contact-value">
          <a href="mailto:talli@example.com">talli@example.com</a>
        </div>
      </div>
    </div>

    <div class="contact-row">
      <div class="contact-icon">🏷️</div>
      <div>
        <div class="contact-label">Nimimerkki</div>
        <div class="contact-value">TallinOmistaja</div>
      </div>
    </div>

    <div class="contact-row">
      <div class="contact-icon">🔖</div>
      <div>
        <div class="contact-label">VRL-tunnus</div>
        <div class="contact-value"><span class="mono">VRL-00000</span></div>
      </div>
    </div>

    <div style="margin-top:1.75rem;">
      <a class="btn" href="mailto:talli@example.com">✉ Lähetä sähköpostia</a>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../src/includes/footer.php'; ?>
