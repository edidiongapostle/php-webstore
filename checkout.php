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

$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');
$anonymous_checkout_enabled = getSetting('anonymous_checkout', '1');
$crypto_payments_enabled = getSetting('crypto_payments', '1');
$pageTitle = "Checkout - " . $site_name;

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation - payment details will be validated in payment.php
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    
    if (empty($payment_method)) {
        $errors['payment_method'] = 'Payment method is required';
    }
    
    // Check terms acceptance
    if (!isset($_POST['accept_terms']) || $_POST['accept_terms'] != '1') {
        $errors['accept_terms'] = 'You must accept the Terms of Use to complete your purchase';
    }
    
    // If no errors, redirect to payment page
    if (empty($errors)) {
        // Store form data in session for payment.php
        $_SESSION['checkout_data'] = $_POST;
        header('Location: payment.php');
        exit;
    }
}

$pageTitle = "Checkout - WebStore";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function toggleAnonymousFields(isAnonymous) {
            const billingFields = document.getElementById('billing-fields');
            if (isAnonymous) {
                billingFields.style.display = 'none';
            } else {
                billingFields.style.display = 'block';
            }
        }
        
        // Check if anonymous checkout was previously selected
        document.addEventListener('DOMContentLoaded', function() {
            const anonymousCheckbox = document.querySelector('input[name="anonymous_checkout"]');
            if (anonymousCheckbox && anonymousCheckbox.checked) {
                toggleAnonymousFields(true);
            }
        });
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl sm:text-2xl font-bold text-indigo-600"><?php echo htmlspecialchars($site_name); ?></h1>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="cart.php" class="relative text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <a href="cart.php" class="relative text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium mr-2">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <button onclick="toggleMobileMenu()" class="text-gray-700 hover:text-indigo-600 p-2 rounded-md">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-2">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Checkout Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8">Checkout</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="payment.php" class="space-y-6">
                    <!-- Billing Information -->
                    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                        <h3 class="text-lg sm:text-xl font-semibold mb-4">Billing Information</h3>
                        
                        <!-- Anonymous Checkout Option -->
                        <?php if ($anonymous_checkout_enabled === '1'): ?>
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="anonymous_checkout" value="1" 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Checkout as Guest</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">No account required - quick and easy checkout</p>
                            </div>
                        <?php endif; ?>
                        
                        <div id="billing-fields">
                        <?php if (isset($errors['general'])): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <?php echo $errors['general']; ?>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <?php if (isset($errors['name'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                                <?php endif; ?>
                            </div>
                             
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <?php if (isset($errors['email'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Payment Method</h3>
                        
                        <div class="space-y-3">
                            <?php 
                            $payment_methods = getPaymentMethods();
                            foreach ($payment_methods as $payment):
                                // Skip crypto payments if disabled
                                if ($payment['type'] === 'crypto' && $crypto_payments_enabled !== '1') {
                                    continue;
                                }
                                $config = json_decode($payment['config_data'], true) ?: [];
                            ?>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="payment_method" value="<?php echo $payment['type']; ?>" class="mr-3"
                                           <?php echo (($_POST['payment_method'] ?? '') === $payment['type'] ? 'checked' : ''); ?>
                                    <i class="<?php echo $payment['icon']; ?> mr-2 text-indigo-600"></i>
                                    <div class="flex-1">
                                        <span class="font-medium"><?php echo htmlspecialchars($payment['name']); ?></span>
                                        <?php if ($payment['type'] === 'crypto' && !empty($config)): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php 
                                                $enabled_coins = $config['enabled_coins'] ?? [];
                                                $coins = [];
                                                
                                                if (!empty($config['btc_address']) && in_array('BTC', $enabled_coins)) {
                                                    $coins[] = 'BTC';
                                                }
                                                if (!empty($config['eth_address']) && in_array('ETH', $enabled_coins)) {
                                                    $coins[] = 'ETH';
                                                }
                                                if (!empty($config['ltc_address']) && in_array('LTC', $enabled_coins)) {
                                                    $coins[] = 'LTC';
                                                }
                                                
                                                if (!empty($coins)) {
                                                    echo 'Available: ' . implode(', ', $coins);
                                                } else {
                                                    echo 'No coins enabled';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($payment['type'] === 'credit_card' && !empty($config['processor'])): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Processed via <?php echo htmlspecialchars($config['processor']); ?>
                                                <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
                                                    <span class="text-orange-600">(Sandbox)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($payment['type'] === 'paypal' && !empty($config['processor'])): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Processed via <?php echo htmlspecialchars($config['processor']); ?>
                                                <?php if (!empty($config['sandbox']) && $config['sandbox'] == '1'): ?>
                                                    <span class="text-orange-600">(Sandbox)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($payment['type'] === 'bank_transfer' && !empty($config['bank_name'])): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Transfer to <?php echo htmlspecialchars($config['bank_name']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isset($errors['payment_method'])): ?>
                            <p class="text-red-500 text-sm mt-2"><?php echo $errors['payment_method']; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Order Notes (Optional)</h3>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Any special instructions or notes..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>

                    <!-- Terms of Use -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Terms of Use</h3>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    By completing this purchase, you agree to our <a href="terms.php" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">Terms of Use</a> and <a href="privacy.php" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">Privacy Policy</a>.
                                </p>
                                <ul class="text-xs text-gray-600 mt-2 space-y-1">
                                    <li>• Products are delivered as digital downloads</li>
                                    <li>• 30-day money-back guarantee applies</li>
                                    <li>• License terms vary by product type</li>
                                    <li>• Anonymous purchases available with crypto</li>
                                </ul>
                            </div>
                            
                            <label class="flex items-start">
                                <input type="checkbox" name="accept_terms" value="1" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">
                                    I have read and agree to the <a href="terms.php" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">Terms of Use</a> and understand that this purchase is final unless I request a refund within 30 days.
                                </span>
                            </label>
                            
                            <?php if (isset($errors['accept_terms'])): ?>
                                <p class="text-red-500 text-sm mt-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <?php echo $errors['accept_terms']; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Continue to Payment
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                    <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
                    
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

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Instant download after purchase</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>30 days money-back guarantee</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>24/7 customer support</span>
                        </div>
                    </div>
                </div>
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
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const menuButton = event.target.closest('button');
            
            if (!menu.contains(event.target) && (!menuButton || !menuButton.onclick)) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
