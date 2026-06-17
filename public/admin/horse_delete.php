<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

// Hyväksy vain POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/admin/horses.php');
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    redirect(SITE_URL . '/admin/horses.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    redirect(SITE_URL . '/admin/horses.php');
}

$db = getDB();
$stmt = $db->prepare('UPDATE horses SET is_deleted = 1, deleted_at = NOW() WHERE id = :id AND is_deleted = 0');
$stmt->execute([':id' => $id]);

redirect(SITE_URL . '/admin/horses.php?deleted=1');
