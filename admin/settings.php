<?php
// Define admin section to optimize loading
define('ADMIN_SECTION', true);

session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;
$active_tab = $_GET['tab'] ?? 'general';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
        // Define all possible settings to ensure they get processed
        $all_settings = [
            'site_name', 'site_email', 'currency', 'timezone', 'max_upload_size', 'items_per_page',
            'tax_rate', 'shipping_enabled', 'anonymous_checkout', 'crypto_payments',
            'enable_reviews', 'enable_wishlist', 'auto_backup', 'maintenance_mode',
            'seo_title', 'seo_description', 'seo_keywords'
        ];
        
        foreach ($all_settings as $key) {
            $value = isset($_POST['settings'][$key]) ? $_POST['settings'][$key] : '';
            
            // Handle checkboxes specifically
            if (in_array($key, ['shipping_enabled', 'anonymous_checkout', 'crypto_payments', 'enable_reviews', 'enable_wishlist', 'auto_backup', 'maintenance_mode'])) {
                $value = isset($_POST['settings'][$key]) ? '1' : '0';
            }
            
            try {
                $stmt = $conn->prepare("UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } catch (Exception $e) {
                $errors['general'] = 'Failed to save settings. Please try again.';
            }
        }
        
        if (empty($errors)) {
            $success = 'Settings saved successfully!';
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_payment') {
        $payment_id = sanitizeInput($_POST['payment_id'] ?? '');
        $enabled = sanitizeInput($_POST['enabled'] ?? '');
        
        try {
            $stmt = $conn->prepare("UPDATE payment_methods SET enabled = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$enabled, $payment_id]);
            $success = 'Payment method updated successfully!';
        } catch (Exception $e) {
            $errors['payment'] = 'Failed to update payment method. Please try again.';
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'save_payment') {
        $payment_id = sanitizeInput($_POST['payment_id'] ?? '');
        $name = sanitizeInput($_POST['name'] ?? '');
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        // Get existing config
        $stmt = $conn->prepare("SELECT config_data FROM payment_methods WHERE id = ?");
        $stmt->execute([$payment_id]);
        $existing_config = json_decode($stmt->fetchColumn(), true) ?: [];
        
        // Handle file uploads
        $btc_qr_code = $existing_config['btc_qr_code'] ?? '';
        $eth_qr_code = $existing_config['eth_qr_code'] ?? '';
        $ltc_qr_code = $existing_config['ltc_qr_code'] ?? '';
        
        // Upload directory
        $upload_dir = __DIR__ . '/../uploads/qr_codes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Handle Bitcoin QR Code
        if (isset($_POST['remove_btc_qr']) && $_POST['remove_btc_qr'] == '1') {
            if (file_exists(__DIR__ . '/../' . $btc_qr_code)) {
                unlink(__DIR__ . '/../' . $btc_qr_code);
            }
            $btc_qr_code = '';
        } elseif (isset($_FILES['btc_qr_code']) && $_FILES['btc_qr_code']['error'] == 0) {
            $file = $_FILES['btc_qr_code'];
            if ($file['size'] <= 2 * 1024 * 1024) { // 2MB limit
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif'])) {
                    $filename = 'btc_' . time() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $btc_qr_code = 'uploads/qr_codes/' . $filename;
                    }
                }
            }
        }
        
        // Handle Ethereum QR Code
        if (isset($_POST['remove_eth_qr']) && $_POST['remove_eth_qr'] == '1') {
            if (file_exists(__DIR__ . '/../' . $eth_qr_code)) {
                unlink(__DIR__ . '/../' . $eth_qr_code);
            }
            $eth_qr_code = '';
        } elseif (isset($_FILES['eth_qr_code']) && $_FILES['eth_qr_code']['error'] == 0) {
            $file = $_FILES['eth_qr_code'];
            if ($file['size'] <= 2 * 1024 * 1024) { // 2MB limit
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif'])) {
                    $filename = 'eth_' . time() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $eth_qr_code = 'uploads/qr_codes/' . $filename;
                    }
                }
            }
        }
        
        // Handle Litecoin QR Code
        if (isset($_POST['remove_ltc_qr']) && $_POST['remove_ltc_qr'] == '1') {
            if (file_exists(__DIR__ . '/../' . $ltc_qr_code)) {
                unlink(__DIR__ . '/../' . $ltc_qr_code);
            }
            $ltc_qr_code = '';
        } elseif (isset($_FILES['ltc_qr_code']) && $_FILES['ltc_qr_code']['error'] == 0) {
            $file = $_FILES['ltc_qr_code'];
            if ($file['size'] <= 2 * 1024 * 1024) { // 2MB limit
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif'])) {
                    $filename = 'ltc_' . time() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $ltc_qr_code = 'uploads/qr_codes/' . $filename;
                    }
                }
            }
        }
        
        $config_data = json_encode([
            'processor' => sanitizeInput($_POST['processor'] ?? ''),
            'public_key' => sanitizeInput($_POST['public_key'] ?? ''),
            'secret_key' => sanitizeInput($_POST['secret_key'] ?? ''),
            'sandbox' => isset($_POST['sandbox']) ? 1 : 0,
            'btc_address' => sanitizeInput($_POST['btc_address'] ?? ''),
            'eth_address' => sanitizeInput($_POST['eth_address'] ?? ''),
            'ltc_address' => sanitizeInput($_POST['ltc_address'] ?? ''),
            'btc_qr_code' => $btc_qr_code,
            'eth_qr_code' => $eth_qr_code,
            'ltc_qr_code' => $ltc_qr_code,
            'enabled_coins' => $_POST['enabled_coins'] ?? []
        ]);
        
        try {
            $stmt = $conn->prepare("UPDATE payment_methods SET name = ?, enabled = ?, config_data = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$name, $enabled, $config_data, $payment_id]);
            $success = 'Payment method updated successfully!';
        } catch (Exception $e) {
            $errors['payment'] = 'Failed to update payment method. Please try again.';
        }
    }
}

