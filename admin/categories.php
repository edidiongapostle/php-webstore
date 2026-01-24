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

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    
    if (empty($name)) {
        $errors['name'] = 'Category name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Category name must be less than 100 characters';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = 'Category created successfully!';
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $errors['name'] = 'Category name already exists';
            } else {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $category_id = intval($_POST['category_id'] ?? 0);
    
    if ($category_id > 0) {
        try {
            // Check if category is being used by any websites
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM websites WHERE category = (SELECT name FROM categories WHERE id = ?)");
            $stmt->execute([$category_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                $errors['delete'] = 'Cannot delete category. It is being used by ' . $count . ' website(s).';
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $success = 'Category deleted successfully!';
            }
        } catch (Exception $e) {
            $errors['delete'] = 'Error deleting category: ' . $e->getMessage();
        }
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
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
                <a href="categories.php" class="block px-4 py-3 text-gray-700 bg-indigo-50 border-r-4 border-indigo-600">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="add_website.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-plus mr-3"></i>
                    Add Website
                </a>
                <a href="orders.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Orders
                </a>
                <a href="settings.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Category Management</h2>
                <button onclick="showAddCategoryModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Add Category
                </button>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($errors['delete'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $errors['delete']; ?>
                </div>
            <?php endif; ?>

            <!-- Categories List -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4">All Categories</h3>
                
                <?php if (empty($categories)): ?>
                    <p class="text-center text-gray-500 py-8">No categories found. Add your first category!</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4">Category Name</th>
                                    <th class="text-left py-3 px-4">Description</th>
                                    <th class="text-left py-3 px-4">Website Count</th>
                                    <th class="text-left py-3 px-4">Created At</th>
                                    <th class="text-center py-3 px-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    // Count websites in this category
                                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM websites WHERE category = ?");
                                    $stmt->execute([$category['name']]);
                                    $website_count = $stmt->fetch()['count'];
                                    ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                                <?php echo $website_count; ?> website(s)
                                            </span>
                                        </td>
                                        <td class="py-3 px-4"><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($website_count == 0): ?>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" class="inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-400">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Cannot Delete
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add New Category</h3>
                <button onclick="hideAddCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                    <input type="text" name="name" maxlength="100" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g., E-Commerce">
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Brief description of this category..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideAddCategoryModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }
        
        function hideAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('addCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddCategoryModal();
            }
        });
    </script>
</body>
</html>
