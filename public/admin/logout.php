<?php
require_once __DIR__ . '/../src/includes/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $_SESSION = [];
        session_destroy();
    }
}
redirect(SITE_URL . '/admin/login.php');
