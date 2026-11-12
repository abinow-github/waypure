<?php
declare(strict_types=1);

/**
 * Waypure Core Helper Functions & Security Library
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Escape HTML output securely
 */
function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Convert string to URL-friendly slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim((string)$text, '-');
    $text = preg_replace('~-+~', '-', (string)$text);
    $text = strtolower((string)$text);

    return empty($text) ? 'item-' . bin2hex(random_bytes(3)) : $text;
}

/**
 * Format currency amount
 */
function formatPrice(float|int|string|null $amount): string {
    $num = (float)($amount ?? 0);
    return '₹' . number_format($num);
}

/**
 * Format date in Indian Standard Time readable format
 */
function formatDate(?string $timestamp, string $format = 'd M Y, h:i A'): string {
    if (empty($timestamp)) {
        return '—';
    }
    try {
        $dt = new DateTime($timestamp);
        return $dt->format($format);
    } catch (Exception) {
        return (string)$timestamp;
    }
}

/**
 * Generate CSRF Token and store in session
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}

/**
 * Render hidden CSRF form input field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

/**
 * Resolve asset or image URL dynamically with BASE_URL
 */
function assetUrl(?string $path): string {
    if (empty($path)) {
        return BASE_URL . 'assets/purifier-render.webp';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return BASE_URL . ltrim($path, '/');
}

/**
 * Get or create unique guest visitor/session ID for Cart & Wishlist
 */
function getVisitorSessionId(): string {
    if (isset($_COOKIE['wp_session_id']) && preg_match('/^[a-f0-9]{32,64}$/i', $_COOKIE['wp_session_id'])) {
        $sessionId = $_COOKIE['wp_session_id'];
    } elseif (isset($_SESSION['wp_session_id']) && preg_match('/^[a-f0-9]{32,64}$/i', $_SESSION['wp_session_id'])) {
        $sessionId = $_SESSION['wp_session_id'];
    } else {
        $sessionId = bin2hex(random_bytes(16));
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('wp_session_id', $sessionId, [
            'expires' => time() + (86400 * 90), // 90 days
            'path' => '/',
            'secure' => $isSecure,
            'httponly' => false, // Accessible to client JS if needed
            'samesite' => 'Lax'
        ]);
    }
    $_SESSION['wp_session_id'] = $sessionId;
    return $sessionId;
}

/**
 * Upload image securely to uploads subfolder
 */
function uploadImage(array $file, string $subfolder = 'products'): string|false {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Max 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return false;
    }

    // Allowed MIME types
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, $allowedTypes)) {
        return false;
    }

    $ext = $allowedTypes[$mime];
    $targetDir = rtrim(UPLOAD_DIR . trim($subfolder, '/'), '/') . '/';

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        return false;
    }

    $filename = sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(6)), $ext);
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . trim($subfolder, '/') . '/' . $filename;
    }

    return false;
}

/**
 * Safely delete an uploaded file
 */
function deleteUploadedImage(?string $filepath): bool {
    if (empty($filepath)) {
        return false;
    }

    // Only allow deletion within uploads directory
    if (!str_starts_with($filepath, 'uploads/')) {
        return false;
    }

    $fullPath = ROOT_PATH . '/' . ltrim($filepath, '/');
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}

/**
 * Retrieve a setting from the settings table
 */
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM `settings` WHERE setting_key = :k LIMIT 1');
        $stmt->execute([':k' => $key]);
        $val = $stmt->fetchColumn();
        $res = ($val !== false && $val !== null) ? (string)$val : $default;
        $cache[$key] = $res;
        return $res;
    } catch (PDOException) {
        return $default;
    }
}

/**
 * Update or set a store setting
 */
function setSetting(PDO $pdo, string $key, string $value): bool {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO `settings` (`setting_key`, `setting_value`) 
            VALUES (:k, :v)
            ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)
        ');
        return $stmt->execute([':k' => $key, ':v' => $value]);
    } catch (PDOException) {
        return false;
    }
}

/**
 * Construct sanitized WhatsApp chat URL
 */
function buildWhatsAppUrl(string $phone, string $message): string {
    $cleanPhone = preg_replace('/[^\d]/', '', $phone);
    if (!str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }
    return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
}

/**
 * Standard JSON Response Sender
 */
function jsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Flash notification messaging system
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message,
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) {
        return '';
    }

    $type = e($flash['type']);
    $msg = e($flash['message']);

    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show mb-4" role="alert">
        {$msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
HTML;
}

/**
 * Check if Admin user is authenticated
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in'], $_SESSION['admin_id']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Fetch Current Logged-in Admin Info
 */
function currentAdmin(PDO $pdo): ?array {
    if (!isAdminLoggedIn()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, role, last_login FROM `admin_users` WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

/**
 * Fetch Cart Summary for a given session ID
 */
function getCartSummary(PDO $pdo, string $sessionId): array {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.id as product_id, p.slug, p.name, p.brand, p.price, p.mrp, p.image, p.technology, p.capacity, p.stock
        FROM `cart` c
        JOIN `products` p ON c.product_id = p.id
        WHERE c.session_id = :session_id AND p.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':session_id' => $sessionId]);
    $items = $stmt->fetchAll();

    $subtotal = 0.0;
    $totalQty = 0;
    foreach ($items as &$item) {
        $item['image_url'] = assetUrl($item['image']);
        $item['subtotal'] = (float)$item['price'] * (int)$item['quantity'];
        $item['formatted_price'] = formatPrice($item['price']);
        $item['formatted_subtotal'] = formatPrice($item['subtotal']);
        $subtotal += $item['subtotal'];
        $totalQty += (int)$item['quantity'];
    }
    unset($item);

    return [
        'items' => $items,
        'count' => $totalQty,
        'subtotal' => $subtotal,
        'formatted_subtotal' => formatPrice($subtotal),
    ];
}

/**
 * Fetch Wishlist Summary for a given session ID
 */
function getWishlistSummary(PDO $pdo, string $sessionId): array {
    $stmt = $pdo->prepare("
        SELECT w.id as wishlist_id, p.id as product_id, p.slug, p.name, p.brand, p.price, p.mrp, p.image, p.technology, p.capacity, p.stock
        FROM `wishlist` w
        JOIN `products` p ON w.product_id = p.id
        WHERE w.session_id = :session_id AND p.status = 'active'
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([':session_id' => $sessionId]);
    $items = $stmt->fetchAll();
    $ids = array_map(fn($it) => (int)$it['product_id'], $items);

    foreach ($items as &$item) {
        $item['image_url'] = assetUrl($item['image']);
        $item['formatted_price'] = formatPrice($item['price']);
    }
    unset($item);

    return [
        'items' => $items,
        'ids' => $ids,
        'count' => count($items),
    ];
}

