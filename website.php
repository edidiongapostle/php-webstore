<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Get website ID from URL
$website_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get website details
$website = getWebsiteById($website_id);

if (!$website) {
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($website['title']) . " - WebStore";
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
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="admin/login.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="index.php" class="text-gray-500 hover:text-gray-700">Home</a></li>
                    <li><span class="text-gray-400">/</span></li>
                    <li><span class="text-gray-700"><?php echo htmlspecialchars($website['title']); ?></span></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Product Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($website['image_url']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>" class="w-full h-96 object-cover">
                </div>
                <?php if ($website['demo_url']): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Live Demo</h3>
                        <a href="<?php echo htmlspecialchars($website['demo_url']); ?>" target="_blank" class="inline-flex items-center bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            View Live Demo
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                            <?php echo htmlspecialchars($website['category']); ?>
                        </span>
                        <?php if ($website['featured']): ?>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Featured</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($website['title']); ?></h1>
                    <div class="flex items-center space-x-4 mb-6">
                        <span class="text-4xl font-bold text-indigo-600"><?php echo formatPrice($website['price']); ?></span>
                        <span class="text-gray-500">One-time payment</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3">Description</h3>
                    <p class="text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($website['description'])); ?></p>
                </div>

                <?php if ($website['features']): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Features</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <?php 
                            $features = explode(',', $website['features']);
                            foreach ($features as $feature): ?>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span><?php echo htmlspecialchars(trim($feature)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($website['technologies']): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Technologies Used</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $technologies = explode(',', $website['technologies']);
                            foreach ($technologies as $tech): ?>
                                <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">
                                    <?php echo htmlspecialchars(trim($tech)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <div class="border-t pt-6">
                    <form action="add_to_cart.php" method="POST" class="space-y-4">
                        <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                        <div class="flex space-x-4">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Add to Cart
                            </button>
                            <a href="index.php" class="flex-1 bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center hover:bg-gray-300 transition">
                                Continue Shopping
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Additional Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">What's Included:</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Full source code</li>
                        <li>• Installation documentation</li>
                        <li>• 30 days support</li>
                        <li>• Free updates for 6 months</li>
                    </ul>
                </div>
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
