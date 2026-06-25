<?php
require_once __DIR__ . '/../src/includes/db.php';
require_once __DIR__ . '/../src/includes/theme.php';

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_GET['page'] ?? '')));

if (!$slug) {
    http_response_code(404);
    exit;
}

$manifest = getThemeManifest();

// Etsi sivu nav- ja pages-listauksista slugin perusteella
$pageEntry = null;
foreach (array_merge($manifest['nav'] ?? [], $manifest['pages'] ?? []) as $entry) {
    if (isset($entry['slug']) && $entry['slug'] === $slug) {
        $pageEntry = $entry;
        break;
    }
}

if (!$pageEntry || !isset($pageEntry['file'])) {
    http_response_code(404);
    exit;
}

$themeFile = resolveThemePath($pageEntry['file']);
if ($themeFile === false) {
    http_response_code(404);
    exit;
}

require $themeFile;
