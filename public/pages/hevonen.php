<?php
require_once __DIR__ . '/../src/includes/db.php';

$db = getDB();

// Hae hevonen slugin tai id:n perusteella
if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])));
    $stmt = $db->prepare(
        'SELECT h.*, d.name AS discipline_name, l.name AS level_name
         FROM horses h
         LEFT JOIN disciplines d ON d.id = h.discipline_id
         LEFT JOIN levels l ON l.id = h.level_id
         WHERE h.slug = :slug AND h.is_deleted = 0'
    );
    $stmt->execute([':slug' => $slug]);
} elseif (!empty($_GET['id'])) {
    // Taaksepäin yhteensopivuus vanhoille linkeille
    $id = (int)$_GET['id'];
    $stmt = $db->prepare(
        'SELECT h.*, d.name AS discipline_name, l.name AS level_name
         FROM horses h
         LEFT JOIN disciplines d ON d.id = h.discipline_id
         LEFT JOIN levels l ON l.id = h.level_id
         WHERE h.id = :id AND h.is_deleted = 0'
    );
    $stmt->execute([':id' => $id]);
} else {
    http_response_code(404);
    $page_title = 'Hevosta ei löydy';
    require __DIR__ . '/../src/includes/header.php';
    echo '<main><p>Hevosta ei löydy.</p></main>';
    require __DIR__ . '/../src/includes/footer.php';
    exit;
}

$horse = $stmt->fetch();

if (!$horse) {
    http_response_code(404);
    $page_title = 'Hevosta ei löydy';
    require __DIR__ . '/../src/includes/header.php';
    echo '<main><p>Hevosta ei löydy tai se on poistettu.</p></main>';
    require __DIR__ . '/../src/includes/footer.php';
    exit;
}

$id = (int)$horse['id'];
$page_title = $horse['name'];

// Hae kilpailut
$stmtComp = $db->prepare(
    'SELECT competition_name, competition_date, placement, points, notes
     FROM competitions WHERE horse_id = :id ORDER BY competition_date DESC'
);
$stmtComp->execute([':id' => $id]);
$competitions = $stmtComp->fetchAll();

// Hae kuvat
$stmtPhotos = $db->prepare(
    'SELECT filename, original_name FROM horse_photos
     WHERE horse_id = :id ORDER BY sort_order ASC LIMIT 5'
);
$stmtPhotos->execute([':id' => $id]);
$photos = $stmtPhotos->fetchAll();

// Sukutaulu
$pedigree = getHorsePedigree($id);

require __DIR__ . '/../src/includes/header.php';

$genderFi = ['ori' => 'Ori', 'tamma' => 'Tamma', 'ruuna' => 'Ruuna', 'käkky' => 'Käkky'];

// Apufunktio sukutaulun hevoslinkkiin
function pedigreeHorseLink(array $h): string {
    if ($h['evm']) {
        if (!empty($h['profile_url'])) {
            $safeUrl = filter_var($h['profile_url'], FILTER_VALIDATE_URL) !== false ? $h['profile_url'] : '#';
            return '<a href="' . e($safeUrl) . '" target="_blank" rel="noopener">' . e($h['name']) . '</a>';
        }
        return e($h['name']);
    }
    return '<a href="' . e(horseUrl($h)) . '">' . e($h['name']) . '</a>';
}

// Ensimmäinen kuva hero-banneria varten
$heroPhoto = !empty($photos) ? $photos[0]['filename'] : null;
$heroStyle = $heroPhoto
    ? 'background-image: linear-gradient(160deg,rgba(42,26,16,.85) 0%,rgba(61,43,31,.65) 50%,rgba(90,64,48,.50) 100%), url(' . e(UPLOADS_URL . $heroPhoto) . ');background-size:cover;background-position:center;'
    : '';
?>

