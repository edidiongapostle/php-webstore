<?php
require_once 'config.php';
require_once 'functions.php';

// Check maintenance mode
$maintenance_mode = getSetting('maintenance_mode', '0');
if ($maintenance_mode === '1' && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(503);
    include 'maintenance.php';
    exit;
}

$site_name = getSetting('site_name', 'WebStore');
$pageTitle = "Blog - " . $site_name;
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
                    <a href="index.php" class="text-2xl font-bold text-indigo-600"><?php echo htmlspecialchars($site_name); ?></a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="about.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">About</a>
                    <a href="blog.php" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Blog</a>
                    <a href="cart.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Cart</a>
                </div>
                <div class="flex items-center md:hidden">
                    <button id="mobileMenuBtn" class="text-gray-700 hover:text-indigo-600 px-3 py-2">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <a href="index.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">Home</a>
            <a href="about.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">About</a>
            <a href="blog.php" class="block px-4 py-3 text-indigo-600 bg-indigo-50">Blog</a>
            <a href="cart.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">Cart</a>
        </div>
    </nav>

    <!-- Blog Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold mb-8 text-center">Blog</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Blog Post 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                <div class="p-6">
                    <span class="text-xs text-indigo-600 font-semibold">TIPS & TRICKS</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">How to Choose the Right Website Template</h2>
                    <p class="text-gray-600 text-sm mb-4">Learn the key factors to consider when selecting a website template for your project.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>

            <!-- Blog Post 2 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-green-500 to-teal-500"></div>
                <div class="p-6">
                    <span class="text-xs text-green-600 font-semibold">TUTORIAL</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">Getting Started with Your New Website</h2>
                    <p class="text-gray-600 text-sm mb-4">A step-by-step guide to setting up and customizing your purchased website.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>

            <!-- Blog Post 3 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-orange-500 to-red-500"></div>
                <div class="p-6">
                    <span class="text-xs text-orange-600 font-semibold">NEWS</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">New Payment Methods Available</h2>
                    <p class="text-gray-600 text-sm mb-4">We've added more payment options to make your shopping experience even better.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>

            <!-- Blog Post 4 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                <div class="p-6">
                    <span class="text-xs text-blue-600 font-semibold">SECURITY</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">Protecting Your Digital Downloads</h2>
                    <p class="text-gray-600 text-sm mb-4">Best practices for securing your purchased digital files and assets.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>

            <!-- Blog Post 5 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-pink-500 to-rose-500"></div>
                <div class="p-6">
                    <span class="text-xs text-pink-600 font-semibold">FEATURES</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">Top 5 Website Features You Need</h2>
                    <p class="text-gray-600 text-sm mb-4">Essential features every modern website should have to succeed online.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>

            <!-- Blog Post 6 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-48 bg-gradient-to-r from-yellow-500 to-amber-500"></div>
                <div class="p-6">
                    <span class="text-xs text-yellow-600 font-semibold">UPDATES</span>
                    <h2 class="text-xl font-semibold mt-2 mb-3">Platform Updates Coming Soon</h2>
                    <p class="text-gray-600 text-sm mb-4">Exciting new features and improvements coming to our platform.</p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Read More →</a>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <p class="text-gray-600 mb-4">More articles coming soon!</p>
            <a href="index.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-shopping-bag mr-2"></i>
                Browse Our Products
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($site_name); ?></h3>
                    <p class="text-gray-400 text-sm">Your trusted source for premium digital products.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="index.php" class="hover:text-white">Home</a></li>
                        <li><a href="about.php" class="hover:text-white">About</a></li>
                        <li><a href="blog.php" class="hover:text-white">Blog</a></li>
                        <li><a href="cart.php" class="hover:text-white">Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="terms.php" class="hover:text-white">Terms of Service</a></li>
                        <li><a href="privacy.php" class="hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact</h4>
                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars(getSetting('site_email', 'noreply@webstore.com')); ?></p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
