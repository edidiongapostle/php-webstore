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

    $download_link = $site_url . '/download/' . $order['download_token'];
    $order_ref = $order['order_reference'];

    $subject = "Your Order Has Been Approved - {$order_ref}";

    $data = [
        'site_name' => $site_name,
        'order_reference' => $order_ref,
        'download_link' => $download_link,
        'total_amount' => formatPrice($order['total_amount']),
        'current_year' => date('Y')
    ];

    $message = renderEmailTemplate('order_approved', $data);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$site_name} <{$site_email}>\r\n";
    $headers .= "Reply-To: {$site_email}\r\n";

    return mail($order['customer_email'], $subject, $message, $headers);
}

function renderEmailTemplate($template_name, $data = []) {
    $template_path = __DIR__ . '/templates/' . $template_name . '.php';

    if (!file_exists($template_path)) {
        return '<p>Email template not found.</p>';
    }

    ob_start();
    include $template_path;
    $content = ob_get_clean();

    // Replace placeholders with data
    foreach ($data as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }

    return $content;
}

function sendEmail($to, $subject, $template_name, $data = []) {
    $site_name = getSetting('site_name', 'WebStore');
    $site_email = getSetting('site_email', 'noreply@webstore.com');

    $message = renderEmailTemplate($template_name, $data);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$site_name} <{$site_email}>\r\n";
    $headers .= "Reply-To: {$site_email}\r\n";

    return mail($to, $subject, $message, $headers);
}

function createDownloadEntry($order_id, $token, $file_path) {
    global $conn;

    // Set expiration to 30 days from now
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

    $stmt = $conn->prepare("INSERT INTO downloads (order_id, token, file_path, max_downloads, expires_at) VALUES (?, ?, ?, 5, ?)");
    $stmt->execute([$order_id, $token, $file_path, $expires_at]);

    return $conn->lastInsertId();
}

function searchWebsites($query) {
    global $conn;
    
    if (empty($query)) {
        return getAllWebsites();
    }
    
    $query = trim($query);
    $searchTerms = preg_split('/\s+/', $query);
    
    $whereConditions = [];
    $params = [];
    
    foreach ($searchTerms as $term) {
        $term = '%' . $term . '%';
        $whereConditions[] = "(title LIKE ? OR category LIKE ? OR technologies LIKE ? OR features LIKE ? OR description LIKE ?)";
        $params = array_merge($params, [$term, $term, $term, $term, $term]);
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $sql = "SELECT * FROM websites WHERE status = 'active' AND ($whereClause) ORDER BY featured DESC, created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getFeaturedWebsites() {
    global $conn;
    
    $sql = "SELECT * FROM websites WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
?>
