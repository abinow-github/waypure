<?php
declare(strict_types=1);

/**
 * Waypure Wishlist AJAX API Endpoint
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    $sessionId = getVisitorSessionId();

    $action = $_GET['action'] ?? $_POST['action'] ?? 'get';
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    match ($action) {
        'get' => (function() use ($pdo, $sessionId) {
            $summary = getWishlistSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'wishlist' => $summary
            ]);
        })(),

        'toggle' => (function() use ($pdo, $sessionId, $input) {
            $productId = (int)($input['product_id'] ?? 0);
            if ($productId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Invalid product identifier.'], 400);
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
        })(),

        'remove' => (function() use ($pdo, $sessionId, $input) {
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
        })(),

        'move_to_cart' => (function() use ($pdo, $sessionId, $input) {
            $productId = (int)($input['product_id'] ?? 0);
            if ($productId > 0) {
                $cartStmt = $pdo->prepare("
                    INSERT INTO `cart` (`session_id`, `product_id`, `quantity`)
                    VALUES (:s, :p, 1)
                    ON DUPLICATE KEY UPDATE `quantity` = `quantity` + 1
                ");
                $cartStmt->execute([':s' => $sessionId, ':p' => $productId]);

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
        })(),

        default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 400)
    };

} catch (Throwable $e) {
    error_log('[Waypure Wishlist API Error] ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error processing wishlist.'], 500);
}
