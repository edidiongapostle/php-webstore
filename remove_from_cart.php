<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_id = isset($_POST['website_id']) ? (int)$_POST['website_id'] : 0;
    
    if ($website_id > 0) {
        if (removeFromCart($website_id)) {
            $_SESSION['success_message'] = "Item removed from cart!";
        } else {
            $_SESSION['error_message'] = "Item not found in cart!";
        }
    }
}

header('Location: cart.php');
exit;
?>
