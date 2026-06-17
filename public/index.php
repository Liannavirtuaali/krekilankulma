<?php
/**
 * Juuren uudelleenohjaus → pages/index.php
 */
require_once __DIR__ . '/src/includes/config.php';
header('Location: ' . SITE_URL . '/pages/index.php', true, 302);
exit;
