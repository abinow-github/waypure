<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();
$count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
echo "Products count: " . $count . "\n";
$first = $pdo->query("SELECT id, slug, brand, name, price, image FROM products LIMIT 3")->fetchAll();
echo json_encode($first, JSON_PRETTY_PRINT);