// Get all settings
$settings = [];
$stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get payment methods
$payment_methods = $conn->query("SELECT * FROM payment_methods ORDER BY sort_order")->fetchAll();

$pageTitle = "Settings - WebStore Admin";
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
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">WebStore Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div class="flex">
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <nav class="mt-8">
                <a href="dashboard.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="websites.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-globe mr-3"></i>
                    Websites
                </a>
                <a href="categories.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="orders.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Orders
                </a>
                <a href="add_website.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-plus mr-3"></i>
                    Add Website
                </a>
                <a href="settings.php" class="block px-4 py-3 text-gray-700 bg-indigo-50 border-r-4 border-indigo-600">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <h2 class="text-3xl font-bold mb-8">Settings</h2>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>

            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-lg mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <a href="?tab=general" class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $active_tab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            <i class="fas fa-cog mr-2"></i>
                            General
                        </a>
                        <a href="?tab=payment" class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $active_tab === 'payment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            <i class="fas fa-credit-card mr-2"></i>
                            Payment Methods
                        </a>
                        <a href="?tab=pricing" class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $active_tab === 'pricing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            <i class="fas fa-dollar-sign mr-2"></i>
                            Pricing
                        </a>
                        <a href="?tab=features" class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $active_tab === 'features' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            <i class="fas fa-star mr-2"></i>
                            Features
                        </a>
                        <a href="?tab=seo" class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $active_tab === 'seo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            <i class="fas fa-search mr-2"></i>
                            SEO
                        </a>
                    </nav>
                </div>
            </div>

            <!-- General Settings Tab -->
            <?php if ($active_tab === 'general'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-6">General Settings</h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                                <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                                <input type="email" name="settings[site_email]" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select name="settings[currency]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                    <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                    <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                    <option value="JPY" <?php echo ($settings['currency'] ?? '') === 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="settings[timezone]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="UTC" <?php echo ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                    <option value="America/Chicago" <?php echo ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                    <option value="America/Denver" <?php echo ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                    <option value="America/Los_Angeles" <?php echo ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Upload Size (MB)</label>
                                <input type="number" name="settings[max_upload_size]" value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '50'); ?>" 
                                       min="1" max="500" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Items Per Page</label>
                                <input type="number" name="settings[items_per_page]" value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '12'); ?>" 
                                       min="6" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Payment Methods Tab -->
            <?php if ($active_tab === 'payment'): ?>
                <div class="space-y-6">
                    <?php foreach ($payment_methods as $payment): ?>
                        <?php 
                        $config = json_decode($payment['config_data'], true) ?: [];
                        ?>
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <i class="<?php echo $payment['icon']; ?> text-2xl mr-3 text-indigo-600"></i>
                                    <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($payment['name']); ?></h3>
                                    <span class="ml-3 px-2 py-1 rounded-full text-xs font-semibold <?php echo $payment['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $payment['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </div>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_payment">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    <input type="hidden" name="enabled" value="<?php echo $payment['enabled'] ? '0' : '1'; ?>">
                                    <button type="submit" class="px-4 py-2 rounded-lg font-medium <?php echo $payment['enabled'] ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700'; ?> transition">
                                        <?php echo $payment['enabled'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                            </div>
                            
                            <form method="POST" class="space-y-4" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="save_payment">
                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($payment['name']); ?>">
                                <input type="hidden" name="enabled" value="<?php echo $payment['enabled']; ?>">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php if ($payment['type'] === 'credit_card' || $payment['type'] === 'paypal'): ?>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Processor</label>
                                            <select name="processor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="stripe" <?php echo ($config['processor'] ?? '') === 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                                                <option value="paypal" <?php echo ($config['processor'] ?? '') === 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                                            <input type="text" name="public_key" value="<?php echo htmlspecialchars($config['public_key'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                            <input type="password" name="secret_key" value="<?php echo htmlspecialchars($config['secret_key'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="sandbox" value="1" <?php echo ($config['sandbox'] ?? 1) ? 'checked' : ''; ?> 
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <span class="ml-2 text-sm text-gray-700">Sandbox Mode</span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($payment['type'] === 'crypto'): ?>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bitcoin Address</label>
                                            <input type="text" name="btc_address" value="<?php echo htmlspecialchars($config['btc_address'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bitcoin QR Code</label>
                                            <?php if (!empty($config['btc_qr_code'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo htmlspecialchars($config['btc_qr_code']); ?>" alt="Bitcoin QR Code" class="h-20 w-20 border rounded">
                                                    <br>
                                                    <label class="flex items-center mt-2">
                                                        <input type="checkbox" name="remove_btc_qr" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                        <span class="ml-2 text-sm text-red-600">Remove current QR code</span>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="btc_qr_code" accept="image/*" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <p class="text-xs text-gray-500 mt-1">Upload QR code image (PNG, JPG, GIF - Max 2MB)</p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Ethereum Address</label>
                                            <input type="text" name="eth_address" value="<?php echo htmlspecialchars($config['eth_address'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Ethereum QR Code</label>
                                            <?php if (!empty($config['eth_qr_code'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo htmlspecialchars($config['eth_qr_code']); ?>" alt="Ethereum QR Code" class="h-20 w-20 border rounded">
                                                    <br>
                                                    <label class="flex items-center mt-2">
                                                        <input type="checkbox" name="remove_eth_qr" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                        <span class="ml-2 text-sm text-red-600">Remove current QR code</span>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="eth_qr_code" accept="image/*" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <p class="text-xs text-gray-500 mt-1">Upload QR code image (PNG, JPG, GIF - Max 2MB)</p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Litecoin Address</label>
                                            <input type="text" name="ltc_address" value="<?php echo htmlspecialchars($config['ltc_address'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Litecoin QR Code</label>
                                            <?php if (!empty($config['ltc_qr_code'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo htmlspecialchars($config['ltc_qr_code']); ?>" alt="Litecoin QR Code" class="h-20 w-20 border rounded">
                                                    <br>
                                                    <label class="flex items-center mt-2">
                                                        <input type="checkbox" name="remove_ltc_qr" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                        <span class="ml-2 text-sm text-red-600">Remove current QR code</span>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="ltc_qr_code" accept="image/*" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <p class="text-xs text-gray-500 mt-1">Upload QR code image (PNG, JPG, GIF - Max 2MB)</p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Enabled Coins</label>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="enabled_coins[]" value="BTC" <?php echo in_array('BTC', $config['enabled_coins'] ?? []) ? 'checked' : ''; ?> 
                                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">Bitcoin (BTC)</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="enabled_coins[]" value="ETH" <?php echo in_array('ETH', $config['enabled_coins'] ?? []) ? 'checked' : ''; ?> 
                                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">Ethereum (ETH)</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="enabled_coins[]" value="LTC" <?php echo in_array('LTC', $config['enabled_coins'] ?? []) ? 'checked' : ''; ?> 
                                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">Litecoin (LTC)</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($payment['type'] === 'bank_transfer'): ?>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                                            <input type="text" name="bank_name" value="<?php echo htmlspecialchars($config['bank_name'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                            <input type="text" name="account_number" value="<?php echo htmlspecialchars($config['account_number'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Routing Number</label>
                                            <input type="text" name="routing_number" value="<?php echo htmlspecialchars($config['routing_number'] ?? ''); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Payment Method
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pricing Settings Tab -->
            <?php if ($active_tab === 'pricing'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-6">Pricing Settings</h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                <input type="number" name="settings[tax_rate]" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>" 
                                       step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-sm text-gray-500 mt-1">Set to 0 for no tax</p>
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="settings[shipping_enabled]" value="1" <?php echo ($settings['shipping_enabled'] ?? '0') ? 'checked' : ''; ?> 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Enable Shipping</span>
                                </label>
                                <p class="text-sm text-gray-500 mt-1">For physical products (if applicable)</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Save Pricing Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Features Settings Tab -->
            <?php if ($active_tab === 'features'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-6">Feature Settings</h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div class="space-y-4">
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Anonymous Checkout</span>
                                    <p class="text-sm text-gray-500">Allow customers to checkout without providing personal information</p>
                                </div>
                                <input type="checkbox" name="settings[anonymous_checkout]" value="1" <?php echo ($settings['anonymous_checkout'] ?? '1') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Cryptocurrency Payments</span>
                                    <p class="text-sm text-gray-500">Enable Bitcoin, Ethereum, and other crypto payments</p>
                                </div>
                                <input type="checkbox" name="settings[crypto_payments]" value="1" <?php echo ($settings['crypto_payments'] ?? '1') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Customer Reviews</span>
                                    <p class="text-sm text-gray-500">Allow customers to leave reviews for purchased products</p>
                                </div>
                                <input type="checkbox" name="settings[enable_reviews]" value="1" <?php echo ($settings['enable_reviews'] ?? '1') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Wishlist</span>
                                    <p class="text-sm text-gray-500">Enable wishlist functionality for customers</p>
                                </div>
                                <input type="checkbox" name="settings[enable_wishlist]" value="1" <?php echo ($settings['enable_wishlist'] ?? '1') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Automatic Backups</span>
                                    <p class="text-sm text-gray-500">Enable automatic database backups</p>
                                </div>
                                <input type="checkbox" name="settings[auto_backup]" value="1" <?php echo ($settings['auto_backup'] ?? '1') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <span class="font-medium text-gray-900">Maintenance Mode</span>
                                    <p class="text-sm text-gray-500">Put site in maintenance mode (disables frontend)</p>
                                </div>
                                <input type="checkbox" name="settings[maintenance_mode]" value="1" <?php echo ($settings['maintenance_mode'] ?? '0') ? 'checked' : ''; ?> 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </label>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Save Feature Settings
                            </button>
                        </div>
                    </form>
                    
                    <!-- Backup Management -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                        <h3 class="text-xl font-semibold mb-6">Backup Management</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h4 class="font-semibold text-blue-900 mb-2">Automatic Backups</h4>
                            <p class="text-sm text-blue-800">
                                <?php 
                                $auto_backup = getSetting('auto_backup', '1');
                                echo $auto_backup === '1' ? 'Automatic backups are enabled and run daily.' : 'Automatic backups are disabled.';
                                ?>
                            </p>
                        </div>
                        
                        <div class="flex space-x-4">
                            <button onclick="createBackup()" class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                                <i class="fas fa-download mr-2"></i>
                                Create Backup Now
                            </button>
                            <button onclick="viewBackups()" class="bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                                <i class="fas fa-folder mr-2"></i>
                                View Backups
                            </button>
                        </div>
                        
                        <div id="backupResult" class="mt-4 hidden"></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- SEO Settings Tab -->
            <?php if ($active_tab === 'seo'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-6">SEO Settings</h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default SEO Title</label>
                                <input type="text" name="settings[seo_title]" value="<?php echo htmlspecialchars($settings['seo_title'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-sm text-gray-500 mt-1">Used for homepage and meta tags</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default SEO Description</label>
                                <textarea name="settings[seo_description]" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($settings['seo_description'] ?? ''); ?></textarea>
                                <p class="text-sm text-gray-500 mt-1">Meta description for search engines (150-160 characters)</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default SEO Keywords</label>
                                <input type="text" name="settings[seo_keywords]" value="<?php echo htmlspecialchars($settings['seo_keywords'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-sm text-gray-500 mt-1">Comma-separated keywords</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Save SEO Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        function createBackup() {
            const resultDiv = document.getElementById('backupResult');
            resultDiv.innerHTML = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">Creating backup...</div>';
            resultDiv.classList.remove('hidden');
            
            fetch('../backup.php?manual=1')
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        resultDiv.innerHTML = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"><i class="fas fa-check-circle mr-2"></i>Backup created successfully!</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-exclamation-triangle mr-2"></i>Backup failed. Please try again.</div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-exclamation-triangle mr-2"></i>Error creating backup.</div>';
                });
        }
        
        function viewBackups() {
            window.open('../backups/', '_blank');
        }
    </script>
</body>
</html>
