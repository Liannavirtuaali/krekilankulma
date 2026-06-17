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
?>
<main>
  <h1><?= e($horse['name']) ?></h1>
  <?php if ($horse['call_name']): ?>
    <p class="call-name">"<?= e($horse['call_name']) ?>"</p>
  <?php endif; ?>

  <!-- Perustiedot -->
  <section>
    <h2>Perustiedot</h2>
    <dl class="horse-profile-info">
      <?php if ($horse['breed']): ?>
        <dt>Rotu</dt><dd><?= e($horse['breed']) ?></dd>
      <?php endif; ?>
      <dt>Sukupuoli</dt><dd><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></dd>
      <?php if ($horse['birth_date']): ?>
        <dt>Syntymäaika</dt><dd><?= e(formatDate($horse['birth_date'])) ?> (<?= e((string)calculateAge($horse['birth_date'])) ?> v.)</dd>
      <?php endif; ?>
      <?php if ($horse['color']): ?>
        <dt>Väri</dt><dd><?= e($horse['color']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['height_cm']): ?>
        <dt>Säkäkorkeus</dt><dd><?= e((string)$horse['height_cm']) ?> cm</dd>
      <?php endif; ?>
      <?php if ($horse['vh_id']): ?>
        <dt>VH-tunnus</dt><dd><?= e($horse['vh_id']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['discipline_name']): ?>
        <dt>Laji</dt><dd><?= e($horse['discipline_name']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['level_name']): ?>
        <dt>Taso</dt><dd><?= e($horse['level_name']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['owner_name']): ?>
        <dt>Omistaja</dt><dd><?= e($horse['owner_name']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['breeder_name']): ?>
        <dt>Kasvattaja</dt><dd><?= e($horse['breeder_name']) ?></dd>
      <?php endif; ?>
      <?php if ($horse['importer_name']): ?>
        <dt>Tuoja</dt><dd><?= e($horse['importer_name']) ?></dd>
      <?php endif; ?>
    </dl>
    <?php if ($horse['description']): ?>
      <h3>Kuvaus</h3>
      <p><?= nl2br(e($horse['description'])) ?></p>
    <?php endif; ?>
  </section>

  <!-- Sukutaulu -->
  <section class="pedigree">
    <h2>Sukutaulu</h2>
    <?php
    // Haetaan sukupolvien viittaukset selkeästi
    $sire     = $pedigree['sire'] ?? null;          // Isä
    $dam      = $pedigree['dam']  ?? null;           // Emä
    $ss       = $sire['sire']     ?? null;           // Isän isä
    $sd       = $sire['dam']      ?? null;           // Isän emä
    $ds       = $dam['sire']      ?? null;           // Emän isä
    $dd       = $dam['dam']       ?? null;           // Emän emä
    $sss      = $ss['sire']       ?? null;           // Isän isän isä
    $ssd      = $ss['dam']        ?? null;           // Isän isän emä
    $sds      = $sd['sire']       ?? null;           // Isän emän isä
    $sdd      = $sd['dam']        ?? null;           // Isän emän emä
    $dss      = $ds['sire']       ?? null;           // Emän isän isä
    $dsd      = $ds['dam']        ?? null;           // Emän isän emä
    $dds      = $dd['sire']       ?? null;           // Emän emän isä
    $ddd      = $dd['dam']        ?? null;           // Emän emän emä

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
        <!-- Rivi 1: Isä / Isän isä / Isän isän isä -->
        <tr>
          <td class="ped-cell ped-gen1" rowspan="4"><?= $sire ? pedigreeHorseLink($sire) : '—' ?></td>
          <td class="ped-cell ped-gen2" rowspan="2"><?= $ss ? pedigreeHorseLink($ss) : '—' ?></td>
          <?= pedigreeCell($sss) ?>
        </tr>
        <!-- Rivi 2: Isän isän emä -->
        <tr><?= pedigreeCell($ssd) ?></tr>
        <!-- Rivi 3: Isän emä / Isän emän isä -->
        <tr>
          <td class="ped-cell ped-gen2" rowspan="2"><?= $sd ? pedigreeHorseLink($sd) : '—' ?></td>
          <?= pedigreeCell($sds) ?>
        </tr>
        <!-- Rivi 4: Isän emän emä -->
        <tr><?= pedigreeCell($sdd) ?></tr>
        <!-- Rivi 5: Emä / Emän isä / Emän isän isä -->
        <tr>
          <td class="ped-cell ped-gen1" rowspan="4"><?= $dam ? pedigreeHorseLink($dam) : '—' ?></td>
          <td class="ped-cell ped-gen2" rowspan="2"><?= $ds ? pedigreeHorseLink($ds) : '—' ?></td>
          <?= pedigreeCell($dss) ?>
        </tr>
        <!-- Rivi 6: Emän isän emä -->
        <tr><?= pedigreeCell($dsd) ?></tr>
        <!-- Rivi 7: Emän emä / Emän emän isä -->
        <tr>
          <td class="ped-cell ped-gen2" rowspan="2"><?= $dd ? pedigreeHorseLink($dd) : '—' ?></td>
          <?= pedigreeCell($dds) ?>
        </tr>
        <!-- Rivi 8: Emän emän emä -->
        <tr><?= pedigreeCell($ddd) ?></tr>
      </tbody>
    </table>
    <?php if ($horse['pedigree_notes']): ?>
      <h3>Sukuselvitys</h3>
      <p><?= nl2br(e($horse['pedigree_notes'])) ?></p>
    <?php endif; ?>
  </section>

  <!-- Kisakalenteri -->
  <section>
    <h2>Kisakalenteri</h2>
    <?php if (empty($competitions)): ?>
      <p>Ei kilpailutuloksia.</p>
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
  <section>
    <h2>Kuvagalleria</h2>
    <?php if (empty($photos)): ?>
      <p>Ei kuvia.</p>
    <?php else: ?>
      <div class="gallery">
        <?php foreach ($photos as $photo): ?>
          <img src="<?= e(UPLOADS_URL . $photo['filename']) ?>"
               alt="<?= e($photo['original_name'] ?? $horse['name']) ?>">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
