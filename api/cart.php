<?php
/**
 * Waypure Cart API
 * Actions: get, add, update, remove, clear
 */
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    $sessionId = getSessionId();

    $action = $_GET['action'] ?? $_POST['action'] ?? 'get';
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'get') {
        $summary = getCartSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'cart' => $summary
        ]);
    }

    if ($action === 'add') {
        $productId = (int)($input['product_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));

        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid product ID'], 400);
        }

        // Verify product exists
        $pStmt = $pdo->prepare("SELECT id, name, price, stock FROM `products` WHERE id = :id");
        $pStmt->execute([':id' => $productId]);
        $prod = $pStmt->fetch();
        if (!$prod) {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Insert or update on duplicate key
        $stmt = $pdo->prepare("
            INSERT INTO `cart` (`session_id`, `product_id`, `quantity`)
            VALUES (:session_id, :product_id, :quantity)
            ON DUPLICATE KEY UPDATE `quantity` = `quantity` + :quantity_inc
        ");
        $stmt->execute([
            ':session_id' => $sessionId,
            ':product_id' => $productId,
            ':quantity' => $qty,
            ':quantity_inc' => $qty
        ]);

        $summary = getCartSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'message' => "{$prod['name']} added to cart",
            'cart' => $summary
        ]);
    }

    if ($action === 'update') {
        $productId = (int)($input['product_id'] ?? 0);
        $qty = (int)($input['quantity'] ?? 1);

        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid product ID'], 400);
        }

        if ($qty <= 0) {
            $stmt = $pdo->prepare("DELETE FROM `cart` WHERE `session_id` = :session_id AND `product_id` = :product_id");
            $stmt->execute([':session_id' => $sessionId, ':product_id' => $productId]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE `cart` SET `quantity` = :quantity 
                WHERE `session_id` = :session_id AND `product_id` = :product_id
            ");
            $stmt->execute([
                ':quantity' => $qty,
                ':session_id' => $sessionId,
                ':product_id' => $productId
            ]);
        }

        $summary = getCartSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'cart' => $summary
        ]);
    }

    if ($action === 'remove') {
        $productId = (int)($input['product_id'] ?? 0);
        if ($productId > 0) {
            $stmt = $pdo->prepare("DELETE FROM `cart` WHERE `session_id` = :session_id AND `product_id` = :product_id");
            $stmt->execute([':session_id' => $sessionId, ':product_id' => $productId]);
        }

        $summary = getCartSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $summary
        ]);
    }

    if ($action === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM `cart` WHERE `session_id` = :session_id");
        $stmt->execute([':session_id' => $sessionId]);

        $summary = getCartSummary($pdo, $sessionId);
        jsonResponse([
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $summary
        ]);
    }

    jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
