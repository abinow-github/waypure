<?php
/**
 * Waypure Products API
 */
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    
    $brand = trim($_GET['brand'] ?? 'All');
    $category = trim($_GET['category'] ?? 'All');
    $search = trim($_GET['q'] ?? '');
    $limit = (int)($_GET['limit'] ?? 100);

    $sql = "SELECT * FROM `products` WHERE 1=1";
    $params = [];

    if ($brand !== '' && $brand !== 'All') {
        $sql .= " AND `brand` = :brand";
        $params[':brand'] = $brand;
    }

    if ($category !== '' && $category !== 'All') {
        $sql .= " AND `category` = :category";
        $params[':category'] = $category;
    }

    if ($search !== '') {
        $sql .= " AND (`name` LIKE :search OR `technology` LIKE :search OR `brand` LIKE :search OR `description` LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY `id` ASC LIMIT " . max(1, min(200, $limit));

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
        $p['formatted_price'] = '₹' . number_format($p['price']);
        $p['formatted_mrp'] = $p['mrp'] ? '₹' . number_format($p['mrp']) : null;
    }

    jsonResponse([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
