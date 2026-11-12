<?php
declare(strict_types=1);

/**
 * Waypure Admin Authentication Guard Middleware
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/functions.php';

if (!isAdminLoggedIn()) {
    $_SESSION['admin_intended_url'] = $_SERVER['REQUEST_URI'] ?? (ADMIN_URL . 'index.php');
    setFlash('warning', 'Please authenticate to access the administration dashboard.');
    header('Location: ' . ADMIN_URL . 'login.php');
    exit;
}

