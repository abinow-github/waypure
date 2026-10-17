<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();
$orders = $pdo->query("SELECT id, order_code, customer_name, customer_phone, total_amount, payment_method, created_at FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
echo json_encode($orders, JSON_PRETTY_PRINT);
