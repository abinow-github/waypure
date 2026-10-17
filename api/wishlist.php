<?php
/**
 * Waypure Wishlist API
 * Actions: get, toggle, remove, move_to_cart
 */
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    $sessionId = getSessionId();

    $action = $_GET['action'] ?? $_POST['action'] ?? 'get';
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'get') {
        $summary = getWishlistSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'wishlist' => $summary
        ]);
    }

    if ($action === 'toggle') {
        $productId = (int)($input['product_id'] ?? 0);
        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid product ID'], 400);
        }

        // Check if already in wishlist
        $check = $pdo->prepare("SELECT id FROM `wishlist` WHERE `session_id` = :s AND `product_id` = :p");
        $check->execute([':s' => $sessionId, ':p' => $productId]);
        $existing = $check->fetch();

        if ($existing) {
            $del = $pdo->prepare("DELETE FROM `wishlist` WHERE `session_id` = :s AND `product_id` = :p");
            $del->execute([':s' => $sessionId, ':p' => $productId]);
            $added = false;
            $msg = 'Removed from wishlist';
        } else {
            $ins = $pdo->prepare("INSERT INTO `wishlist` (`session_id`, `product_id`) VALUES (:s, :p)");
            $ins->execute([':s' => $sessionId, ':p' => $productId]);
            $added = true;
            $msg = 'Added to wishlist ❤️';
        }

        $summary = getWishlistSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'added' => $added,
            'message' => $msg,
            'wishlist' => $summary
        ]);
    }

    if ($action === 'remove') {
        $productId = (int)($input['product_id'] ?? 0);
        if ($productId > 0) {
            $del = $pdo->prepare("DELETE FROM `wishlist` WHERE `session_id` = :s AND `product_id` = :p");
            $del->execute([':s' => $sessionId, ':p' => $productId]);
        }

        $summary = getWishlistSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'message' => 'Removed from wishlist',
            'wishlist' => $summary
        ]);
    }

    if ($action === 'move_to_cart') {
        $productId = (int)($input['product_id'] ?? 0);
        if ($productId > 0) {
            // Add to cart
            $cartStmt = $pdo->prepare("
                INSERT INTO `cart` (`session_id`, `product_id`, `quantity`)
                VALUES (:s, :p, 1)
                ON DUPLICATE KEY UPDATE `quantity` = `quantity` + 1
            ");
            $cartStmt->execute([':s' => $sessionId, ':p' => $productId]);

            // Remove from wishlist
            $del = $pdo->prepare("DELETE FROM `wishlist` WHERE `session_id` = :s AND `product_id` = :p");
            $del->execute([':s' => $sessionId, ':p' => $productId]);
        }

        $wSummary = getWishlistSummary($pdo, $sessionId);
        $cSummary = getCartSummary($pdo, $sessionId);

        jsonResponse([
            'success' => true,
            'message' => 'Moved to cart 🛒',
            'wishlist' => $wSummary,
            'cart' => $cSummary
        ]);
    }

    jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
