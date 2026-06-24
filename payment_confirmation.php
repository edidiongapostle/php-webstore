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
$cart_items = getCartItems();
$cart_total = getCartTotal();
$site_name = getSetting('site_name', 'WebStore');

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;
$order_id = null;
$order_reference = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_reference = sanitizeInput($_POST['transaction_reference'] ?? '');
    $anonymous_checkout = isset($checkout_data['anonymous_checkout']) ? $checkout_data['anonymous_checkout'] : '0';
    $name = sanitizeInput($checkout_data['name'] ?? '');
    $email = sanitizeInput($checkout_data['email'] ?? '');
    $phone = sanitizeInput($checkout_data['phone'] ?? '');
    $notes = sanitizeInput($checkout_data['notes'] ?? '');
    $payment_method = sanitizeInput($checkout_data['payment_method'] ?? '');

    // Validate transaction reference
    if (empty($transaction_reference)) {
        $errors['transaction_reference'] = 'Transaction reference is required';
    }

    // Handle screenshot upload
    $screenshot_path = '';
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
        $file = $_FILES['payment_screenshot'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors['payment_screenshot'] = 'Only JPG, PNG, and GIF images are allowed';
        } elseif ($file['size'] > $max_size) {
            $errors['payment_screenshot'] = 'File size must be less than 5MB';
        } else {
            $upload_dir = __DIR__ . '/uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = 'payment_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $screenshot_path = 'uploads/payment_proofs/' . $filename;
            } else {
                $errors['payment_screenshot'] = 'Failed to upload screenshot';
            }
        }
    } else {
        $errors['payment_screenshot'] = 'Payment screenshot is required';
    }

    if (empty($errors)) {
        // If not anonymous but name/email are empty, treat as anonymous
        if ($anonymous_checkout !== '1' && (empty($name) || empty($email))) {
            $anonymous_checkout = '1';
        }

        try {
            $customer_data = [
                'name' => $anonymous_checkout === '1' ? 'Anonymous Customer' : $name,
                'email' => $anonymous_checkout === '1' ? 'anonymous@webstore.com' : $email,
                'phone' => $phone,
                'total' => $cart_total,
                'anonymous_checkout' => $anonymous_checkout === '1' ? 1 : 0
            ];

            // Use the order reference that was already generated and shown to customer
            $order_reference = $_SESSION['order_reference'] ?? generateOrderReference();

            // Check if this reference already exists in database (from a previous failed attempt)
            $stmt = $conn->prepare("SELECT id FROM orders WHERE order_reference = ?");
            $stmt->execute([$order_reference]);
            if ($stmt->fetch()) {
                // Reference already exists, generate a new one
                $order_reference = generateOrderReference();
            }

            // Create order with the pre-generated reference
            $order_id = createOrderWithReference($customer_data, $cart_items, $order_reference);

            if ($order_id) {
                // Update order with payment details
                $stmt = $conn->prepare("UPDATE orders SET status = 'awaiting_verification', payment_method = ?, transaction_reference = ?, payment_screenshot = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$payment_method, $transaction_reference, $screenshot_path, $order_id]);

                // Clear cart and session
                clearCart();
                unset($_SESSION['checkout_data']);
                unset($_SESSION['order_reference']);
                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $order_id;
                $_SESSION['order_reference'] = $order_reference;
                $_SESSION['payment_method'] = $payment_method;

                // Send order confirmation email
                $email_data = [
                    'site_name' => $site_name,
                    'order_reference' => $order_reference,
                    'total_amount' => formatPrice($cart_total),
                    'payment_method' => $payment_method,
                    'current_year' => date('Y')
                ];
                sendEmail($customer_data['email'], "Order Confirmation - {$order_reference}", 'order_confirmation', $email_data);

                $success = true;
            } else {
                $errors['general'] = 'Failed to create order. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Payment Confirmation - " . $site_name;
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Payment Confirmation Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-8 mb-6">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-green-900 mb-2">Payment Submitted Successfully!</h2>
                        <p class="text-green-800 mb-4">Your order is awaiting verification.</p>
                        <div class="bg-white rounded p-4 border mt-4">
                            <p class="text-sm text-gray-600">Order Reference:</p>
                            <p class="text-xl font-bold text-indigo-600"><?php echo htmlspecialchars($order_reference); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">What happens next?</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">1</span>
                            </div>
                            <div>
                                <p class="font-medium">Payment Verification</p>
                                <p class="text-sm text-gray-600">Our team will verify your payment within 24-48 hours.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">2</span>
                            </div>
                            <div>
                                <p class="font-medium">Order Approval</p>
                                <p class="text-sm text-gray-600">Once verified, your order will be approved and you'll receive a download link.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">3</span>
                            </div>
                            <div>
                                <p class="font-medium">Download Your Purchase</p>
                                <p class="text-sm text-gray-600">Use the download link sent to your email to access your purchase.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="index.php" class="block w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition text-center">
                    <i class="fas fa-home mr-2"></i>
                    Return to Home
                </a>

            <?php else: ?>
                <!-- Payment Confirmation Form -->
                <h2 class="text-3xl font-bold mb-8">Payment Confirmation</h2>

                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
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

                <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Submit Payment Details</h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference *</label>
                            <input type="text" name="transaction_reference" value="<?php echo htmlspecialchars($_POST['transaction_reference'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Enter transaction ID or reference number">
                            <?php if (isset($errors['transaction_reference'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['transaction_reference']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Screenshot *</label>
                            <input type="file" name="payment_screenshot" accept="image/jpeg,image/png,image/jpg,image/gif"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-sm text-gray-500 mt-1">Upload a screenshot of your payment confirmation (JPG, PNG, GIF - Max 5MB)</p>
                            <?php if (isset($errors['payment_screenshot'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['payment_screenshot']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Before submitting:</h4>
                            <ul class="text-sm text-yellow-800 space-y-1">
                                <li>• Ensure you have made the payment</li>
                                <li>• Take a clear screenshot of the payment confirmation</li>
                                <li>• Copy the transaction reference from your payment</li>
                                <li>• Double-check the amount matches your order total</li>
                            </ul>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Submit Payment Details
                            </button>
                            <a href="payment_instructions.php" class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Instructions
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
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
