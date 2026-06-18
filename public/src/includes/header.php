<?php
/**
 * Sivupohjan yläosa — sisällytetään joka sivulla
 *
 * Vaatii ennen include:a:
 *   $page_title = 'Sivun otsikko';
 *   require_once __DIR__ . '/../src/includes/header.php';
 */

// Varmista että config on ladattu
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/config.php';
}

// Haetaan tallin nimi tietokannasta (kerran per pyyntö)
if (!isset($GLOBALS['stable_name'])) {
    try {
        $db = getDB();
        $val = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'stable_name' LIMIT 1")->fetchColumn();
        $GLOBALS['stable_name'] = ($val !== false && $val !== '') ? $val : SITE_NAME;
    } catch (Exception $e) {
        $GLOBALS['stable_name'] = SITE_NAME;
    }
}
$site_display_name = $GLOBALS['stable_name'];

// XSS-suojaus: sanitoi $page_title
$page_title = isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : $site_display_name;
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?> — <?= htmlspecialchars($site_display_name, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<header>
  <div class="site-header">
    <a href="<?= SITE_URL ?>/" class="site-title"><?= htmlspecialchars($site_display_name, ENT_QUOTES, 'UTF-8') ?></a>
  </div>
  <?php require_once __DIR__ . '/nav.php'; ?>
</header>
