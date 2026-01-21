<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get statistics
$total_websites = $conn->query("SELECT COUNT(*) as count FROM websites")->fetch()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch()['total'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Get recent websites
$recent_websites = $conn->query("SELECT * FROM websites ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WebStore</title>
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
                <a href="dashboard.php" class="block px-4 py-3 text-gray-700 bg-indigo-50 border-r-4 border-indigo-600">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="websites.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-globe mr-3"></i>
                    Websites
                </a>
                <a href="orders.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Orders
                </a>
                <a href="add_website.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-plus mr-3"></i>
                    Add Website
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <h2 class="text-3xl font-bold mb-8">Dashboard Overview</h2>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <i class="fas fa-globe text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Websites</p>
                            <p class="text-2xl font-bold"><?php echo $total_websites; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Orders</p>
                            <p class="text-2xl font-bold"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold"><?php echo formatPrice($total_revenue); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-clock text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Pending Orders</p>
                            <p class="text-2xl font-bold"><?php echo $pending_orders; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Recent Orders</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Order #</th>
                                    <th class="text-left py-2">Customer</th>
                                    <th class="text-left py-2">Total</th>
                                    <th class="text-left py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">No orders yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr class="border-b">
                                            <td class="py-2">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td class="py-2"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td class="py-2"><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td class="py-2">
                                                <span class="px-2 py-1 rounded-full text-xs <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-indigo-600 hover:text-indigo-800 text-sm">View all orders →</a>
                    </div>
                </div>

                <!-- Recent Websites -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Recent Websites</h3>
                    <div class="space-y-4">
                        <?php if (empty($recent_websites)): ?>
                            <p class="text-center text-gray-500 py-4">No websites added yet</p>
                        <?php else: ?>
                            <?php foreach ($recent_websites as $website): ?>
                                <div class="flex items-center space-x-4">
                                    <img src="<?php echo htmlspecialchars($website['image_url']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>" class="w-16 h-16 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-semibold"><?php echo htmlspecialchars($website['title']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo formatPrice($website['price']); ?></p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs <?php echo $website['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo ucfirst($website['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <a href="websites.php" class="text-indigo-600 hover:text-indigo-800 text-sm">View all websites →</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