<!-- Hero-banneri -->
<div class="hero-banner" style="<?= $heroStyle ?>">
  <div class="hero-overlay">
    <h1><?= e($horse['name']) ?></h1>
    <?php if ($horse['call_name']): ?>
      <div class="horse-callname">"<?= e($horse['call_name']) ?>"</div>
    <?php endif; ?>
    <div class="hero-pills">
      <?php if ($horse['breed']): ?><span class="hero-pill"><?= e($horse['breed']) ?></span><?php endif; ?>
      <span class="hero-pill"><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></span>
      <?php if ($horse['birth_date']): ?>
        <span class="hero-pill"><?= e((string)calculateAge($horse['birth_date'])) ?> v.</span>
      <?php endif; ?>
      <?php if ($horse['discipline_name']): ?>
        <span class="hero-pill"><?= e($horse['discipline_name']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<main style="max-width:1100px;margin:0 auto;padding:0 1.5rem 3rem;">
  <div class="profile-layout">

    <!-- Pääsisältö -->
    <div class="profile-main">

      <?php if ($horse['description']): ?>
      <section>
        <h2>Kuvaus</h2>
        <p style="font-size:var(--text-base);color:var(--color-text-muted);line-height:1.75;"><?= nl2br(e($horse['description'])) ?></p>
      </section>
      <?php endif; ?>

      <!-- Sukutaulu -->
      <section class="pedigree">
        <h2>Sukutaulu</h2>
        <?php
        $sire = $pedigree['sire'] ?? null;
        $dam  = $pedigree['dam']  ?? null;
        $ss   = $sire['sire']     ?? null;
        $sd   = $sire['dam']      ?? null;
        $ds   = $dam['sire']      ?? null;
        $dd   = $dam['dam']       ?? null;
        $sss  = $ss['sire']       ?? null;
        $ssd  = $ss['dam']        ?? null;
        $sds  = $sd['sire']       ?? null;
        $sdd  = $sd['dam']        ?? null;
        $dss  = $ds['sire']       ?? null;
        $dsd  = $ds['dam']        ?? null;
        $dds  = $dd['sire']       ?? null;
        $ddd  = $dd['dam']        ?? null;

        function pedigreeCell(?array $h): string {
            if (!$h) return '<td class="ped-cell ped-empty">—</td>';
            return '<td class="ped-cell">' . pedigreeHorseLink($h) . '</td>';
        }
        ?>
        <table class="ped-table">
          <thead>
            <tr>
              <th>Vanhemmat</th>
              <th>Isovanhemmat</th>
              <th>Isoisovanhemmat</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="ped-cell ped-gen1" rowspan="4"><?= $sire ? pedigreeHorseLink($sire) : '—' ?></td>
              <td class="ped-cell ped-gen2" rowspan="2"><?= $ss ? pedigreeHorseLink($ss) : '—' ?></td>
              <?= pedigreeCell($sss) ?>
            </tr>
            <tr><?= pedigreeCell($ssd) ?></tr>
            <tr>
              <td class="ped-cell ped-gen2" rowspan="2"><?= $sd ? pedigreeHorseLink($sd) : '—' ?></td>
              <?= pedigreeCell($sds) ?>
            </tr>
            <tr><?= pedigreeCell($sdd) ?></tr>
            <tr>
              <td class="ped-cell ped-gen1" rowspan="4"><?= $dam ? pedigreeHorseLink($dam) : '—' ?></td>
              <td class="ped-cell ped-gen2" rowspan="2"><?= $ds ? pedigreeHorseLink($ds) : '—' ?></td>
              <?= pedigreeCell($dss) ?>
            </tr>
            <tr><?= pedigreeCell($dsd) ?></tr>
            <tr>
              <td class="ped-cell ped-gen2" rowspan="2"><?= $dd ? pedigreeHorseLink($dd) : '—' ?></td>
              <?= pedigreeCell($dds) ?>
            </tr>
            <tr><?= pedigreeCell($ddd) ?></tr>
          </tbody>
        </table>
        <?php if ($horse['pedigree_notes']): ?>
          <p style="margin-top:.75rem;font-size:var(--text-sm);color:var(--color-text-muted);"><?= nl2br(e($horse['pedigree_notes'])) ?></p>
        <?php endif; ?>
      </section>

      <!-- Kisakalenteri -->
      <section>
        <h2>Kisakalenteri</h2>
        <?php if (empty($competitions)): ?>
          <p style="color:var(--color-text-muted);font-family:var(--font-sans);font-size:var(--text-sm);">Ei kilpailutuloksia.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Päivämäärä</th><th>Kilpailu</th><th>Sijoitus</th><th>Pisteet</th><th>Huomiot</th></tr>
            </thead>
            <tbody>
              <?php foreach ($competitions as $comp): ?>
                <tr>
                  <td><?= e(formatDate($comp['competition_date'])) ?></td>
                  <td><?= e($comp['competition_name']) ?></td>
                  <td><?= e($comp['placement'] ?? '—') ?></td>
                  <td><?= $comp['points'] !== null ? e((string)$comp['points']) : '—' ?></td>
                  <td><?= e($comp['notes'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

      <!-- Kuvagalleria -->
      <?php if (!empty($photos)): ?>
      <section>
        <h2>Kuvagalleria</h2>
        <div class="gallery">
          <?php foreach ($photos as $photo): ?>
            <img src="<?= e(UPLOADS_URL . $photo['filename']) ?>"
                 alt="<?= e($photo['original_name'] ?? $horse['name']) ?>">
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div><!-- /.profile-main -->

    <!-- Sivupalkki -->
    <aside class="profile-sidebar">

      <div class="sidebar-card">
        <h3>Perustiedot</h3>
        <dl>
          <?php if ($horse['breed']): ?>
            <div class="info-row"><dt>Rotu</dt><dd><?= e($horse['breed']) ?></dd></div>
          <?php endif; ?>
          <div class="info-row"><dt>Sukupuoli</dt><dd><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></dd></div>
          <?php if ($horse['birth_date']): ?>
            <div class="info-row"><dt>Syntymäaika</dt><dd><?= e(formatDate($horse['birth_date'])) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['color']): ?>
            <div class="info-row"><dt>Väri</dt><dd><?= e($horse['color']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['height_cm']): ?>
            <div class="info-row"><dt>Säkäkorkeus</dt><dd><?= e((string)$horse['height_cm']) ?> cm</dd></div>
          <?php endif; ?>
          <?php if ($horse['vh_id']): ?>
            <div class="info-row"><dt>VH-tunnus</dt><dd style="font-family:var(--font-mono);font-size:var(--text-xs);"><?= e($horse['vh_id']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['discipline_name']): ?>
            <div class="info-row"><dt>Laji</dt><dd><?= e($horse['discipline_name']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['level_name']): ?>
            <div class="info-row"><dt>Taso</dt><dd><?= e($horse['level_name']) ?></dd></div>
          <?php endif; ?>
        </dl>
      </div>

      <?php if ($horse['owner_name'] || $horse['breeder_name'] || $horse['importer_name']): ?>
      <div class="sidebar-card">
        <h3>Omistus & kasvatus</h3>
        <dl>
          <?php if ($horse['owner_name']): ?>
            <div class="info-row"><dt>Omistaja</dt><dd><?= e($horse['owner_name']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['breeder_name']): ?>
            <div class="info-row"><dt>Kasvattaja</dt><dd><?= e($horse['breeder_name']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['importer_name']): ?>
            <div class="info-row"><dt>Tuoja</dt><dd><?= e($horse['importer_name']) ?></dd></div>
          <?php endif; ?>
        </dl>
      </div>
      <?php endif; ?>

    </aside>

  </div><!-- /.profile-layout -->
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
