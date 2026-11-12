<?php
declare(strict_types=1);

/**
 * Waypure Customer Enquiry & WhatsApp Order API Endpoint
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

    // CSRF verification if provided
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? null);
    if ($csrfToken !== null && !verifyCsrfToken((string)$csrfToken)) {
        jsonResponse([
            'success' => false,
            'message' => 'Security token expired or invalid. Please refresh the page.'
        ], 403);
    }

    $type = trim((string)($input['type'] ?? 'product_enquiry'));
    if (!in_array($type, ['product_enquiry', 'whatsapp_order', 'general'], true)) {
        $type = 'product_enquiry';
    }

    $name = trim((string)($input['name'] ?? $input['customer_name'] ?? ''));
    $phone = trim((string)($input['phone'] ?? $input['customer_phone'] ?? ''));
    $email = trim((string)($input['email'] ?? $input['customer_email'] ?? ''));
    $city = trim((string)($input['city'] ?? ''));
    $address = trim((string)($input['address'] ?? ''));
    $pincode = trim((string)($input['pincode'] ?? ''));
    $notes = trim((string)($input['message'] ?? $input['notes'] ?? ''));
    $paymentMethod = trim((string)($input['payment_method'] ?? 'cod'));

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Please enter your full name.'], 400);
    }
    if (empty($phone) || strlen(preg_replace('/\D/', '', $phone)) < 10) {
        jsonResponse(['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.'], 400);
    }
    if (empty($city) && empty($address)) {
        jsonResponse(['success' => false, 'message' => 'Please provide your city or delivery location.'], 400);
    }

    $adminWhatsApp = getSetting($pdo, 'admin_whatsapp', '919895301810');
    $storeName = getSetting($pdo, 'store_name', 'Waypure');

    $enquiryCode = sprintf(
        'WP-%s-%s-%s',
        ($type === 'whatsapp_order' ? 'ORD' : 'ENQ'),
        date('Ymd'),
        strtoupper(bin2hex(random_bytes(2)))
    );

    $productId = !empty($input['product_id']) ? (int)$input['product_id'] : null;
    $productName = trim((string)($input['product'] ?? $input['product_name'] ?? ''));
    $quantity = max(1, (int)($input['quantity'] ?? 1));
    $totalAmount = 0.0;
    $itemsJson = null;

    if ($type === 'whatsapp_order') {
        // Fetch cart items
        $cart = getCartSummary($pdo, $sessionId);
        if (empty($cart['items'])) {
            // Check if direct single product order
            if ($productId !== null && $productId > 0) {
                $pStmt = $pdo->prepare("SELECT id, brand, name, price, technology, capacity FROM `products` WHERE id = :id LIMIT 1");
                $pStmt->execute([':id' => $productId]);
                $prod = $pStmt->fetch();
                if ($prod) {
                    $itemPrice = (float)$prod['price'];
                    $totalAmount = $itemPrice * $quantity;
                    $itemsJson = json_encode([[
                        'product_id' => $prod['id'],
                        'brand' => $prod['brand'],
                        'name' => $prod['name'],
                        'quantity' => $quantity,
                        'price' => $itemPrice,
                        'subtotal' => $totalAmount,
                        'technology' => $prod['technology'],
                        'capacity' => $prod['capacity']
                    ]], JSON_UNESCAPED_UNICODE);
                    $productName = $prod['brand'] . ' ' . $prod['name'];
                }
            } else {
                jsonResponse(['success' => false, 'message' => 'Your cart is empty. Please add a product to order.'], 400);
            }
        } else {
            $totalAmount = (float)$cart['subtotal'];
            $itemsJson = json_encode($cart['items'], JSON_UNESCAPED_UNICODE);
            $productName = sprintf('%d items in order', $cart['count']);
        }

        // Build WhatsApp message for Order
        $itemsText = "";
        $orderItems = json_decode((string)$itemsJson, true) ?: [];
        foreach ($orderItems as $it) {
            $itemsText .= sprintf("• %s %s (Qty: %d) — ₹%s\n", $it['brand'] ?? '', $it['name'] ?? '', $it['quantity'] ?? 1, number_format((float)($it['subtotal'] ?? $it['price'] ?? 0)));
        }

        $waMessage = "🌊 *{$storeName} — NEW ORDER CONFIRMATION*\n"
                   . "----------------------------------------\n"
                   . "*Order ID:* {$enquiryCode}\n\n"
                   . "*Items Requested:*\n" . $itemsText . "\n"
                   . "*Total Amount:* ₹" . number_format($totalAmount) . " (Free Delivery & Installation)\n"
                   . "*Payment Method:* " . strtoupper($paymentMethod) . "\n\n"
                   . "*Delivery Details:*\n"
                   . "• Name: {$name}\n"
                   . "• Phone: {$phone}\n"
                   . "• Address: " . ($address ? $address . ', ' : '') . $city . ($pincode ? ' - ' . $pincode : '') . "\n";
        
        if (!empty($notes)) {
            $waMessage .= "• Order Notes: {$notes}\n";
        }
        $waMessage .= "\nPlease confirm stock availability and installation schedule.";

        // Clear cart after order placed
        $clearStmt = $pdo->prepare("DELETE FROM `cart` WHERE session_id = :s");
        $clearStmt->execute([':s' => $sessionId]);

    } else {
        // Direct product inquiry or general inquiry
        if (empty($productName)) {
            $productName = 'General Purifier Inquiry';
        }

        if ($productId !== null && $productId > 0) {
            $pStmt = $pdo->prepare("SELECT price FROM `products` WHERE id = :id LIMIT 1");
            $pStmt->execute([':id' => $productId]);
            $price = $pStmt->fetchColumn();
            if ($price !== false) {
                $totalAmount = (float)$price;
            }
        }

        $waMessage = "🌊 *{$storeName} — PRODUCT INQUIRY*\n"
                   . "----------------------------------------\n"
                   . "*Inquiry Ref:* {$enquiryCode}\n"
                   . "*Product/Service:* {$productName}\n\n"
                   . "*Customer Details:*\n"
                   . "• Name: {$name}\n"
                   . "• Phone: {$phone}\n"
                   . "• City/Location: {$city}\n";
        if (!empty($email)) {
            $waMessage .= "• Email: {$email}\n";
        }
        if (!empty($notes)) {
            $waMessage .= "• Message/Questions: {$notes}\n";
        }
        $waMessage .= "\nPlease share pricing, TDS suitability and installation details.";
    }

    $waUrl = buildWhatsAppUrl($adminWhatsApp, $waMessage);

    // Insert enquiry into database
    $stmt = $pdo->prepare("
        INSERT INTO `enquiries` 
        (`enquiry_code`, `session_id`, `type`, `customer_name`, `customer_phone`, `customer_email`, `city`, `address`, `pincode`, `product_id`, `product_name`, `quantity`, `total_amount`, `payment_method`, `message`, `items_json`, `whatsapp_url`, `status`)
        VALUES
        (:code, :session_id, :type, :name, :phone, :email, :city, :address, :pincode, :product_id, :product_name, :qty, :total, :pay, :message, :items_json, :wa_url, 'new')
    ");

    $stmt->execute([
        ':code' => $enquiryCode,
        ':session_id' => $sessionId,
        ':type' => $type,
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email ?: null,
        ':city' => $city ?: ($address ?: 'Kerala'),
        ':address' => $address ?: null,
        ':pincode' => $pincode ?: null,
        ':product_id' => $productId,
        ':product_name' => $productName,
        ':qty' => $quantity,
        ':total' => $totalAmount,
        ':pay' => $paymentMethod,
        ':message' => $notes ?: null,
        ':items_json' => $itemsJson,
        ':wa_url' => $waUrl
    ]);

    jsonResponse([
        'success' => true,
        'message' => $type === 'whatsapp_order' ? 'Order initiated successfully!' : 'Enquiry prepared successfully!',
        'enquiry_code' => $enquiryCode,
        'whatsapp_url' => $waUrl,
        'type' => $type,
        'total' => $totalAmount,
        'formatted_total' => formatPrice($totalAmount)
    ]);

} catch (Throwable $e) {
    error_log('[Waypure Enquiry API Error] ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Unable to submit enquiry at this moment. Please try again or WhatsApp us directly.'
    ], 500);
}

