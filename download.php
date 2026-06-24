<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: index.php');
    exit;
}

// Get order by download token
$stmt = $conn->prepare("SELECT * FROM orders WHERE download_token = ? AND status = 'completed'");
$stmt->execute([$token]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Check download limit
if ($order['download_count'] >= 5) {
    $error = 'Download limit exceeded. You have already downloaded this file 5 times.';
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();

// Handle download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download']) && !isset($error)) {
    // Increment download count
    $stmt = $conn->prepare("UPDATE orders SET download_count = download_count + 1 WHERE id = ?");
    $stmt->execute([$order['id']]);

    // For this implementation, we'll redirect to a page showing download links
    // In a real implementation, you would serve actual files
    $_SESSION['downloaded'] = true;
    header('Location: download.php?token=' . $token);
    exit;
}

$site_name = getSetting('site_name', 'WebStore');
$pageTitle = "Download - " . $site_name;
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Download Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <?php if (isset($error)): ?>
                <!-- Error Message -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-8 mb-6">
                    <div class="text-center">
                        <i class="fas fa-exclamation-circle text-6xl text-red-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-red-900 mb-2">Download Limit Exceeded</h2>
                        <p class="text-red-800"><?php echo $error; ?></p>
                    </div>
                </div>
            <?php elseif (isset($_SESSION['downloaded'])): ?>
                <!-- Download Success -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-8 mb-6">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-green-900 mb-2">Download Ready!</h2>
                        <p class="text-green-800 mb-4">Your files are ready for download.</p>
                        <p class="text-sm text-green-700">Downloads remaining: <?php echo 5 - $order['download_count']; ?>/5</p>
                    </div>
                </div>

                <!-- Download Links -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">Your Downloads</h3>
                    <div class="space-y-4">
                        <?php foreach ($order_items as $item): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($item['title']); ?></p>
                                    <p class="text-sm text-gray-500">Order: <?php echo htmlspecialchars($order['order_reference']); ?></p>
                                </div>
                                <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                    <i class="fas fa-download mr-2"></i>
                                    Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php unset($_SESSION['downloaded']); ?>

            <?php else: ?>
                <!-- Download Confirmation -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-3xl font-bold mb-8">Download Your Purchase</h2>

                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Reference:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($order['order_reference']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Downloads Used:</span>
                                <span class="font-medium"><?php echo $order['download_count']; ?>/5</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Downloads Remaining:</span>
                                <span class="font-medium text-green-600"><?php echo 5 - $order['download_count']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Items to Download</h3>
                        <div class="space-y-3">
                            <?php foreach ($order_items as $item): ?>
                                <div class="flex items-center p-3 bg-white rounded border">
                                    <i class="fas fa-file-archive text-indigo-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($item['title']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo formatPrice($item['price']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-yellow-900 mb-2">Important Notes</h4>
                        <ul class="text-sm text-yellow-800 space-y-1">
                            <li>• You can download your files up to 5 times</li>
                            <li>• Download links are valid for this order only</li>
                            <li>• Please save your files in a secure location</li>
                            <li>• Contact support if you have any issues downloading</li>
                        </ul>
                    </div>

                    <form method="POST">
                        <button type="submit" name="download" value="1" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition <?php echo $order['download_count'] >= 5 ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $order['download_count'] >= 5 ? 'disabled' : ''; ?>>
                            <i class="fas fa-download mr-2"></i>
                            Download Files (<?php echo 5 - $order['download_count']; ?> remaining)
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <a href="index.php" class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                <i class="fas fa-home mr-2"></i>
                Return to Home
            </a>
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
