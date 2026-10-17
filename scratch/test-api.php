<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();
$sessionId = 'test_session_12345';

// Test adding product 1 to cart
$stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 2) ON DUPLICATE KEY UPDATE quantity = quantity + 2");
$stmt->execute([$sessionId, 1]);

// Test adding product 2 to wishlist
$stmt = $pdo->prepare("INSERT INTO wishlist (session_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id");
$stmt->execute([$sessionId, 2]);

$cart = getCartSummary($pdo, $sessionId);
$wishlist = getWishlistSummary($pdo, $sessionId);

echo "Cart test:\n";
print_r($cart);
echo "\nWishlist test:\n";
print_r($wishlist);

// Cleanup test session
$pdo->prepare("DELETE FROM cart WHERE session_id = ?")->execute([$sessionId]);
$pdo->prepare("DELETE FROM wishlist WHERE session_id = ?")->execute([$sessionId]);
echo "\nCleaned up test records successfully.\n";
