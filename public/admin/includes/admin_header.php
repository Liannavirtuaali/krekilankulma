<?php
// Varmista että config on ladattu
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../../src/includes/config.php';
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>Admin — <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(SITE_URL) ?>/assets/css/style.css">
  <style>
    .admin-header { background: #2a1a0f; }
    .admin-header nav a { color: #e8d5b7; font-size: 0.85rem; }
    .admin-header nav a:hover { color: #fff; }
    .admin-nav-bar { display: flex; gap: 1.5rem; align-items: center; padding: 0.75rem 2rem; flex-wrap: wrap; }
    .admin-main { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
    .admin-footer { background: #2a1a0f; color: #c9b89a; padding: 0.75rem 2rem; display: flex; justify-content: flex-end; }
    .admin-footer button { background: #c9392b; color: #fff; border: none; padding: 0.4rem 1rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
    .admin-footer button:hover { background: #a02a1f; }
    .stat-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
    .stat-card { background: #fff; border: 1px solid #e0d5c5; border-radius: 6px; padding: 1rem; text-align: center; }
    .stat-card .stat-num { font-size: 2rem; font-weight: bold; color: #3d2b1f; }
    .stat-card .stat-label { font-size: 0.8rem; color: #888; }
    .admin-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .admin-table th { background: #3d2b1f; color: #e8d5b7; padding: 0.5rem 0.75rem; text-align: left; font-size: 0.85rem; }
    .admin-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #e0d5c5; font-size: 0.9rem; vertical-align: middle; }
    .admin-table tr:hover td { background: #f9f6f2; }
    .btn-sm { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 3px; font-size: 0.8rem; text-decoration: none; border: none; cursor: pointer; }
    .btn-edit { background: #4a6fa5; color: #fff; }
    .btn-danger { background: #c9392b; color: #fff; }
    .btn-view { background: #5a8a5a; color: #fff; }
    .btn-photos { background: #8a5a8a; color: #fff; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: bold; margin-bottom: 0.25rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; font-family: inherit; }
    .form-group textarea { min-height: 100px; resize: vertical; }
    .flash-ok { background: #d4edda; color: #155724; padding: 0.6rem 1rem; border-radius: 4px; margin-bottom: 1rem; }
    .flash-err { background: #f8d7da; color: #721c24; padding: 0.6rem 1rem; border-radius: 4px; margin-bottom: 1rem; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<header class="admin-header">
  <nav class="admin-nav-bar">
    <a href="<?= e(SITE_URL) ?>/admin/" style="font-weight:bold">🐴 Admin</a>
    <a href="<?= e(SITE_URL) ?>/admin/horses.php">Hevoset</a>
    <a href="<?= e(SITE_URL) ?>/admin/horse_add.php">+ Lisää hevonen</a>
    <a href="<?= e(SITE_URL) ?>/" style="color:#999">← Julkinen sivu</a>
  </nav>
</header>
<div class="admin-main">
