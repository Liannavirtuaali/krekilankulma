<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Etusivu';

$db = getDB();

// Hevosmäärä
$stmtCount = $db->query('SELECT COUNT(*) FROM horses WHERE is_deleted = 0 AND evm = 0');
$horseCount = (int)$stmtCount->fetchColumn();

// Varsoja tänä vuonna
$thisYear = (int)date('Y');
$stmtFoals = $db->prepare('SELECT COUNT(*) FROM foals WHERE birth_year = :y');
$stmtFoals->execute([':y' => $thisYear]);
$foalCount = (int)$stmtFoals->fetchColumn();

require __DIR__ . '/../src/includes/header.php';
?>

<!-- Hero -->
<div class="frontpage-hero">
  <div class="frontpage-hero-content">
    <h1>Tervetuloa <?= e(SITE_NAME) ?>on</h1>
    <p>Täällä asuvat rakkaimmat hevosemme. Tutustu talliimme ja sen asukkaisiin!</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a class="btn-gold" href="<?= e(SITE_URL) ?>/pages/hevoset.php">Hevoset →</a>
      <a href="<?= e(SITE_URL) ?>/pages/kasvatus.php"
         style="color:var(--color-cream);text-decoration:none;font-size:var(--text-sm);opacity:.85;align-self:center;font-family:var(--font-sans);">Kasvatus →</a>
    </div>
  </div>
</div>

<!-- Overlay-kortit -->
<div class="overlay-cards">
  <a class="overlay-card" href="<?= e(SITE_URL) ?>/pages/hevoset.php">
    <img src="https://picsum.photos/seed/horses1/320/160" alt="Hevoset">
    <div class="overlay-card-stat" id="stat-hevoset"><?= $horseCount ?></div>
    <h3>Hevosta tallissa</h3>
    <p>Tammoja, oriita ja kasvavaa nuorta sukupolvea.</p>
    <span class="overlay-card-link">Katso kaikki →</span>
  </a>

  <a class="overlay-card" href="<?= e(SITE_URL) ?>/pages/kasvatus.php">
    <img src="https://picsum.photos/seed/foal2024/320/160" alt="Varsat">
    <div class="overlay-card-stat" id="stat-varsat"><?= $foalCount ?></div>
    <h3>Varsaa <?= $thisYear ?></h3>
    <p>Syntyneet ja odotetut varsat — katso kasvatusohjelma.</p>
    <span class="overlay-card-link">Kasvatussivu →</span>
  </a>

  <div class="overlay-card" style="cursor:default;">
    <div style="font-size:2rem;margin-bottom:.75rem;">📰</div>
    <h3>Ajankohtaista</h3>
    <p>Tallin viimeisin uutinen ja tapahtumat löydät täältä.</p>
    <div class="card-date"><?= date('j.n.Y') ?></div>
  </div>
</div>

<!-- Esittely + uutinen -->
<div class="frontpage-content">
  <div class="frontpage-esittely">
    <img src="https://picsum.photos/seed/barn2/600/200" alt="Talli">
    <h2>Tietoa tallista</h2>
    <p><?= e(SITE_NAME) ?> on perustettu rakkaudesta hevosiin ja virtuaaliseen hevosmaailmaan.
       Pidämme huolta jokaisesta tallin asukkaasta ja panostamme laadukkaaseen kasvatukseen.</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:1.25rem;">
      <a class="btn" href="<?= e(SITE_URL) ?>/pages/hevoset.php">Tutustu hevosiin →</a>
      <a class="btn-gold" href="<?= e(SITE_URL) ?>/pages/kasvatus.php">Kasvatus →</a>
    </div>
  </div>

  <div class="frontpage-uutinen">
    <div class="uutinen-tag">📰 Ajankohtaista</div>
    <h3>Tervetuloa tallin sivuille!</h3>
    <p>Sivusto on juuri avattu. Löydät täältä kaikki tallin hevoset, kasvatusohjelman ja yhteystiedot.</p>
    <div class="uutinen-pvm"><?= date('j.n.Y') ?></div>
  </div>
</div>

<script>
function animateCount(el, target, duration) {
  if (!el || target === 0) return;
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start = Math.min(start + step, target);
    el.textContent = Math.round(start);
    if (start >= target) clearInterval(timer);
  }, 16);
}
setTimeout(() => {
  animateCount(document.getElementById('stat-hevoset'), <?= $horseCount ?>, 800);
  animateCount(document.getElementById('stat-varsat'),  <?= $foalCount ?>,  600);
}, 150);
</script>

<?php require __DIR__ . '/../src/includes/footer.php'; ?>
