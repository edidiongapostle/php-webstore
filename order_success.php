<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if order was successful
if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['order_id'] ?? 0;

// Clear session variables
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);

$pageTitle = "Order Successful - WebStore";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">WebStore</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="cart.php" class="relative text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="admin/login.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Success Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-check-circle text-4xl text-green-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Order Successful!</h2>
                <p class="text-lg text-gray-600 mb-2">Thank you for your purchase.</p>
                <p class="text-gray-500">Your order has been received and is being processed.</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                <h3 class="text-lg font-semibold mb-4">Order Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-medium">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date:</span>
                        <span class="font-medium"><?php echo date('F j, Y, g:i a'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium text-yellow-600">Processing</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h4 class="font-semibold text-blue-900 mb-3">What happens next?</h4>
                <div class="text-left space-y-2 text-sm text-blue-800">
                    <div class="flex items-start">
                        <i class="fas fa-envelope mt-1 mr-3 text-blue-600"></i>
                        <span>You'll receive an order confirmation email shortly</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-download mt-1 mr-3 text-blue-600"></i>
                        <span>Download links will be sent once payment is confirmed</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-headset mt-1 mr-3 text-blue-600"></i>
                        <span>Our support team will contact you for installation assistance</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-home mr-2"></i>
                    Back to Home
                </a>
                <a href="mailto:support@webstore.com" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact Support
                </a>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <i class="fas fa-shield-alt text-3xl text-indigo-600 mb-3"></i>
                <h4 class="font-semibold mb-2">Secure Payment</h4>
                <p class="text-sm text-gray-600">Your payment information is encrypted and secure</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <i class="fas fa-redo text-3xl text-indigo-600 mb-3"></i>
                <h4 class="font-semibold mb-2">30-Day Guarantee</h4>
                <p class="text-sm text-gray-600">Full refund within 30 days if not satisfied</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <i class="fas fa-headset text-3xl text-indigo-600 mb-3"></i>
                <h4 class="font-semibold mb-2">24/7 Support</h4>
                <p class="text-sm text-gray-600">Round-the-clock customer support available</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; <?php echo date('Y'); ?> WebStore. All rights reserved.</p>
            <p class="mt-2 text-gray-400">Premium websites for your business needs</p>
        </div>
    </footer>
</body>
</html>
