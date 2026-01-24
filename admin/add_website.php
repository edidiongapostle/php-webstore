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

// Get categories from database
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? '');
    $demo_url = sanitizeInput($_POST['demo_url'] ?? '');
    $features = sanitizeInput($_POST['features'] ?? '');
    $technologies = sanitizeInput($_POST['technologies'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $image_source = sanitizeInput($_POST['image_source'] ?? 'url');
    $screenshot_source = sanitizeInput($_POST['screenshot_source'] ?? 'upload');
    
    // Handle main image
    $image_url = '';
    if ($image_source === 'url') {
        $image_url = sanitizeInput($_POST['image_url'] ?? '');
        if (empty($image_url)) {
            $errors['image_url'] = 'Image URL is required when using URL option';
        }
    } else {
        // Handle main image upload
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['main_image'];
            if ($file['size'] <= 5 * 1024 * 1024) { // 5MB limit
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                    $upload_dir = __DIR__ . '/../uploads/images/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $filename = 'main_' . time() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $image_url = 'uploads/images/' . $filename;
                    } else {
                        $errors['main_image'] = 'Failed to upload main image';
                    }
                } else {
                    $errors['main_image'] = 'Invalid file type. Only PNG, JPG, GIF allowed';
                }
            } else {
                $errors['main_image'] = 'File too large. Maximum size is 5MB';
            }
        } else {
            $errors['main_image'] = 'Main image is required when using upload option';
        }
    }
    
    // Handle screenshots
    $screenshots = [];
    if ($screenshot_source === 'url') {
        // Handle screenshot URLs
        if (isset($_POST['screenshot_urls']) && is_array($_POST['screenshot_urls'])) {
            foreach ($_POST['screenshot_urls'] as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $screenshots[] = $url;
                    } else {
                        $errors['screenshot_urls'] = 'Invalid screenshot URL: ' . $url;
                    }
                }
            }
        }
    } else {
        // Handle screenshot uploads
        if (isset($_FILES['screenshots']) && is_array($_FILES['screenshots']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/screenshots/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach ($_FILES['screenshots']['name'] as $key => $name) {
                if (!empty($name) && $_FILES['screenshots']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_size = $_FILES['screenshots']['size'][$key];
                    $file_tmp = $_FILES['screenshots']['tmp_name'][$key];
                    
                    if ($file_size <= 5 * 1024 * 1024) { // 5MB limit
                        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                            $filename = 'screenshot_' . time() . '_' . $key . '.' . $extension;
                            $filepath = $upload_dir . $filename;
                            if (move_uploaded_file($file_tmp, $filepath)) {
                                $screenshots[] = 'uploads/screenshots/' . $filename;
                            }
                        }
                    }
                }
            }
        }
    }
    
    $screenshots_json = json_encode($screenshots);
    
    // Validation
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    if ($price <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }
    
    if (empty($category)) {
        $errors['category'] = 'Category is required';
    }
    
    if (!in_array($status, ['active', 'inactive', 'sold'])) {
        $errors['status'] = 'Invalid status';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO websites (title, description, price, category, image_url, demo_url, features, technologies, screenshots, featured, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $price, $category, $image_url, $demo_url, $features, $technologies, $screenshots_json, $featured, $status, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
            
            $success = true;
            
            // Clear form data
            $_POST = [];
            $_FILES = [];
        } catch (Exception $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Website - WebStore Admin</title>
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
                <a href="add_website.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-plus mr-3"></i>
                    Add Website
                </a>
                <a href="settings.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
                <a href="orders.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Orders
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Add New Website</h2>
                <a href="websites.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Websites
                </a>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    Website added successfully! <a href="websites.php" class="font-semibold">View all websites</a>
                </div>
            <?php endif; ?>

            <!-- Add Website Form -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <?php if (isset($errors['title'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['title']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price ($) *</label>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                   step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <?php if (isset($errors['price'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['price']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo (($_POST['category'] ?? '') === $cat['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['category']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active" <?php echo (($_POST['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="sold" <?php echo (($_POST['status'] ?? '') === 'sold') ? 'selected' : ''; ?>>Sold</option>
                            </select>
                            <?php if (isset($errors['status'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['status']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Describe the website features, functionality, and target audience..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $errors['description']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Main Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Main Image *</label>
                        
                        <!-- Image Source Selection -->
                        <div class="mb-4">
                            <label class="flex items-center mb-2">
                                <input type="radio" name="image_source" value="url" checked onchange="toggleImageInput('url')" class="mr-2">
                                <span class="text-sm">Use Image URL</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="image_source" value="upload" onchange="toggleImageInput('upload')" class="mr-2">
                                <span class="text-sm">Upload Image</span>
                            </label>
                        </div>
                        
                        <!-- URL Input -->
                        <div id="imageUrlInput">
                            <input type="url" name="image_url" value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="https://example.com/image.jpg">
                            <?php if (isset($errors['image_url'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['image_url']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- File Upload -->
                        <div id="imageUploadInput" class="hidden">
                            <input type="file" name="main_image" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Upload a main image for the website (PNG, JPG, GIF - Max 5MB)</p>
                            <?php if (isset($errors['main_image'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['main_image']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Demo URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Demo URL</label>
                        <input type="url" name="demo_url" value="<?php echo htmlspecialchars($_POST['demo_url'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="https://demo.example.com">
                    </div>
                    </div>

                    <!-- Features -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
                        <textarea name="features" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="List key features separated by commas (e.g., Payment Integration, User Accounts, Admin Panel)"><?php echo htmlspecialchars($_POST['features'] ?? ''); ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Separate features with commas</p>
                    </div>

                    <!-- Technologies -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                        <textarea name="technologies" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="List technologies separated by commas (e.g., PHP, MySQL, JavaScript, Bootstrap)"><?php echo htmlspecialchars($_POST['technologies'] ?? ''); ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Separate technologies with commas</p>
                    </div>
                    
                    <!-- Screenshots -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Screenshots</label>
                        
                        <!-- Screenshot Source Selection -->
                        <div class="mb-4">
                            <label class="flex items-center mb-2">
                                <input type="radio" name="screenshot_source" value="upload" checked onchange="toggleScreenshotInput('upload')" class="mr-2">
                                <span class="text-sm">Upload Screenshots</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="screenshot_source" value="url" onchange="toggleScreenshotInput('url')" class="mr-2">
                                <span class="text-sm">Use Screenshot URLs</span>
                            </label>
                        </div>
                        
                        <!-- File Upload -->
                        <div id="screenshotUploadInput">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot 1</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot 2</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot 3</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot 4</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot 5</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Upload up to 5 screenshots (PNG, JPG, GIF - Max 5MB each)</p>
                        </div>
                        
                        <!-- URL Input -->
                        <div id="screenshotUrlInput" class="hidden">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot URL 1</label>
                                    <input type="url" name="screenshot_urls[]" placeholder="https://example.com/screenshot1.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot URL 2</label>
                                    <input type="url" name="screenshot_urls[]" placeholder="https://example.com/screenshot2.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot URL 3</label>
                                    <input type="url" name="screenshot_urls[]" placeholder="https://example.com/screenshot3.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot URL 4</label>
                                    <input type="url" name="screenshot_urls[]" placeholder="https://example.com/screenshot4.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Screenshot URL 5</label>
                                    <input type="url" name="screenshot_urls[]" placeholder="https://example.com/screenshot5.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Enter up to 5 screenshot URLs (one per line)</p>
                        </div>
                    </div>

                    <!-- Featured -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?> 
                                   class="mr-2 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="text-sm font-medium text-gray-700">Featured Website</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-1">Featured websites appear prominently on the homepage</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="websites.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Add Website
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

<script>
function toggleImageInput(source) {
    const urlInput = document.getElementById('imageUrlInput');
    const uploadInput = document.getElementById('imageUploadInput');
    
    if (source === 'url') {
        urlInput.classList.remove('hidden');
        uploadInput.classList.add('hidden');
    } else {
        urlInput.classList.add('hidden');
        uploadInput.classList.remove('hidden');
    }
}

function toggleScreenshotInput(source) {
    const uploadInput = document.getElementById('screenshotUploadInput');
    const urlInput = document.getElementById('screenshotUrlInput');
    
    if (source === 'upload') {
        uploadInput.classList.remove('hidden');
        urlInput.classList.add('hidden');
    } else {
        uploadInput.classList.add('hidden');
        urlInput.classList.remove('hidden');
    }
}

// Set initial state based on radio buttons
document.addEventListener('DOMContentLoaded', function() {
    const imageSource = document.querySelector('input[name="image_source"]:checked').value;
    const screenshotSource = document.querySelector('input[name="screenshot_source"]:checked').value;
    
    toggleImageInput(imageSource);
    toggleScreenshotInput(screenshotSource);
});
</script>
</html>
