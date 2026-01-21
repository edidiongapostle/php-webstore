<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle update order status
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitizeInput($_POST['status']);
    
    if (in_array($status, ['pending', 'completed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        header('Location: orders.php?updated=1');
        exit;
    }
}

// Get all orders with item counts
$orders = $conn->query("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - WebStore Admin</title>
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
                <a href="orders.php" class="block px-4 py-3 text-gray-700 bg-indigo-50 border-r-4 border-indigo-600">
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
            <h2 class="text-3xl font-bold mb-8">Manage Orders</h2>

            <!-- Messages -->
            <?php if (isset($_GET['updated'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    Order status updated successfully!
                </div>
            <?php endif; ?>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                                        <p>No orders found yet.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($order['customer_name']); ?>
                                                <?php if ($order['anonymous_checkout']): ?>
                                                    <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Anonymous</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($order['customer_phone']): ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">
                                                <?php echo htmlspecialchars($order['customer_email']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $order['item_count']; ?> item(s)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo formatPrice($order['total_amount']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1 <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="text-green-600 hover:text-green-900" title="Contact Customer">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <i class="fas fa-shopping-bag text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Orders</p>
                            <p class="text-2xl font-bold"><?php echo count($orders); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Pending Orders</p>
                            <p class="text-2xl font-bold"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Completed Orders</p>
                            <p class="text-2xl font-bold"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Cancelled Orders</p>
                            <p class="text-2xl font-bold"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'cancelled')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Summary -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Revenue Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue (All Orders)</p>
                        <p class="text-2xl font-bold text-indigo-600">
                            <?php 
                            $total_revenue = array_sum(array_column($orders, 'total_amount'));
                            echo formatPrice($total_revenue);
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Completed Revenue</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?php 
                            $completed_revenue = array_sum(array_column(array_filter($orders, fn($o) => $o['status'] === 'completed'), 'total_amount'));
                            echo formatPrice($completed_revenue);
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pending Revenue</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            <?php 
                            $pending_revenue = array_sum(array_column(array_filter($orders, fn($o) => $o['status'] === 'pending'), 'total_amount'));
                            echo formatPrice($pending_revenue);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
