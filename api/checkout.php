<?php
/**
 * Waypure Checkout / Order Placement API
 */
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    $sessionId = getSessionId();

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $address = trim($input['address'] ?? '');
    $city = trim($input['city'] ?? '');
    $pincode = trim($input['pincode'] ?? '');
    $paymentMethod = trim($input['payment_method'] ?? 'cod');
    $notes = trim($input['notes'] ?? '');

    if (empty($name) || empty($phone) || empty($address) || empty($city)) {
        jsonResponse([
            'success' => false,
            'message' => 'Please fill in Name, Phone, Address, and City.'
        ], 400);
    }

    // Get cart items
    $cart = getCartSummary($pdo, $sessionId);
    if (empty($cart['items'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Your cart is empty. Please add products before checking out.'
        ], 400);
    }

    // Generate unique order code
    $orderCode = 'WP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // Save order
    $stmt = $pdo->prepare("
        INSERT INTO `orders` 
        (`order_code`, `session_id`, `customer_name`, `customer_phone`, `customer_email`, `address`, `city`, `pincode`, `payment_method`, `total_amount`, `items_json`, `notes`)
        VALUES
        (:code, :session_id, :name, :phone, :email, :address, :city, :pincode, :pay, :total, :items, :notes)
    ");

    $stmt->execute([
        ':code' => $orderCode,
        ':session_id' => $sessionId,
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':address' => $address,
        ':city' => $city,
        ':pincode' => $pincode,
        ':pay' => $paymentMethod,
        ':total' => $cart['subtotal'],
        ':items' => json_encode($cart['items'], JSON_UNESCAPED_UNICODE),
        ':notes' => $notes
    ]);

    // Build WhatsApp Order Message
    $itemsText = "";
    foreach ($cart['items'] as $it) {
        $itemsText .= "• {$it['brand']} {$it['name']} (Qty: {$it['quantity']}) — ₹" . number_format($it['subtotal']) . "\n";
    }

    $waMessage = "WAYPURE — NEW ORDER CONFIRMATION\n"
               . "Order ID: {$orderCode}\n\n"
               . "Items:\n" . $itemsText . "\n"
               . "Total Amount: ₹" . number_format($cart['subtotal']) . "\n"
               . "Payment: " . strtoupper($paymentMethod) . "\n\n"
               . "Delivery Address:\n"
               . "Name: {$name}\n"
               . "Phone: {$phone}\n"
               . "Address: {$address}, {$city} - {$pincode}\n";
    if ($notes) {
        $waMessage .= "Note: {$notes}\n";
    }
    $waMessage .= "\nPlease confirm my order and share dispatch details.";

    $waUrl = "https://wa.me/919895301810?text=" . urlencode($waMessage);

    // Clear cart after order is successfully placed
    $clear = $pdo->prepare("DELETE FROM `cart` WHERE `session_id` = :s");
    $clear->execute([':s' => $sessionId]);

    jsonResponse([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_code' => $orderCode,
        'total' => $cart['subtotal'],
        'formatted_total' => '₹' . number_format($cart['subtotal']),
        'whatsapp_url' => $waUrl
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
