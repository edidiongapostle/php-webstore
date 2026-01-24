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

// Get website ID from URL
$website_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get website details
$website = getWebsiteById($website_id);

if (!$website) {
    header('Location: index.php');
    exit;
}

// Get feature settings
$enable_reviews = getSetting('enable_reviews', '1');
$enable_wishlist = getSetting('enable_wishlist', '1');
$site_name = getSetting('site_name', 'WebStore');

$pageTitle = htmlspecialchars($website['title']) . " - " . $site_name;
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($website['image_url']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>" class="w-full h-64 sm:h-80 lg:h-96 object-cover">
                </div>
                
                <!-- Screenshots Gallery -->
                <?php if (!empty($website['screenshots'])): ?>
                    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                        <h3 class="text-lg font-semibold mb-4">Screenshots</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            <?php 
                            $screenshots = json_decode($website['screenshots'] ?? '[]', true) ?: [];
                            foreach ($screenshots as $screenshot): 
                                if (!empty($screenshot)): ?>
                                    <div class="relative group cursor-pointer" onclick="openScreenshotModal('<?php echo htmlspecialchars($screenshot); ?>')">
                                        <img src="<?php echo htmlspecialchars($screenshot); ?>" alt="Website Screenshot" class="w-full h-48 object-cover rounded-lg hover:opacity-90 transition">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition rounded-lg flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition text-2xl"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
            <div class="space-y-4 sm:space-y-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                            <?php echo htmlspecialchars($website['category']); ?>
                        </span>
                        <?php if ($website['featured']): ?>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Featured</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($website['title']); ?></h1>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mb-6 space-y-2 sm:space-y-0">
                        <span class="text-3xl sm:text-4xl font-bold text-indigo-600"><?php echo formatPrice($website['price']); ?></span>
                        <span class="text-gray-500">One-time payment</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3">Description</h3>
                    <p class="text-gray-600 leading-relaxed text-sm sm:text-base"><?php echo nl2br(htmlspecialchars($website['description'])); ?></p>
                </div>

                <?php if ($website['features']): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Features</h3>
                        <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
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
                <div class="border-t pt-4 sm:pt-6">
                    <form action="add_to_cart.php" method="POST" class="space-y-4">
                        <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 sm:px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm sm:text-base">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Add to Cart
                            </button>
                            <?php if ($enable_wishlist === '1'): ?>
                            <button type="button" onclick="addToWishlist(<?php echo $website['id']; ?>)" class="flex-1 bg-pink-600 text-white px-4 sm:px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition text-sm sm:text-base">
                                <i class="fas fa-heart mr-2"></i>
                                Add to Wishlist
                            </button>
                            <?php endif; ?>
                            <a href="index.php" class="flex-1 bg-gray-200 text-gray-800 px-4 sm:px-6 py-3 rounded-lg font-semibold text-center hover:bg-gray-300 transition text-sm sm:text-base">
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

    <!-- Reviews Section -->
    <?php if ($enable_reviews === '1'): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-2xl font-bold mb-6">Customer Reviews</h3>
            
            <!-- Review Form -->
            <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold mb-4">Leave a Review</h4>
                <form id="reviewForm" class="space-y-4">
                    <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Very Good</option>
                            <option value="3">⭐⭐⭐ Good</option>
                            <option value="2">⭐⭐ Fair</option>
                            <option value="1">⭐ Poor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                        <textarea name="review" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Share your experience with this product..."></textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-star mr-2"></i>
                        Submit Review
                    </button>
                </form>
            </div>
            
            <!-- Reviews List -->
            <div class="space-y-4">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-star text-4xl mb-4"></i>
                    <p>Be the first to review this product!</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            <p class="mt-2 text-gray-400">Premium websites for your business needs</p>
        </div>
    </footer>
<script>
        function addToWishlist(websiteId) {
            // Simple wishlist functionality using localStorage
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            
            if (!wishlist.includes(websiteId)) {
                wishlist.push(websiteId);
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                alert('Added to wishlist!');
            } else {
                alert('Already in wishlist!');
            }
        }
        
        // Review form submission
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'submit_review');
            
            fetch('submit_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    this.reset();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error submitting review. Please try again.');
            });
        });
    </script>
    
    <!-- Screenshot Modal -->
    <div id="screenshotModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl max-h-[90vh] overflow-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold">Screenshot Preview</h3>
                <button onclick="closeScreenshotModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="Screenshot" class="w-full h-auto rounded-lg">
            </div>
            <div class="p-4 border-t bg-gray-50">
                <button onclick="closeScreenshotModal()" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function openScreenshotModal(imageSrc) {
            const modal = document.getElementById('screenshotModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeScreenshotModal() {
            const modal = document.getElementById('screenshotModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        document.getElementById('screenshotModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScreenshotModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeScreenshotModal();
            }
        });
    </script>
</body>
</html>
