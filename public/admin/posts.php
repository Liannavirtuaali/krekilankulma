<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

$db = getDB();
$errors = [];
$success = '';

// ── POST-käsittelijä: lisää tai muokkaa ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Virheellinen pyyntö.';
    } else {
        $title   = sanitize($_POST['title']   ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $edit_id = (int)($_POST['edit_id'] ?? 0);

        if ($title === '') $errors[] = 'Otsikko on pakollinen.';
        if ($content === '') $errors[] = 'Sisältö on pakollinen.';

        if (empty($errors)) {
            // Generoi slug (sama logiikka kuin horse_add.php) — T-05-06
            $slug = slugify($title);
            $base = $slug;
            $n = 2;
            while (true) {
                $chk = $db->prepare(
                    'SELECT id FROM posts WHERE slug = :slug' .
                    ($edit_id ? ' AND id != :id' : '')
                );
                $params = [':slug' => $slug];
                if ($edit_id) $params[':id'] = $edit_id;
                $chk->execute($params);
                if (!$chk->fetch()) break;
                $slug = $base . '-' . $n++;
            }

            if ($edit_id > 0) {
                $db->prepare('UPDATE posts SET title=:t, slug=:s, content=:c WHERE id=:id')
                   ->execute([':t'=>$title, ':s'=>$slug, ':c'=>$content, ':id'=>$edit_id]);
                redirect(SITE_URL . '/admin/posts.php?updated=1');
            } else {
                $db->prepare('INSERT INTO posts (title, slug, content) VALUES (:t, :s, :c)')
                   ->execute([':t'=>$title, ':s'=>$slug, ':c'=>$content]);
                redirect(SITE_URL . '/admin/posts.php?added=1');
            }
        }
    }
}

// ── GET: määritä näkymä ──────────────────────────────────────────────────────
$action  = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);

// Lomakkeen oletusarvot
$f = ['title' => '', 'content' => '', 'edit_id' => 0];

if ($action === 'edit' && $edit_id > 0) {
    $editPost = $db->prepare('SELECT * FROM posts WHERE id = :id');
    $editPost->execute([':id' => $edit_id]);
    $editPost = $editPost->fetch();
    if ($editPost) {
        $f = ['title' => $editPost['title'], 'content' => $editPost['content'], 'edit_id' => $edit_id];
    } else {
        redirect(SITE_URL . '/admin/posts.php');
    }
}

// Jos POST-virheitä, täytä lomake lähetetyillä arvoilla
if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['title']   = sanitize($_POST['title'] ?? '');
    $f['content'] = sanitize($_POST['content'] ?? '');
    $f['edit_id'] = (int)($_POST['edit_id'] ?? 0);
    $action = $f['edit_id'] > 0 ? 'edit' : 'new';
}

// Flash-viestit
$flash = '';
if (isset($_GET['added']))   $flash = '<p class="flash-ok">Postaus lisätty.</p>';
if (isset($_GET['updated'])) $flash = '<p class="flash-ok">Muutokset tallennettu.</p>';
if (isset($_GET['deleted'])) $flash = '<p class="flash-ok">Postaus poistettu.</p>';

// Haetaan kaikki postaukset listanäkymää varten
$posts = $db->query('SELECT id, title, slug, created_at FROM posts ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Postaukset';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <h1>Postaukset</h1>
  <div class="page-actions">
    <?php if ($action !== 'list'): ?>
      <a href="<?= e(SITE_URL) ?>/admin/posts.php" class="btn-ghost">← Takaisin listaan</a>
    <?php else: ?>
      <a href="<?= e(SITE_URL) ?>/admin/posts.php?action=new" class="btn">+ Uusi postaus</a>
    <?php endif; ?>
  </div>
</div>
<div class="admin-body">

  <?php if ($flash): echo $flash; endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="flash-err">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($action === 'new' || $action === 'edit'): ?>
    <!-- ── LOMAKE ──────────────────────────────────────────────── -->
    <div class="admin-card">
      <h2><?= $action === 'edit' ? 'Muokkaa postausta' : 'Uusi postaus' ?></h2>
      <form method="post" action="<?= e(SITE_URL) ?>/admin/posts.php" class="post-admin-form">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="edit_id" value="<?= (int)$f['edit_id'] ?>">

        <div class="form-group" style="margin-bottom:1rem;">
          <label for="post-title">Otsikko *</label>
          <input type="text" id="post-title" name="title"
                 value="<?= e($f['title']) ?>" required
                 placeholder="Postauksen otsikko">
        </div>

        <div class="form-group" style="margin-bottom:1rem;">
          <label for="post-content">Sisältö *</label>
          <textarea id="post-content" name="content" required
                    placeholder="Kirjoita postauksen sisältö tähän..."><?= e($f['content']) ?></textarea>
        </div>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem;">
          <button type="submit" class="btn">
            <?= $action === 'edit' ? 'Tallenna muutokset' : 'Julkaise postaus' ?>
          </button>
          <a href="<?= e(SITE_URL) ?>/admin/posts.php" class="btn-ghost">Peruuta</a>
        </div>
      </form>
    </div>

  <?php else: ?>
    <!-- ── LISTA ───────────────────────────────────────────────── -->
    <div class="admin-card">
      <h2>Kaikki postaukset (<?= count($posts) ?>)</h2>
      <?php if (empty($posts)): ?>
        <p style="color:var(--color-text-muted);">Ei vielä postauksia. <a href="<?= e(SITE_URL) ?>/admin/posts.php?action=new">Lisää ensimmäinen →</a></p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Otsikko</th>
              <th>Luotu</th>
              <th>Muokkaa</th>
              <th>Poista</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posts as $p): ?>
              <tr>
                <td>
                  <a href="<?= e(SITE_URL) ?>/pages/postaus.php?slug=<?= rawurlencode($p['slug']) ?>"
                     target="_blank" class="btn-sm btn-view" style="margin-right:.35rem;">
                    Katso
                  </a>
                  <?= e($p['title']) ?>
                </td>
                <td><?= formatDate($p['created_at']) ?></td>
                <td>
                  <a href="<?= e(SITE_URL) ?>/admin/posts.php?action=edit&amp;id=<?= (int)$p['id'] ?>"
                     class="btn-sm btn-edit">Muokkaa</a>
                </td>
                <td>
                  <form method="post" action="<?= e(SITE_URL) ?>/admin/post_delete.php"
                        style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn-sm btn-danger"
                            onclick="return confirm('Poistetaanko postaus \'<?= e(addslashes($p['title'])) ?>\'?')">
                      Poista
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
