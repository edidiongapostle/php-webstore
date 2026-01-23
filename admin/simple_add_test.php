<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
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
    $image_url = sanitizeInput($_POST['image_url'] ?? '');
    $demo_url = sanitizeInput($_POST['demo_url'] ?? '');
    $features = sanitizeInput($_POST['features'] ?? '');
    $technologies = sanitizeInput($_POST['technologies'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    // Simple screenshots handling - empty array for now
    $screenshots_json = json_encode([]);
    
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
    
    if (empty($image_url)) {
        $errors['image_url'] = 'Image URL is required';
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
            $result = $stmt->execute([$title, $description, $price, $category, $image_url, $demo_url, $features, $technologies, $screenshots_json, $featured, $status, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
            
            if ($result) {
                $success = true;
                $_POST = [];
            } else {
                $errors['general'] = 'Database insert failed.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'Failed to add website. Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Add Website Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto p-8">
        <h1 class="text-2xl font-bold mb-6">Simple Add Website Test</h1>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                Website added successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['general'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <?php if (isset($errors['title'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['title']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['description']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price ($) *</label>
                <input type="number" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                       step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <?php if (isset($errors['price'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['price']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Select a category</option>
                    <?php 
                    $categories = ['E-Commerce', 'Portfolio', 'Blog', 'Restaurant', 'Real Estate', 'Education', 'Business', 'Other'];
                    foreach ($categories as $cat) {
                        $selected = ($_POST['category'] ?? '') === $cat ? 'selected' : '';
                        echo '<option value="' . $cat . '" ' . $selected . '>' . $cat . '</option>';
                    }
                    ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['category']; ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Image URL *</label>
                <input type="url" name="image_url" value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                       placeholder="https://example.com/image.jpg">
                <?php if (isset($errors['image_url'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo $errors['image_url']; ?></p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Add Website
            </button>
        </form>
    </div>
</body>
</html>
