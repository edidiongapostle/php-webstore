<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check maintenance mode
$maintenance_mode = getSetting('maintenance_mode', '0');
if ($maintenance_mode === '1' && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(503);
    include 'maintenance.php';
    exit;
}

// Get checkout data from session
if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$payment_method = $checkout_data['payment_method'] ?? '';
$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');

if (empty($payment_method) || empty($cart_items)) {
    header('Location: checkout.php');
    exit;
}

// Get payment method details
$payment_methods = getPaymentMethods();
$selected_payment = null;
foreach ($payment_methods as $payment) {
    if ($payment['type'] === $payment_method) {
        $selected_payment = $payment;
        break;
    }
}

if (!$selected_payment) {
    header('Location: checkout.php');
    exit;
}

$config = json_decode($selected_payment['config_data'], true) ?: [];

// Generate order reference for this transaction
if (!isset($_SESSION['order_reference'])) {
    $_SESSION['order_reference'] = generateOrderReference();
}
$order_reference = $_SESSION['order_reference'];

$pageTitle = "Payment Instructions - " . $site_name;
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
                    <h1 class="text-2xl font-bold text-indigo-600"><?php echo htmlspecialchars($site_name); ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="cart.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Cart</a>
                    <a href="checkout.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Checkout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Payment Instructions Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold mb-8">Payment Instructions</h2>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Your Order Reference:</span>
                        <span class="text-2xl font-bold text-indigo-600"><?php echo htmlspecialchars($order_reference); ?></span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Use this reference when making your payment</p>
                </div>
                <div class="space-y-2">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between">
                            <span><?php echo htmlspecialchars($item['title']); ?></span>
                            <span><?php echo formatPrice($item['price']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total</span>
                            <span class="text-indigo-600"><?php echo formatPrice($cart_total); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">
                    <i class="<?php echo $selected_payment['icon']; ?> mr-2 text-indigo-600"></i>
                    <?php echo htmlspecialchars($selected_payment['name']); ?> Payment Instructions
                </h3>

                <?php if ($selected_payment['type'] === 'bank_transfer'): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-green-900 mb-2">Bank Transfer Details</h4>
                        <p class="text-sm text-green-800 mb-4">
                            Please transfer the total amount to our bank account:
                        </p>
                        <div class="bg-white rounded p-4 border space-y-2">
                            <p class="font-medium">Bank: <?php echo htmlspecialchars($config['bank_name'] ?? 'Your Bank'); ?></p>
                            <p class="font-medium">Account Number: <?php echo htmlspecialchars($config['account_number'] ?? '****1234'); ?></p>
                            <?php if (!empty($config['routing_number'])): ?>
                                <p class="font-medium">Routing Number: <?php echo htmlspecialchars($config['routing_number']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600">Amount: <?php echo formatPrice($cart_total); ?></p>
                            <p class="text-sm text-gray-600 font-semibold">Reference: <span class="text-indigo-600"><?php echo htmlspecialchars($order_reference); ?></span></p>
                        </div>
                    </div>

                <?php elseif ($selected_payment['type'] === 'crypto'): ?>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-orange-900 mb-2">Cryptocurrency Payment</h4>
                        <p class="text-sm text-orange-800 mb-4">
                            Send cryptocurrency to the addresses below or scan the QR codes:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $enabled_coins = $config['enabled_coins'] ?? [];

                            if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)): ?>
                                <div class="bg-white rounded p-4 border">
                                    <p class="font-semibold mb-2">Bitcoin (BTC)</p>
                                    <p class="text-sm break-all mb-2"><?php echo htmlspecialchars($config['btc_address']); ?></p>
                                    <?php if (!empty($config['btc_qr_code'])): ?>
                                        <img src="<?php echo htmlspecialchars($config['btc_qr_code']); ?>" alt="BTC QR Code" class="w-32 h-32 mx-auto">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)): ?>
                                <div class="bg-white rounded p-4 border">
                                    <p class="font-semibold mb-2">Ethereum (ETH)</p>
                                    <p class="text-sm break-all mb-2"><?php echo htmlspecialchars($config['eth_address']); ?></p>
                                    <?php if (!empty($config['eth_qr_code'])): ?>
                                        <img src="<?php echo htmlspecialchars($config['eth_qr_code']); ?>" alt="ETH QR Code" class="w-32 h-32 mx-auto">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)): ?>
                                <div class="bg-white rounded p-4 border">
                                    <p class="font-semibold mb-2">Litecoin (LTC)</p>
                                    <p class="text-sm break-all mb-2"><?php echo htmlspecialchars($config['ltc_address']); ?></p>
                                    <?php if (!empty($config['ltc_qr_code'])): ?>
                                        <img src="<?php echo htmlspecialchars($config['ltc_qr_code']); ?>" alt="LTC QR Code" class="w-32 h-32 mx-auto">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-600 mt-4">Amount: <?php echo formatPrice($cart_total); ?></p>
                        <p class="text-sm text-gray-600 font-semibold">Reference for memo/note: <span class="text-indigo-600"><?php echo htmlspecialchars($order_reference); ?></span></p>
                    </div>

                <?php elseif ($selected_payment['type'] === 'paypal'): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">PayPal Payment</h4>
                        <p class="text-sm text-blue-800 mb-4">
                            Send payment to our PayPal account:
                        </p>
                        <div class="bg-white rounded p-4 border">
                            <p class="font-medium">PayPal Email: <?php echo htmlspecialchars($config['processor'] ?? 'paypal@example.com'); ?></p>
                            <p class="text-sm text-gray-600">Amount: <?php echo formatPrice($cart_total); ?></p>
                            <p class="text-sm text-gray-600 font-semibold">Reference/Note: <span class="text-indigo-600"><?php echo htmlspecialchars($order_reference); ?></span></p>
                            <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
                                <p class="text-xs text-orange-600 mt-2">(Sandbox Mode)</p>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-700">Payment instructions will be provided after order creation.</p>
                    </div>
                <?php endif; ?>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                    <h4 class="font-semibold text-yellow-900 mb-2">Important Notes</h4>
                    <ul class="text-sm text-yellow-800 space-y-1">
                        <li>• Make sure to send the exact amount shown above</li>
                        <li>• Include your order reference in the payment description</li>
                        <li>• Save your transaction reference/screenshot for verification</li>
                        <li>• Your order will be processed after payment verification</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="payment_confirmation.php" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition text-center">
                    <i class="fas fa-check mr-2"></i>
                    I've Made Payment
                </a>
                <a href="checkout.php" class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Checkout
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
