<?php
require_once __DIR__ . '/../src/includes/db.php';

$db = getDB();

// Hae hevonen slugin tai id:n perusteella
if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['slug'])));
    $stmt = $db->prepare(
        'SELECT h.*,
                (SELECT GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR \', \')
                 FROM horse_disciplines hd
                 JOIN disciplines d ON d.id = hd.discipline_id
                 WHERE hd.horse_id = h.id) AS discipline_names,
                b.name AS breed_name, c.name AS color_name
         FROM horses h
         LEFT JOIN breeds b ON b.id = h.breed_id
         LEFT JOIN colors c ON c.id = h.color_id
         WHERE h.slug = :slug AND h.is_deleted = 0'
    );
    $stmt->execute([':slug' => $slug]);
} elseif (!empty($_GET['id'])) {
    // Taaksepäin yhteensopivuus vanhoille linkeille
    $id = (int)$_GET['id'];
    $stmt = $db->prepare(
        'SELECT h.*,
                (SELECT GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR \', \')
                 FROM horse_disciplines hd
                 JOIN disciplines d ON d.id = hd.discipline_id
                 WHERE hd.horse_id = h.id) AS discipline_names,
                b.name AS breed_name, c.name AS color_name
         FROM horses h
         LEFT JOIN breeds b ON b.id = h.breed_id
         LEFT JOIN colors c ON c.id = h.color_id
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
    'SELECT competition_date, discipline, country, organizer, organizer_url, class, placement, points, notes
     FROM competitions WHERE horse_id = :id ORDER BY competition_date DESC'
);
$stmtComp->execute([':id' => $id]);
$competitions = $stmtComp->fetchAll();

// Hae kuvat
$stmtPhotos = $db->prepare(
    'SELECT filename, original_name, title, caption FROM horse_photos
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
    if ($h['evm'] || !empty($h['ancestor'])) {
        if (!empty($h['profile_url'])) {
            $safeUrl = filter_var($h['profile_url'], FILTER_VALIDATE_URL) !== false ? $h['profile_url'] : '#';
            $nameLink = '<a href="' . e($safeUrl) . '" class="ext-link" target="_blank" rel="noopener">' . e($h['name']) . '</a>';
        } else {
            $nameLink = e($h['name']);
        }
    } else {
        $nameLink = '<a href="' . e(horseUrl($h)) . '">' . e($h['name']) . '</a>';
    }
    $meta = [];
    if (!empty($h['breed_abbr'])) $meta[] = e($h['breed_abbr']);
    if (!empty($h['color_abbr'])) $meta[] = e($h['color_abbr']);
    if (!empty($h['height_cm']))  $meta[] = e((string)$h['height_cm']) . ' cm';
    $metaHtml = $meta ? '<span class="ped-meta">' . implode(' · ', $meta) . '</span>' : '';
    return $nameLink . $metaHtml;
}

// Ensimmäinen kuva hero-banneria varten
$heroPhoto = !empty($photos) ? $photos[0]['filename'] : null;
$heroStyle = $heroPhoto
    ? 'background-image: var(--hero-overlay), url(' . e(UPLOADS_URL . $heroPhoto) . ');background-size:cover;background-position:center;'
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
      <?php if ($horse['breed_name']): ?><span class="hero-pill"><?= e($horse['breed_name']) ?></span><?php endif; ?>
      <span class="hero-pill"><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></span>
      <?php if ($horse['birth_date']): ?>
        <?php
          $agingSystem = $horse['aging_system'] ?: 'IRL';
          $heroAge = calculateAgeBySystem($horse['birth_date'], $agingSystem);
        ?>
        <span class="hero-pill"><?= e((string)$heroAge) ?> v. (<?= e($agingSystem) ?>)</span>
      <?php endif; ?>
      <?php if ($horse['discipline_names']): ?>
        <?php
          $levelParts = [];
          if (!empty($horse['level_ko'])) $levelParts[] = 'ko: ' . $horse['level_ko'];
          if (!empty($horse['level_re'])) $levelParts[] = 're: ' . $horse['level_re'];
        ?>
        <span class="hero-pill">
          <?= e($horse['discipline_names']) ?>
          <?php if ($levelParts): ?>
            <span style="opacity:.75;font-size:.85em">(<?= e(implode(', ', $levelParts)) ?>)</span>
          <?php endif; ?>
        </span>
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

      <!-- Kuvagalleria poistettu täältä, siirretty koko leveyteen alempana -->

    </div><!-- /.profile-main -->

    <!-- Sivupalkki -->
    <aside class="profile-sidebar">

      <div class="sidebar-card">
        <h3>Perustiedot</h3>
        <dl>
          <?php if ($horse['breed_name']): ?>
            <div class="info-row"><dt>Rotu</dt><dd><?= e($horse['breed_name']) ?></dd></div>
          <?php endif; ?>
          <div class="info-row"><dt>Sukupuoli</dt><dd><?= e($genderFi[$horse['gender']] ?? $horse['gender']) ?></dd></div>
          <?php if ($horse['birth_date']): ?>
            <?php
              $sidebarSystem = $horse['aging_system'] ?: 'IRL';
              $sidebarAge    = calculateAgeBySystem($horse['birth_date'], $sidebarSystem);
            ?>
            <div class="info-row"><dt>Syntymäaika</dt><dd><?= e(formatDate($horse['birth_date'])) ?> <span style="color:var(--color-text-muted);font-size:var(--text-sm)">(<?= e((string)$sidebarAge) ?> v., <?= e($sidebarSystem) ?>)</span></dd></div>
          <?php endif; ?>
          <?php if ($horse['color_name']): ?>
            <div class="info-row"><dt>Väri</dt><dd><?= e($horse['color_name']) ?><?php if (!empty($horse['genes'])): ?> <span style="color:var(--color-text-muted)">(<?= e($horse['genes']) ?>)</span><?php endif; ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['height_cm']): ?>
            <div class="info-row"><dt>Säkäkorkeus</dt><dd><?= e((string)$horse['height_cm']) ?> cm</dd></div>
          <?php endif; ?>
          <?php if ($horse['vh_id']): ?>
            <div class="info-row"><dt>VH-tunnus</dt><dd style="font-family:var(--font-mono);font-size:var(--text-xs);"><?= e($horse['vh_id']) ?></dd></div>
          <?php endif; ?>
          <?php if ($horse['discipline_names']): ?>
            <?php
              $levelParts = [];
              if (!empty($horse['level_ko'])) $levelParts[] = 'ko: ' . $horse['level_ko'];
              if (!empty($horse['level_re'])) $levelParts[] = 're: ' . $horse['level_re'];
            ?>
            <div class="info-row">
              <dt>Lajit</dt>
              <dd>
                <?= e($horse['discipline_names']) ?>
                <?php if ($levelParts): ?>
                  <span style="color:var(--color-text-muted)">(<?= e(implode(', ', $levelParts)) ?>)</span>
                <?php endif; ?>
              </dd>
            </div>
          <?php elseif (!empty($horse['level_ko']) || !empty($horse['level_re'])): ?>
            <?php
              $levelParts = [];
              if (!empty($horse['level_ko'])) $levelParts[] = 'ko: ' . $horse['level_ko'];
              if (!empty($horse['level_re'])) $levelParts[] = 're: ' . $horse['level_re'];
            ?>
            <div class="info-row"><dt>Taso</dt><dd><?= e(implode(', ', $levelParts)) ?></dd></div>
          <?php endif; ?>
        </dl>
      </div>

