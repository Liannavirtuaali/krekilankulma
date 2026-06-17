<?php
require_once __DIR__ . '/../src/includes/db.php';

$page_title = 'Kasvatus';

$db = getDB();
$stmt = $db->prepare(
    'SELECT f.*, sire.name AS sire_name, dam.name AS dam_name
     FROM foals f
     LEFT JOIN horses sire ON sire.id = f.sire_id AND sire.is_deleted = 0
     LEFT JOIN horses dam  ON dam.id  = f.dam_id  AND dam.is_deleted = 0
     ORDER BY FIELD(f.status, \'expected\', \'born\'), f.birth_year DESC'
);
$stmt->execute();
$allFoals = $stmt->fetchAll();

$expected = array_filter($allFoals, fn($f) => $f['status'] === 'expected');
$born     = array_filter($allFoals, fn($f) => $f['status'] === 'born');

require __DIR__ . '/../src/includes/header.php';

$genderFi = ['stallion' => 'Ori', 'mare' => 'Tamma', 'gelding' => 'Ruuna'];

function foalRow(array $foal, array $genderFi): string {
    $name   = e($foal['foal_name'] ?? '—');
    $sire   = e($foal['sire_name'] ?? '—');
    $dam    = e($foal['dam_name'] ?? '—');
    $year   = e((string)($foal['birth_year'] ?? '—'));
    $gender = e($genderFi[$foal['gender']] ?? ($foal['gender'] ?? '—'));
    return "<tr><td>{$name}</td><td>{$sire}</td><td>{$dam}</td><td>{$year}</td><td>{$gender}</td></tr>";
}
?>
<main>
  <h1>Kasvatus</h1>

  <?php if (empty($allFoals)): ?>
    <p>Tallissa ei ole vielä kasvatustietoja.</p>
  <?php else: ?>

    <?php if (!empty($expected)): ?>
      <h2>Odotetut varsat</h2>
      <table>
        <thead><tr><th>Nimi</th><th>Isä</th><th>Emä</th><th>Syntymävuosi</th><th>Sukupuoli</th></tr></thead>
        <tbody>
          <?php foreach ($expected as $foal): ?>
            <?= foalRow($foal, $genderFi) ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if (!empty($born)): ?>
      <h2>Syntyneet varsat</h2>
      <table>
        <thead><tr><th>Nimi</th><th>Isä</th><th>Emä</th><th>Syntymävuosi</th><th>Sukupuoli</th></tr></thead>
        <tbody>
          <?php foreach ($born as $foal): ?>
            <?= foalRow($foal, $genderFi) ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  <?php endif; ?>
</main>
<?php require __DIR__ . '/../src/includes/footer.php'; ?>
