<?php
declare(strict_types=1);

/**
 * Waypure Admin Logout Endpoint
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';

unset(
    $_SESSION['admin_logged_in'],
    $_SESSION['admin_id'],
    $_SESSION['admin_username'],
    $_SESSION['admin_name']
);

setFlash('success', 'You have been securely signed out.');
header('Location: ' . ADMIN_URL . 'login.php');
exit;

