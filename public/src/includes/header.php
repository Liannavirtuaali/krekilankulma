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

// XSS-suojaus: sanitoi $page_title
$page_title = isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?> — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<header>
  <div class="site-header">
    <a href="<?= SITE_URL ?>/pages/index.php" class="site-title"><?= SITE_NAME ?></a>
  </div>
  <?php require_once __DIR__ . '/nav.php'; ?>
</header>
