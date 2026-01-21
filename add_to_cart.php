<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_id = isset($_POST['website_id']) ? (int)$_POST['website_id'] : 0;
    
    if ($website_id > 0) {
        $website = getWebsiteById($website_id);
        if ($website) {
            if (addToCart($website_id)) {
                $_SESSION['success_message'] = $website['title'] . " has been added to your cart!";
            } else {
                $_SESSION['info_message'] = $website['title'] . " is already in your cart!";
            }
        } else {
            $_SESSION['error_message'] = "Website not found!";
        }
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
exit;
?>
