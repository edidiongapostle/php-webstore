<?php
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

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_category') {
        $category_name = sanitizeInput($_POST['category_name'] ?? '');
        
        if (empty($category_name)) {
            $errors['category_name'] = 'Category name is required';
        } else {
            // Check if category already exists
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$category_name]);
            if ($stmt->fetch()) {
                $errors['category_name'] = 'Category already exists';
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $conn->prepare("INSERT INTO categories (name, created_at) VALUES (?, CURRENT_TIMESTAMP)");
                $stmt->execute([$category_name]);
                $success = 'Category added successfully!';
            } catch (Exception $e) {
                $errors['general'] = 'Failed to add category';
            }
        }
    } elseif ($action === 'edit_category') {
        $category_id = intval($_POST['category_id'] ?? 0);
        $category_name = sanitizeInput($_POST['category_name'] ?? '');
        
        if ($category_id <= 0) {
            $errors['general'] = 'Invalid category ID';
        } elseif (empty($category_name)) {
            $errors['category_name'] = 'Category name is required';
        } else {
            // Check if category name already exists (excluding current category)
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$category_name, $category_id]);
            if ($stmt->fetch()) {
                $errors['category_name'] = 'Category already exists';
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$category_name, $category_id]);
                $success = 'Category updated successfully!';
            } catch (Exception $e) {
                $errors['general'] = 'Failed to update category';
            }
        }
    } elseif ($action === 'delete_category') {
        $category_id = intval($_POST['category_id'] ?? 0);
        
        if ($category_id <= 0) {
            $errors['general'] = 'Invalid category ID';
        } else {
            // Check if category is being used by any websites
            $stmt = $conn->prepare("SELECT COUNT(*) FROM websites WHERE category = (SELECT name FROM categories WHERE id = ?)");
            $stmt->execute([$category_id]);
            $website_count = $stmt->fetchColumn();
            
            if ($website_count > 0) {
                $errors['general'] = "Cannot delete category. It is being used by $website_count website(s).";
            } else {
                try {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$category_id]);
                    $success = 'Category deleted successfully!';
                } catch (Exception $e) {
                    $errors['general'] = 'Failed to delete category';
                }
            }
        }
    }
}

// Fetch all categories
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errors['general'] = 'Failed to fetch categories';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - WebStore Admin</title>
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
                <a href="categories.php" class="block px-4 py-3 text-indigo-600 bg-indigo-50">
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
                <h2 class="text-3xl font-bold">Manage Categories</h2>
                <a href="websites.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Websites
                </a>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Add Category Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Add New Category</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                            <input type="text" name="category_name" value="<?php echo htmlspecialchars($_POST['category_name'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="e.g., E-Commerce, Portfolio, Blog">
                            <?php if (isset($errors['category_name'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['category_name']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Add Category
                        </button>
                    </form>
                </div>

                <!-- Categories List -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Existing Categories</h3>
                    <div class="space-y-3">
                        <?php if (empty($categories)): ?>
                            <p class="text-gray-500 text-center py-4">No categories found. Add your first category!</p>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div>
                                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h4>
                                        <p class="text-sm text-gray-500">
                                            Created: <?php echo date('M j, Y', strtotime($category['created_at'])); ?>
                                            <?php if ($category['updated_at']): ?>
                                                • Updated: <?php echo date('M j, Y', strtotime($category['updated_at'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <!-- Edit Button -->
                                        <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" 
                                                class="text-blue-600 hover:text-blue-800 transition">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Delete Button -->
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" class="inline">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-xl font-semibold mb-4">Edit Category</h3>
                    <form method="POST" id="editForm">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="category_id" id="editCategoryId">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                            <input type="text" name="category_name" id="editCategoryName" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   required>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEditModal()" 
                                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                Update Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function editCategory(id, name) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
