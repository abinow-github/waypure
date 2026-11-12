<?php
declare(strict_types=1);

/**
 * Waypure E-Commerce Platform Configuration
 * Pure Core PHP 8.4+ Architecture
 */

// Timezone
date_default_timezone_set('Asia/Kolkata');

// App metadata
define('APP_NAME', 'Waypure');
define('APP_VERSION', '2.0.0');
define('APP_TAGLINE', 'DRINK · EAT · BREATHE PURE');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOAD_DIR', ROOT_PATH . '/uploads/');

// Dynamic URL Resolution (Supports XAMPP subfolder or root domain)
if (PHP_SAPI === 'cli') {
    define('BASE_URL', 'http://localhost/waypure/');
} else {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Normalize paths to determine subfolder relative to document root
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $appRoot = str_replace('\\', '/', ROOT_PATH);

    if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $subFolder = trim(substr($appRoot, strlen($docRoot)), '/');
        $baseUri = $subFolder !== '' ? '/' . $subFolder . '/' : '/';
    } else {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        $scriptDir = preg_replace('#/(admin|api|includes)(/.*)?$#', '', $scriptDir);
        $baseUri = rtrim((string)$scriptDir, '/') . '/';
    }

    define('BASE_URL', $protocol . $host . $baseUri);
}

define('UPLOAD_URL', BASE_URL . 'uploads/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('ADMIN_URL', BASE_URL . 'admin/');

// Session Start & Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 86400 * 30, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

