<?php
// Database helper functions

function getSetting($key, $default = null) {
    global $conn;
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Return default values if database fails
            return $default;
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

function getPaymentMethods() {
    global $conn;
    static $payment_methods = null;
    
    if ($payment_methods === null) {
        $payment_methods = [];
        try {
            $stmt = $conn->query("SELECT * FROM payment_methods WHERE enabled = 1 ORDER BY sort_order");
            $payment_methods = $stmt->fetchAll();
        } catch (Exception $e) {
            // Return empty array if database fails
            return [];
        }
    }
    
    return $payment_methods;
}

function getAllWebsites() {
    global $conn;
    $sql = "SELECT * FROM websites WHERE status = 'active' ORDER BY featured DESC, created_at DESC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

function getWebsiteById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM websites WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addToCart($website_id) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (!in_array($website_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $website_id;
        return true;
    }
    return false;
}

function removeFromCart($website_id) {
    if (isset($_SESSION['cart'])) {
        $key = array_search($website_id, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            return true;
        }
    }
    return false;
}

function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    $valid_cart = [];
    global $conn;
    
    foreach ($_SESSION['cart'] as $website_id) {
        $website = getWebsiteById($website_id);
        if ($website) {
            $items[] = $website;
            $valid_cart[] = $website_id;
        }
    }
    
    // Update cart session to remove invalid items
    $_SESSION['cart'] = $valid_cart;
    
    return $items;
}

function getCartCount() {
    $items = getCartItems();
    return count($items);
}

function getCartTotal() {
    $total = 0;
    $items = getCartItems();
    
    foreach ($items as $item) {
        $total += $item['price'];
    }
    
    return $total;
}

function createOrder($customer_data, $cart_items) {
    global $conn;

    $conn->beginTransaction();

    try {
        // Generate unique order reference
        $order_reference = generateOrderReference();

        // Insert order
        $anonymous_checkout = isset($customer_data['anonymous_checkout']) ? $customer_data['anonymous_checkout'] : 0;
        $stmt = $conn->prepare("INSERT INTO orders (order_reference, customer_name, customer_email, customer_phone, total_amount, status, anonymous_checkout, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$order_reference, $customer_data['name'], $customer_data['email'], $customer_data['phone'], $customer_data['total'], $anonymous_checkout]);
        $order_id = $conn->lastInsertId();

        // Insert order items
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, website_id, price, title) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['price'], $item['title']]);
        }

        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function createOrderWithReference($customer_data, $cart_items, $order_reference) {
    global $conn;

    $conn->beginTransaction();

    try {
        // Insert order with provided reference
        $anonymous_checkout = isset($customer_data['anonymous_checkout']) ? $customer_data['anonymous_checkout'] : 0;
        $stmt = $conn->prepare("INSERT INTO orders (order_reference, customer_name, customer_email, customer_phone, total_amount, status, anonymous_checkout, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$order_reference, $customer_data['name'], $customer_data['email'], $customer_data['phone'], $customer_data['total'], $anonymous_checkout]);
        $order_id = $conn->lastInsertId();

        // Insert order items
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, website_id, price, title) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['price'], $item['title']]);
        }

        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function clearCart() {
    unset($_SESSION['cart']);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function formatPrice($price) {
    return CURRENCY . ' ' . number_format($price, 2);
}

function generateOrderReference() {
    // Format: TKR-YYYYMMDD-XXXXXX
    // TKR = prefix
    // YYYYMMDD = current date
    // XXXXXX = random alphanumeric string
    $prefix = 'TKR';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    return $prefix . '-' . $date . '-' . $random;
}

function generateDownloadToken() {
    // Generate a random 12-character alphanumeric token
    return bin2hex(random_bytes(6));
}

function sendOrderApprovalEmail($order) {
    global $conn;
    $site_name = getSetting('site_name', 'WebStore');
    $site_email = getSetting('site_email', 'noreply@webstore.com');
    $site_url = SITE_URL;

    $download_link = $site_url . '/download.php?token=' . $order['download_token'];
    $order_ref = $order['order_reference'];

    $subject = "Your Order Has Been Approved - {$order_ref}";

    $message = "
    <html>
    <head>
        <title>Order Approved</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>{$site_name}</h1>
            </div>
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #333; margin-top: 0;'>Your Order Has Been Approved!</h2>
                <p>Great news! Your order <strong>{$order_ref}</strong> has been verified and approved.</p>
                
                <div style='background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #667eea;'>Download Your Purchase</h3>
                    <p>You can now download your purchased items using the link below:</p>
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='{$download_link}' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Download Now</a>
                    </p>
                    <p style='font-size: 12px; color: #666;'>Or copy this link: {$download_link}</p>
                </div>

                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #856404;'>Important Notes:</h4>
                    <ul style='margin: 10px 0; padding-left: 20px; color: #856404;'>
                        <li>You can download your files up to 5 times</li>
                        <li>The download link is valid for this order only</li>
                        <li>Please save your files in a secure location</li>
                    </ul>
                </div>

                <p style='color: #666; font-size: 14px;'>If you have any questions or issues, please contact our support team.</p>
                
                <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                
                <p style='text-align: center; color: #999; font-size: 12px;'>
                    &copy; " . date('Y') . " {$site_name}. All rights reserved.<br>
                    This is an automated email, please do not reply.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$site_name} <{$site_email}>\r\n";
    $headers .= "Reply-To: {$site_email}\r\n";

    return mail($order['customer_email'], $subject, $message, $headers);
}
?>
