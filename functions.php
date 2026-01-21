<?php
// Database helper functions

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
