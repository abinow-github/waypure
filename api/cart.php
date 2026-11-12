<?php
declare(strict_types=1);

/**
 * Waypure Cart AJAX API Endpoint
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

    // CSRF validation for mutating POST requests if token supplied
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? null);
        if ($clientToken !== null && !verifyCsrfToken((string)$clientToken)) {
            jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
        }
    }

    match ($action) {
        'add' => (function() use ($pdo, $sessionId, $input) {
            $productId = (int)($input['product_id'] ?? 0);
            $quantity = max(1, (int)($input['quantity'] ?? 1));

            if ($productId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Invalid product identifier.'], 400);
            }

            // Check if product exists and is active
            $pStmt = $pdo->prepare("SELECT id, name, brand, stock, price FROM `products` WHERE id = :id AND status = 'active' LIMIT 1");
            $pStmt->execute([':id' => $productId]);
            $product = $pStmt->fetch();

            if (!$product) {
                jsonResponse(['success' => false, 'message' => 'Product not found or unavailable.'], 404);
            }

            // Upsert into cart
            $stmt = $pdo->prepare("
                INSERT INTO `cart` (`session_id`, `product_id`, `quantity`)
                VALUES (:s, :p, :q)
                ON DUPLICATE KEY UPDATE `quantity` = `quantity` + VALUES(`quantity`), `updated_at` = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                ':s' => $sessionId,
                ':p' => $productId,
                ':q' => $quantity
            ]);

            $cart = getCartSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'message' => sprintf('Added %s %s to your cart! 🛒', e($product['brand']), e($product['name'])),
                'cart' => $cart
            ]);
        })(),

        'update', 'update_qty' => (function() use ($pdo, $sessionId, $input) {
            $productId = (int)($input['product_id'] ?? 0);
            $quantity = (int)($input['quantity'] ?? 1);

            if ($productId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Invalid product identifier.'], 400);
            }

            if ($quantity <= 0) {
                $del = $pdo->prepare("DELETE FROM `cart` WHERE session_id = :s AND product_id = :p");
                $del->execute([':s' => $sessionId, ':p' => $productId]);
            } else {
                $upd = $pdo->prepare("UPDATE `cart` SET quantity = :q WHERE session_id = :s AND product_id = :p");
                $upd->execute([':q' => $quantity, ':s' => $sessionId, ':p' => $productId]);
            }

            $cart = getCartSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'message' => 'Cart updated.',
                'cart' => $cart
            ]);
        })(),

        'remove' => (function() use ($pdo, $sessionId, $input) {
            $productId = (int)($input['product_id'] ?? 0);
            if ($productId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Invalid product identifier.'], 400);
            }

            $del = $pdo->prepare("DELETE FROM `cart` WHERE session_id = :s AND product_id = :p");
            $del->execute([':s' => $sessionId, ':p' => $productId]);

            $cart = getCartSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart' => $cart
            ]);
        })(),

        'clear' => (function() use ($pdo, $sessionId) {
            $del = $pdo->prepare("DELETE FROM `cart` WHERE session_id = :s");
            $del->execute([':s' => $sessionId]);

            $cart = getCartSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart' => $cart
            ]);
        })(),

        'get', 'get_cart' => (function() use ($pdo, $sessionId) {
            $cart = getCartSummary($pdo, $sessionId);
            jsonResponse([
                'success' => true,
                'cart' => $cart
            ]);
        })(),

        default => jsonResponse(['success' => false, 'message' => 'Invalid cart action specified.'], 400)
    };

} catch (Throwable $e) {
    error_log('[Waypure Cart API Error] ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'An error occurred while managing your cart.'
    ], 500);
}