<?php if ($horse['owner_name'] || $horse['owner_email'] || $horse['breeder_name'] || $horse['breeder_email'] || $horse['importer_name'] || $horse['importer_email']): ?>
      <div class="sidebar-card">
        <h3>Omistus & kasvatus</h3>
        <dl>
          <?php if ($horse['owner_name'] || $horse['owner_email']): ?>
            <div class="info-row"><dt>Omistaja</dt><dd>
              <?= e($horse['owner_name']) ?>
              <?php if ($horse['owner_email']): ?><br><a href="mailto:<?= e($horse['owner_email']) ?>" style="font-size:var(--text-sm)"><?= e($horse['owner_email']) ?></a><?php endif; ?>
            </dd></div>
          <?php endif; ?>
          <?php if ($horse['breeder_name'] || $horse['breeder_email']): ?>
            <div class="info-row"><dt>Kasvattaja</dt><dd>
              <?= e($horse['breeder_name']) ?>
              <?php if ($horse['breeder_email']): ?><br><a href="mailto:<?= e($horse['breeder_email']) ?>" style="font-size:var(--text-sm)"><?= e($horse['breeder_email']) ?></a><?php endif; ?>
            </dd></div>
          <?php endif; ?>
          <?php if ($horse['importer_name'] || $horse['importer_email']): ?>
            <div class="info-row"><dt>Tuoja</dt><dd>
              <?= e($horse['importer_name']) ?>
              <?php if ($horse['importer_email']): ?><br><a href="mailto:<?= e($horse['importer_email']) ?>" style="font-size:var(--text-sm)"><?= e($horse['importer_email']) ?></a><?php endif; ?>
            </dd></div>
          <?php endif; ?>
        </dl>
      </div>
      <?php endif; ?>

    </aside>

  </div><!-- /.profile-layout -->

  <!-- Sukutaulu — koko leveys -->
  <section class="pedigree profile-fullwidth">
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

    function pedigreeCell(?array $h, int $row): string {
        $style = 'grid-column:3; grid-row:' . $row;
        if (!$h) return '<div class="ped-cell ped-empty" style="' . $style . '">—</div>';
        return '<div class="ped-cell" style="' . $style . '">' . pedigreeHorseLink($h) . '</div>';
    }
    ?>
    <div class="ped-tree">
      <div class="ped-header">
        <div>Vanhemmat</div>
        <div>Isovanhemmat</div>
        <div>Isoisovanhemmat</div>
      </div>
      <div class="ped-grid">
        <div class="ped-cell ped-gen1" style="grid-column:1; grid-row:1/span 4"><?= $sire ? pedigreeHorseLink($sire) : '—' ?></div>
        <div class="ped-cell ped-gen1" style="grid-column:1; grid-row:5/span 4"><?= $dam  ? pedigreeHorseLink($dam)  : '—' ?></div>
        <div class="ped-cell ped-gen2" style="grid-column:2; grid-row:1/span 2"><?= $ss ? pedigreeHorseLink($ss) : '—' ?></div>
        <div class="ped-cell ped-gen2" style="grid-column:2; grid-row:3/span 2"><?= $sd ? pedigreeHorseLink($sd) : '—' ?></div>
        <div class="ped-cell ped-gen2" style="grid-column:2; grid-row:5/span 2"><?= $ds ? pedigreeHorseLink($ds) : '—' ?></div>
        <div class="ped-cell ped-gen2" style="grid-column:2; grid-row:7/span 2"><?= $dd ? pedigreeHorseLink($dd) : '—' ?></div>
        <?= pedigreeCell($sss, 1) ?>
        <?= pedigreeCell($ssd, 2) ?>
        <?= pedigreeCell($sds, 3) ?>
        <?= pedigreeCell($sdd, 4) ?>
        <?= pedigreeCell($dss, 5) ?>
        <?= pedigreeCell($dsd, 6) ?>
        <?= pedigreeCell($dds, 7) ?>
        <?= pedigreeCell($ddd, 8) ?>
      </div>
    </div>
    <?php if ($horse['pedigree_notes']): ?>
      <p style="margin-top:.75rem;font-size:var(--text-sm);color:var(--color-text-muted);"><?= nl2br(e($horse['pedigree_notes'])) ?></p>
    <?php endif; ?>
  </section>

  <!-- Kisakalenteri — koko leveys -->
  <section class="profile-fullwidth">
    <h2>Kisakalenteri</h2>
    <?php if (empty($competitions)): ?>
      <p style="color:var(--color-text-muted);font-family:var(--font-sans);font-size:var(--text-sm);">Ei kilpailutuloksia.</p>
    <?php else: ?>
      <div class="comp-list">
        <div class="comp-header">
          <div>PVM</div>
          <div>Laji</div>
          <div>Maa</div>
          <div>Järjestäjä</div>
          <div>Luokka</div>
          <div>Tulos</div>
          <div>Pisteet</div>
          <div>Huom</div>
        </div>
        <?php foreach ($competitions as $comp): ?>
          <div class="comp-row">
            <div class="comp-cell" data-label="PVM"><?= e(formatDate($comp['competition_date'])) ?></div>
            <div class="comp-cell" data-label="Laji"><?= e($comp['discipline'] ?? '—') ?></div>
            <div class="comp-cell" data-label="Maa"><?= e($comp['country'] ?? '—') ?></div>
            <div class="comp-cell" data-label="Järjestäjä">
              <?php if (!empty($comp['organizer_url'])): ?>
                <a href="<?= e($comp['organizer_url']) ?>" target="_blank" rel="noopener"><?= e($comp['organizer'] ?? '—') ?></a>
              <?php else: ?>
                <?= e($comp['organizer'] ?? '—') ?>
              <?php endif; ?>
            </div>
            <div class="comp-cell" data-label="Luokka"><?= e($comp['class'] ?? '—') ?></div>
            <div class="comp-cell" data-label="Tulos"><?= e($comp['placement'] ?? '—') ?></div>
            <div class="comp-cell" data-label="Pisteet"><?= $comp['points'] !== null ? e((string)$comp['points']) : '—' ?></div>
            <div class="comp-cell" data-label="Huom"><?= e($comp['notes'] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Kuvagalleria — koko leveys -->
  <section class="profile-fullwidth">
    <h2>Kuvagalleria</h2>
    <div class="gallery">
      <?php if (!empty($photos)): ?>
        <?php foreach ($photos as $photo): ?>
          <?php
            $altText  = e($photo['title'] ?? $photo['original_name'] ?? $horse['name']);
            $captionJ = htmlspecialchars($photo['caption'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $titleJ   = htmlspecialchars($photo['title']   ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
          ?>
          <div class="gallery-item" onclick="openLightbox(this)" title="Avaa suuremmaksi"
               data-caption="<?= $captionJ ?>" data-title="<?= $titleJ ?>">
            <img src="<?= e(UPLOADS_URL . $photo['filename']) ?>" alt="<?= $altText ?>">
            <?php if (!empty($photo['title'])): ?>
              <div class="gallery-item-label"><?= e($photo['title']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="gallery-item gallery-placeholder">
            <span>Ei kuvaa</span>
          </div>
        <?php endfor; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Lightbox -->
  <div id="lightbox" class="lightbox" onclick="closeLightbox()" style="display:none">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Sulje">&times;</button>
    <div class="lightbox-inner" onclick="event.stopPropagation()">
      <img id="lightbox-img" src="" alt="">
      <div id="lightbox-caption" class="lightbox-caption" style="display:none">
        <strong id="lightbox-title"></strong>
        <span id="lightbox-text"></span>
      </div>
    </div>
  </div>
  <script>
  function openLightbox(el) {
    var img     = el.querySelector('img');
    var title   = el.dataset.title   || '';
    var caption = el.dataset.caption || '';
    document.getElementById('lightbox-img').src = img.src;
    document.getElementById('lightbox-img').alt = img.alt;
    document.getElementById('lightbox-title').textContent   = title;
    document.getElementById('lightbox-text').textContent    = caption;
    var capEl = document.getElementById('lightbox-caption');
    capEl.style.display = (title || caption) ? 'block' : 'none';
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLightbox(); });
  </script>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
