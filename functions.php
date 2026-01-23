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
    global $conn;
    
    foreach ($_SESSION['cart'] as $website_id) {
        $website = getWebsiteById($website_id);
        if ($website) {
            $items[] = $website;
        }
    }
    
    return $items;
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
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, total_amount, status, created_at) VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)");
        $stmt->execute([$customer_data['name'], $customer_data['email'], $customer_data['phone'], $customer_data['total']]);
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
?>
