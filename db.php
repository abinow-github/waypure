<?php
/**
 * Waypure E-Commerce Database Connection & Initialization
 * Automatically sets up MySQL database 'waypure_db' and required tables.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'waypure_db');

function getDb(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // First connect to MySQL server to ensure database exists
        $dsnWithoutDb = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        $tempPdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        // Now connect to the database
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        initTables($pdo);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
}

function initTables(PDO $pdo): void {
    // 1. Products Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(120) NOT NULL UNIQUE,
            `brand` VARCHAR(60) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `category` VARCHAR(100) NOT NULL DEFAULT 'Water purifiers',
            `capacity` VARCHAR(60) NULL,
            `technology` VARCHAR(255) NOT NULL,
            `mrp` DECIMAL(10,2) NULL,
            `price` DECIMAL(10,2) NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            `warranty` VARCHAR(100) NULL DEFAULT '36 months',
            `dimensions` VARCHAR(100) NULL,
            `features` LONGTEXT NULL,
            `description` TEXT NULL,
            `stock` INT NOT NULL DEFAULT 25,
            `rating` DECIMAL(2,1) NOT NULL DEFAULT 4.8,
            `reviews_count` INT NOT NULL DEFAULT 28,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Cart Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `cart` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(128) NOT NULL,
            `product_id` INT NOT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `session_product` (`session_id`, `product_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Wishlist Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `wishlist` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(128) NOT NULL,
            `product_id` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `session_wishlist` (`session_id`, `product_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Orders Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `orders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_code` VARCHAR(50) NOT NULL UNIQUE,
            `session_id` VARCHAR(128) NOT NULL,
            `customer_name` VARCHAR(150) NOT NULL,
            `customer_phone` VARCHAR(30) NOT NULL,
            `customer_email` VARCHAR(150) NULL,
            `address` TEXT NOT NULL,
            `city` VARCHAR(100) NOT NULL,
            `pincode` VARCHAR(20) NOT NULL,
            `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cod',
            `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending',
            `total_amount` DECIMAL(10,2) NOT NULL,
            `items_json` LONGTEXT NOT NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Seed products if table is empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    if ($count === 0) {
        seedProducts($pdo);
    }
}

function seedProducts(PDO $pdo): void {
    $jsonFile = __DIR__ . '/data/products.json';
    if (!file_exists($jsonFile)) {
        return;
    }

    $raw = file_get_contents($jsonFile);
    $items = json_decode($raw, true);
    if (!is_array($items)) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO `products` 
        (`slug`, `brand`, `name`, `category`, `capacity`, `technology`, `mrp`, `price`, `image`, `warranty`, `dimensions`, `features`, `description`, `stock`, `rating`, `reviews_count`, `is_featured`)
        VALUES 
        (:slug, :brand, :name, :category, :capacity, :technology, :mrp, :price, :image, :warranty, :dimensions, :features, :description, :stock, :rating, :reviews_count, :is_featured)
    ");

    foreach ($items as $idx => $item) {
        $slug = $item['slug'] ?? ($item['id'] ?? 'product-' . ($idx + 1));
        $brand = $item['brand'] ?? 'Waypure';
        $name = $item['name'] ?? 'Purifier';
        $category = $item['category'] ?? 'Water purifiers';
        $capacity = $item['capacity'] ?? null;
        $technology = $item['technology'] ?? 'RO + UV';
        $mrp = !empty($item['mrp']) ? (float)$item['mrp'] : null;
        $price = !empty($item['price']) ? (float)$item['price'] : ($mrp ?? 14999);
        $image = !empty($item['image']) ? $item['image'] : 'assets/purifier-render.webp';
        $warranty = $item['warranty'] ?? '36 months';
        $dimensions = $item['dimensions'] ?? null;
        $features = isset($item['features']) ? json_encode($item['features'], JSON_UNESCAPED_UNICODE) : '[]';
        $description = $item['description'] ?? "Premium {$brand} {$name} water purifier designed for safe, healthy drinking water.";
        $stock = $item['stock'] ?? 25;
        $rating = $item['rating'] ?? 4.8;
        $reviews_count = $item['reviews_count'] ?? 30;
        $is_featured = ($idx < 8) ? 1 : 0;

        $stmt->execute([
            ':slug' => $slug,
            ':brand' => $brand,
            ':name' => $name,
            ':category' => $category,
            ':capacity' => $capacity,
            ':technology' => $technology,
            ':mrp' => $mrp,
            ':price' => $price,
            ':image' => $image,
            ':warranty' => $warranty,
            ':dimensions' => $dimensions,
            ':features' => $features,
            ':description' => $description,
            ':stock' => $stock,
            ':rating' => $rating,
            ':reviews_count' => $reviews_count,
            ':is_featured' => $is_featured
        ]);
    }
}

/**
 * Get or create unique visitor/session ID for guest cart & wishlist
 */
function getSessionId(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_COOKIE['wp_session_id']) && preg_match('/^[a-f0-9]{32,64}$/i', $_COOKIE['wp_session_id'])) {
        $sessionId = $_COOKIE['wp_session_id'];
    } elseif (isset($_SESSION['wp_session_id']) && preg_match('/^[a-f0-9]{32,64}$/i', $_SESSION['wp_session_id'])) {
        $sessionId = $_SESSION['wp_session_id'];
    } else {
        $sessionId = bin2hex(random_bytes(16));
        setcookie('wp_session_id', $sessionId, [
            'expires' => time() + (86400 * 60), // 60 days
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }
    $_SESSION['wp_session_id'] = $sessionId;
    return $sessionId;
}

/**
 * Send JSON response
 */
function jsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Helper to fetch cart summary
 */
function getCartSummary(PDO $pdo, string $sessionId): array {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.id as product_id, p.slug, p.name, p.brand, p.price, p.mrp, p.image, p.technology, p.capacity
        FROM `cart` c
        JOIN `products` p ON c.product_id = p.id
        WHERE c.session_id = :session_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':session_id' => $sessionId]);
    $items = $stmt->fetchAll();

    $subtotal = 0;
    $totalQty = 0;
    foreach ($items as &$item) {
        $item['subtotal'] = (float)$item['price'] * (int)$item['quantity'];
        $subtotal += $item['subtotal'];
        $totalQty += (int)$item['quantity'];
    }

    return [
        'items' => $items,
        'count' => $totalQty,
        'subtotal' => $subtotal,
        'formatted_subtotal' => '₹' . number_format($subtotal)
    ];
}

/**
 * Helper to fetch wishlist product IDs
 */
function getWishlistSummary(PDO $pdo, string $sessionId): array {
    $stmt = $pdo->prepare("
        SELECT w.id as wishlist_id, p.id as product_id, p.slug, p.name, p.brand, p.price, p.mrp, p.image, p.technology, p.capacity
        FROM `wishlist` w
        JOIN `products` p ON w.product_id = p.id
        WHERE w.session_id = :session_id
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([':session_id' => $sessionId]);
    $items = $stmt->fetchAll();
    $ids = array_map(fn($item) => (int)$item['product_id'], $items);

    return [
        'items' => $items,
        'ids' => $ids,
        'count' => count($items)
    ];
}
