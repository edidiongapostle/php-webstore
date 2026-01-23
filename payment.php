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

// Get payment method from session or form
$payment_method = $_POST['payment_method'] ?? $_SESSION['checkout_data']['payment_method'] ?? '';
$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');

if (empty($payment_method) || empty($cart_items)) {
    header('Location: checkout.php');
    exit;
}

// Store payment method in session
$_SESSION['payment_method'] = $payment_method;

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
$pageTitle = "Payment - " . $site_name;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Process the order
    $anonymous_checkout = isset($_POST['anonymous_checkout']) ? 1 : 0;
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    try {
        $customer_data = [
            'name' => $anonymous_checkout ? 'Anonymous Customer' : $name,
            'email' => $anonymous_checkout ? 'anonymous@webstore.com' : $email,
            'phone' => $phone,
            'total' => $cart_total
        ];
        
        $order_id = createOrder($customer_data, $cart_items);
        
        if ($order_id) {
            clearCart();
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $order_id;
            $_SESSION['payment_method'] = $payment_method;
            header('Location: order_success.php');
            exit;
        } else {
            $errors['general'] = 'Failed to create order. Please try again.';
        }
    } catch (Exception $e) {
        $errors['general'] = 'An error occurred. Please try again.';
    }
}
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

    <!-- Payment Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-3xl font-bold mb-8">Payment Details</h2>

        <?php if (isset($errors['general'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Payment Method Details -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <i class="<?php echo $selected_payment['icon']; ?> text-3xl mr-4 text-indigo-600"></i>
                    <div>
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($selected_payment['name']); ?></h3>
                        <p class="text-gray-600">Complete your payment using this method</p>
                    </div>
                </div>

                <!-- Payment Method Specific Instructions -->
                <div class="space-y-4">
                    <?php if ($selected_payment['type'] === 'credit_card'): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Credit/Debit Card Payment</h4>
                            <p class="text-sm text-blue-800 mb-4">
                                Your card will be processed securely via <?php echo htmlspecialchars($config['processor'] ?? 'Stripe'); ?>.
                                <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
                                    <span class="text-orange-600 font-medium">This is a test transaction (sandbox mode).</span>
                                <?php endif; ?>
                            </p>
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-lock mr-1"></i> Secure payment processing
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_payment['type'] === 'paypal'): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">PayPal Payment</h4>
                            <p class="text-sm text-blue-800 mb-4">
                                You will be redirected to PayPal to complete your payment.
                                <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
                                    <span class="text-orange-600 font-medium">This is a test transaction (sandbox mode).</span>
                                <?php endif; ?>
                            </p>
                            <div class="text-xs text-gray-600">
                                <i class="fab fa-paypal mr-1"></i> PayPal secure checkout
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_payment['type'] === 'bank_transfer'): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 mb-2">Bank Transfer</h4>
                            <p class="text-sm text-green-800 mb-4">
                                Please transfer the total amount to our bank account:
                            </p>
                            <div class="bg-white rounded p-3 border">
                                <p class="font-medium">Bank: <?php echo htmlspecialchars($config['bank_name'] ?? 'Your Bank'); ?></p>
                                <p class="font-medium">Account: <?php echo htmlspecialchars($config['account_number'] ?? '****1234'); ?></p>
                                <p class="text-sm text-gray-600">Reference: Your order ID</p>
                            </div>
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-university mr-1"></i> Bank transfer details
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_payment['type'] === 'crypto'): ?>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <h4 class="font-semibold text-orange-900 mb-2">Cryptocurrency Payment</h4>
                            <p class="text-sm text-orange-800 mb-4">
                                Send cryptocurrency to the addresses below or scan the QR codes:
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php 
                                $enabled_coins = $config['enabled_coins'] ?? [];
                                
                                if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)): ?>
                                    <div class="bg-white rounded p-4 border">
                                        <p class="font-medium mb-3">Bitcoin (BTC)</p>
                                        <div class="space-y-3">
                                            <div>
                                                <p class="font-mono text-sm break-all mb-2"><?php echo htmlspecialchars($config['btc_address']); ?></p>
                                                <button onclick="copyAddress('<?php echo htmlspecialchars($config['btc_address']); ?>')" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                                    <i class="fas fa-copy mr-1"></i>Copy Address
                                                </button>
                                            </div>
                                            <div class="text-center">
                                                <?php if (!empty($config['btc_qr_code'])): ?>
                                                    <img src="<?php echo htmlspecialchars($config['btc_qr_code']); ?>" alt="Bitcoin QR Code" class="mx-auto border rounded max-w-[200px] max-h-[200px]">
                                                <?php else: ?>
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['btc_address']); ?>" alt="Bitcoin QR Code" class="mx-auto border rounded">
                                                <?php endif; ?>
                                                <p class="text-xs text-gray-500 mt-2">Scan QR Code</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)): ?>
                                    <div class="bg-white rounded p-4 border">
                                        <p class="font-medium mb-3">Ethereum (ETH)</p>
                                        <div class="space-y-3">
                                            <div>
                                                <p class="font-mono text-sm break-all mb-2"><?php echo htmlspecialchars($config['eth_address']); ?></p>
                                                <button onclick="copyAddress('<?php echo htmlspecialchars($config['eth_address']); ?>')" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                                    <i class="fas fa-copy mr-1"></i>Copy Address
                                                </button>
                                            </div>
                                            <div class="text-center">
                                                <?php if (!empty($config['eth_qr_code'])): ?>
                                                    <img src="<?php echo htmlspecialchars($config['eth_qr_code']); ?>" alt="Ethereum QR Code" class="mx-auto border rounded max-w-[200px] max-h-[200px]">
                                                <?php else: ?>
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['eth_address']); ?>" alt="Ethereum QR Code" class="mx-auto border rounded">
                                                <?php endif; ?>
                                                <p class="text-xs text-gray-500 mt-2">Scan QR Code</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)): ?>
                                    <div class="bg-white rounded p-4 border">
                                        <p class="font-medium mb-3">Litecoin (LTC)</p>
                                        <div class="space-y-3">
                                            <div>
                                                <p class="font-mono text-sm break-all mb-2"><?php echo htmlspecialchars($config['ltc_address']); ?></p>
                                                <button onclick="copyAddress('<?php echo htmlspecialchars($config['ltc_address']); ?>')" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                                    <i class="fas fa-copy mr-1"></i>Copy Address
                                                </button>
                                            </div>
                                            <div class="text-center">
                                                <?php if (!empty($config['ltc_qr_code'])): ?>
                                                    <img src="<?php echo htmlspecialchars($config['ltc_qr_code']); ?>" alt="Litecoin QR Code" class="mx-auto border rounded max-w-[200px] max-h-[200px]">
                                                <?php else: ?>
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($config['ltc_address']); ?>" alt="Litecoin QR Code" class="mx-auto border rounded">
                                                <?php endif; ?>
                                                <p class="text-xs text-gray-500 mt-2">Scan QR Code</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-600 mt-4">
                                <i class="fab fa-bitcoin mr-1"></i> Cryptocurrency payment with QR codes
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Summary & Confirmation -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-6">Order Summary</h3>
                
                <div class="space-y-3 mb-6">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600"><?php echo htmlspecialchars($item['title']); ?></span>
                            <span><?php echo formatPrice($item['price']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="border-t pt-3 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span>$0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total</span>
                            <span class="text-indigo-600"><?php echo formatPrice($cart_total); ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="confirm_payment" value="1">
                    <input type="hidden" name="payment_method" value="<?php echo $payment_method; ?>">
                    <input type="hidden" name="anonymous_checkout" value="<?php echo $_SESSION['checkout_data']['anonymous_checkout'] ?? '0'; ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['name'] ?? ''); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['email'] ?? ''); ?>">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['phone'] ?? ''); ?>">
                    <input type="hidden" name="notes" value="<?php echo htmlspecialchars($_SESSION['checkout_data']['notes'] ?? ''); ?>">
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-semibold text-yellow-900 mb-2">Confirmation Required</h4>
                        <p class="text-sm text-yellow-800">
                            Please review your payment details and confirm to complete your order.
                        </p>
                        <label class="flex items-start">
                            <input type="checkbox" name="confirm" required class="mt-1 h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-yellow-800">
                                I have reviewed the payment details and confirm my order
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-check mr-2"></i>
                        Confirm Order & Complete Payment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            <p class="mt-2 text-gray-400">Premium websites for your business needs</p>
        </div>
    </footer>
    
    <script>
        function copyAddress(address) {
            navigator.clipboard.writeText(address)
                .then(() => {
                    // Show success message
                    const button = event.target;
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
                    button.classList.add('bg-green-600');
                    button.classList.remove('bg-blue-500');
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-600');
                        button.classList.add('bg-blue-500');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy address: ', err);
                });
        }
        
        // Handle payment form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const confirmCheckbox = document.querySelector('input[name="confirm"]');
            if (!confirmCheckbox.checked) {
                e.preventDefault();
                alert('Please confirm that you have reviewed the payment details');
                confirmCheckbox.focus();
            }
        });
    </script>
</body>
</html>
