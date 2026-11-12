<?php
declare(strict_types=1);

/**
 * Waypure Checkout / Order Placement API
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDb();
    $sessionId = getVisitorSessionId();

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $name = trim((string)($input['name'] ?? ''));
    $phone = trim((string)($input['phone'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $address = trim((string)($input['address'] ?? ''));
    $city = trim((string)($input['city'] ?? ''));
    $pincode = trim((string)($input['pincode'] ?? ''));
    $paymentMethod = trim((string)($input['payment_method'] ?? 'cod'));
    $notes = trim((string)($input['notes'] ?? ''));

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
    $orderCode = 'WP-ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

    $adminWhatsApp = getSetting($pdo, 'admin_whatsapp', '919895301810');
    $storeName = getSetting($pdo, 'store_name', 'Waypure');

    // Build WhatsApp Order Message
    $itemsText = "";
    foreach ($cart['items'] as $it) {
        $itemsText .= "• {$it['brand']} {$it['name']} (Qty: {$it['quantity']}) — ₹" . number_format($it['subtotal']) . "\n";
    }

    $waMessage = "🌊 *{$storeName} — NEW ORDER CONFIRMATION*\n"
               . "----------------------------------------\n"
               . "*Order ID:* {$orderCode}\n\n"
               . "*Items Requested:*\n" . $itemsText . "\n"
               . "*Total Amount:* ₹" . number_format($cart['subtotal']) . " (Free Delivery & Installation)\n"
               . "*Payment Method:* " . strtoupper($paymentMethod) . "\n\n"
               . "*Delivery Address:*\n"
               . "• Name: {$name}\n"
               . "• Phone: {$phone}\n"
               . "• Address: {$address}, {$city}" . ($pincode ? " - {$pincode}" : "") . "\n";
    if ($notes) {
        $waMessage .= "• Note: {$notes}\n";
    }
    $waMessage .= "\nPlease confirm order dispatch and technician arrival.";

    $waUrl = buildWhatsAppUrl($adminWhatsApp, $waMessage);

    // Save into enquiries table for unified admin management
    $stmtEnq = $pdo->prepare("
        INSERT INTO `enquiries` 
        (`enquiry_code`, `session_id`, `type`, `customer_name`, `customer_phone`, `customer_email`, `city`, `address`, `pincode`, `product_name`, `quantity`, `total_amount`, `payment_method`, `message`, `items_json`, `status`, `whatsapp_url`)
        VALUES
        (:code, :session_id, 'whatsapp_order', :name, :phone, :email, :city, :address, :pincode, :pname, :qty, :total, :pay, :notes, :items, 'new', :wa_url)
    ");
    $stmtEnq->execute([
        ':code' => $orderCode,
        ':session_id' => $sessionId,
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email ?: null,
        ':city' => $city,
        ':address' => $address,
        ':pincode' => $pincode ?: null,
        ':pname' => sprintf('%d purifiers in cart', $cart['count']),
        ':qty' => $cart['count'],
        ':total' => $cart['subtotal'],
        ':pay' => $paymentMethod,
        ':notes' => $notes ?: null,
        ':items' => json_encode($cart['items'], JSON_UNESCAPED_UNICODE),
        ':wa_url' => $waUrl
    ]);

    // Also save into orders table if table exists
    try {
        $stmtOrd = $pdo->prepare("
            INSERT INTO `orders` 
            (`order_code`, `session_id`, `customer_name`, `customer_phone`, `customer_email`, `address`, `city`, `pincode`, `payment_method`, `total_amount`, `items_json`, `notes`)
            VALUES
            (:code, :session_id, :name, :phone, :email, :address, :city, :pincode, :pay, :total, :items, :notes)
        ");
        $stmtOrd->execute([
            ':code' => $orderCode,
            ':session_id' => $sessionId,
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email ?: null,
            ':address' => $address,
            ':city' => $city,
            ':pincode' => $pincode ?: null,
            ':pay' => $paymentMethod,
            ':total' => $cart['subtotal'],
            ':items' => json_encode($cart['items'], JSON_UNESCAPED_UNICODE),
            ':notes' => $notes ?: null
        ]);
    } catch (PDOException) {
        // Table optional
    }

    // Clear cart after order is successfully placed
    $clear = $pdo->prepare("DELETE FROM `cart` WHERE `session_id` = :s");
    $clear->execute([':s' => $sessionId]);

    jsonResponse([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_code' => $orderCode,
        'total' => $cart['subtotal'],
        'formatted_total' => formatPrice($cart['subtotal']),
        'whatsapp_url' => $waUrl
    ]);

} catch (Throwable $e) {
    error_log('[Waypure Checkout API Error] ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Error placing order. Please try again.'
    ], 500);
}
