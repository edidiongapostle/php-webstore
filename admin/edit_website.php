<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get website ID from URL
$website_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($website_id === 0) {
    header('Location: websites.php');
    exit;
}

// Get website details
$stmt = $conn->prepare("SELECT * FROM websites WHERE id = ?");
$stmt->execute([$website_id]);
$website = $stmt->fetch();

if (!$website) {
    header('Location: websites.php');
    exit;
}

$errors = [];
$success = false;

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
    
    // Handle main image upload
    $image_url = $website['image_url']; // Keep existing image by default
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Delete old image if exists
        if (!empty($website['image_url'])) {
            $old_image_path = __DIR__ . '/../' . $website['image_url'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        
        $file = $_FILES['main_image'];
        if ($file['size'] <= 5 * 1024 * 1024) { // 5MB limit
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                $filename = 'main_' . time() . '.' . $extension;
                $filepath = $upload_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $image_url = 'uploads/images/' . $filename;
                }
            }
        }
    }
    
    // Handle screenshot management
    $existing_screenshots = json_decode($website['screenshots'] ?? '[]', true) ?: [];
    
    // Remove checked screenshots
    if (isset($_POST['remove_screenshots']) && is_array($_POST['remove_screenshots'])) {
        foreach ($_POST['remove_screenshots'] as $index) {
            if (isset($existing_screenshots[$index])) {
                // Delete file from server
                $file_path = __DIR__ . '/../' . $existing_screenshots[$index];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                unset($existing_screenshots[$index]);
            }
        }
        // Re-index array
        $existing_screenshots = array_values($existing_screenshots);
    }
    
    // Handle new screenshot uploads
    if (isset($_FILES['screenshots']) && is_array($_FILES['screenshots']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/screenshots/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        foreach ($_FILES['screenshots']['name'] as $key => $name) {
            if (!empty($name) && $_FILES['screenshots']['error'][$key] == 0) {
                $file = [
                    'name' => $name,
                    'type' => $_FILES['screenshots']['type'][$key],
                    'tmp_name' => $_FILES['screenshots']['tmp_name'][$key],
                    'error' => $_FILES['screenshots']['error'][$key],
                    'size' => $_FILES['screenshots']['size'][$key]
                ];
                
                if ($file['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif'])) {
                        $filename = 'screenshot_' . time() . '_' . $key . '.' . $extension;
                        $filepath = $upload_dir . $filename;
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $existing_screenshots[] = 'uploads/screenshots/' . $filename;
                        }
                    }
                }
            }
        }
    }
    
    $screenshots_json = json_encode(array_values($existing_screenshots));
    
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
                UPDATE websites 
                SET title = ?, description = ?, price = ?, category = ?, image_url = ?, 
                    demo_url = ?, features = ?, technologies = ?, screenshots = ?, featured = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $price, $category, $image_url, $demo_url, $features, $technologies, $screenshots_json, $featured, $status, $website_id]);
            
            $success = true;
            
            // Clear form data
            $_POST = [];
            $_FILES = [];
            
            // Refresh website data
            $stmt = $conn->prepare("SELECT * FROM websites WHERE id = ?");
            $stmt->execute([$website_id]);
            $website = $stmt->fetch();
        } catch (Exception $e) {
            $errors['general'] = 'Failed to update website. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website - WebStore Admin</title>
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
                <a href="add_website.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-plus mr-3"></i>
                    Add Website
                </a>
                <a href="categories.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
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
                <h2 class="text-3xl font-bold">Edit Website</h2>
                <a href="websites.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Websites
                </a>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    Website updated successfully!
                </div>
            <?php endif; ?>

            <!-- Edit Website Form -->
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
                            <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? $website['title']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <?php if (isset($errors['title'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['title']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price ($) *</label>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? $website['price']); ?>" 
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
                                <?php 
                                try {
                                    $stmt = $conn->query("SELECT name FROM categories ORDER BY name ASC");
                                    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($categories as $cat) {
                                        $selected = (($_POST['category'] ?? $website['category']) === $cat) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($cat) . '" ' . $selected . '>' . htmlspecialchars($cat) . '</option>';
                                    }
                                } catch (Exception $e) {
                                    // Fallback to hardcoded categories if database fails
                                    $fallback_categories = ['E-Commerce', 'Portfolio', 'Blog', 'Restaurant', 'Real Estate', 'Education', 'Business', 'Other'];
                                    foreach ($fallback_categories as $cat) {
                                        $selected = (($_POST['category'] ?? $website['category']) === $cat) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($cat) . '" ' . $selected . '>' . htmlspecialchars($cat) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['category']; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <?php 
                                $statuses = ['active', 'inactive', 'sold'];
                                foreach ($statuses as $status_option) {
                                    $selected_status = ($_POST['status'] ?? $website['status']) === $status_option ? 'selected' : '';
                                    echo '<option value="' . $status_option . '" ' . $selected_status . '>' . ucfirst($status_option) . '</option>';
                                }
                                ?>
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
                                  placeholder="Describe website features, functionality, and target audience..."><?php echo htmlspecialchars($_POST['description'] ?? $website['description']); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $errors['description']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Main Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Main Image</label>
                            <?php if (!empty($website['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="../<?php echo htmlspecialchars($website['image_url']); ?>" alt="Current Main Image" class="h-20 w-20 object-cover rounded border">
                                    <p class="text-xs text-gray-500 mt-1">Current image</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="main_image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-sm text-gray-500 mt-1">Upload new main image (PNG, JPG, GIF - Max 5MB)</p>
                        </div>

                        <!-- Demo URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Demo URL</label>
                            <input type="url" name="demo_url" value="<?php echo htmlspecialchars($_POST['demo_url'] ?? $website['demo_url']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="https://demo.example.com">
                        </div>
                    </div>

                    <!-- Features -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
                        <textarea name="features" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="List key features separated by commas (e.g., Payment Integration, User Accounts, Admin Panel)"><?php echo htmlspecialchars($_POST['features'] ?? $website['features']); ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Separate features with commas</p>
                    </div>

                    <!-- Technologies -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                        <textarea name="technologies" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="List technologies separated by commas (e.g., PHP, MySQL, JavaScript, Bootstrap)"><?php echo htmlspecialchars($_POST['technologies'] ?? $website['technologies']); ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Separate technologies with commas</p>
                    </div>
                    
                    <!-- Screenshots -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Screenshots</label>
                        
                        <!-- Show existing screenshots -->
                        <?php 
                        $existing_screenshots = json_decode($website['screenshots'] ?? '[]', true) ?: [];
                        if (!empty($existing_screenshots)): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Current Screenshots:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <?php foreach ($existing_screenshots as $index => $screenshot): ?>
                                        <div class="relative">
                                            <?php 
                                            $image_path = '../' . $screenshot;
                                            $full_path = __DIR__ . '/../' . $screenshot;
                                            ?>
                                            <?php if (file_exists($full_path)): ?>
                                                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Screenshot <?php echo $index + 1; ?>" class="w-full h-20 object-cover rounded border">
                                            <?php else: ?>
                                                <div class="w-full h-20 bg-gray-200 rounded border flex items-center justify-center">
                                                    <span class="text-xs text-gray-500">Missing</span>
                                                </div>
                                            <?php endif; ?>
                                            <label class="flex items-center mt-1">
                                                <input type="checkbox" name="remove_screenshots[]" value="<?php echo $index; ?>" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                <span class="ml-2 text-xs text-red-600">Remove</span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Upload new screenshots -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">New Screenshot 1</label>
                                <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">New Screenshot 2</label>
                                <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">New Screenshot 3</label>
                                <input type="file" name="screenshots[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Upload up to 3 new screenshot images (PNG, JPG, GIF - Max 5MB each)</p>
                    </div>

                    <!-- Featured -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" value="1" <?php echo (isset($_POST['featured']) ? $_POST['featured'] : $website['featured']) ? 'checked' : ''; ?> 
                                   class="mr-2 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="text-sm font-medium text-gray-700">Featured Website</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-1">Featured websites appear prominently on homepage</p>
                    </div>

                    <!-- Website Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">Current Website Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Website ID:</span>
                                <span class="font-medium">#<?php echo str_pad($website['id'], 4, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium"><?php echo date('M j, Y', strtotime($website['created_at'])); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium"><?php echo date('M j, Y', strtotime($website['updated_at'])); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Current Status:</span>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $website['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo ucfirst($website['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="websites.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-save mr-2"></i>
                            Update Website
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
