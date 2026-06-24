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
$pageTitle = "About - " . $site_name;
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
                    <a href="about.php" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">About</a>
                    <a href="blog.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Blog</a>
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
            <a href="about.php" class="block px-4 py-3 text-indigo-600 bg-indigo-50">About</a>
            <a href="blog.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">Blog</a>
            <a href="cart.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">Cart</a>
        </div>
    </nav>

    <!-- About Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-8 text-center">About <?php echo htmlspecialchars($site_name); ?></h1>

            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Our Mission</h2>
                <p class="text-gray-700 mb-6">
                    <?php echo htmlspecialchars($site_name); ?> is dedicated to providing high-quality digital products and services to our customers. We believe in making premium digital assets accessible to everyone, with a focus on quality, security, and customer satisfaction.
                </p>

                <h2 class="text-2xl font-semibold mb-4">What We Offer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Premium Websites</h3>
                            <p class="text-gray-600 text-sm">High-quality, professionally designed websites ready for use.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Secure Payments</h3>
                            <p class="text-gray-600 text-sm">Multiple payment options with bank-level security.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Instant Delivery</h3>
                            <p class="text-gray-600 text-sm">Get your purchases immediately after payment verification.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold">24/7 Support</h3>
                            <p class="text-gray-600 text-sm">Our team is always ready to help you with any questions.</p>
                        </div>
                    </div>
                </div>

                <h2 class="text-2xl font-semibold mb-4">Our Story</h2>
                <p class="text-gray-700 mb-6">
                    Founded with a passion for digital excellence, <?php echo htmlspecialchars($site_name); ?> has grown from a small idea to a trusted platform for digital products. We understand the importance of having the right tools and resources to succeed in the digital world, and we're committed to providing exactly that.
                </p>

                <h2 class="text-2xl font-semibold mb-4">Why Choose Us?</h2>
                <ul class="list-disc list-inside text-gray-700 space-y-2">
                    <li>Curated selection of premium digital products</li>
                    <li>Competitive pricing without compromising quality</li>
                    <li>Secure and anonymous checkout options</li>
                    <li>Regular updates and improvements to our products</li>
                    <li>Dedicated customer support team</li>
                </ul>
            </div>

            <div class="bg-indigo-600 rounded-lg shadow-lg p-8 text-white text-center">
                <h2 class="text-2xl font-semibold mb-4">Get in Touch</h2>
                <p class="mb-6">Have questions or want to learn more? We'd love to hear from you!</p>
                <a href="mailto:<?php echo htmlspecialchars(getSetting('site_email', 'noreply@webstore.com')); ?>" class="inline-block bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact Us
                </a>
            </div>
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
