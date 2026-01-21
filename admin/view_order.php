<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header('Location: orders.php');
    exit;
}

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - WebStore Admin</title>
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
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Order Details</h2>
                <a href="orders.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Orders
                </a>
            </div>

            <!-- Order Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Number:</span>
                                <span class="font-medium">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Date:</span>
                                <span class="font-medium"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <?php if ($order['payment_method']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium">
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                    <?php if ($order['anonymous_checkout']): ?>
                                        <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Anonymous</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="font-medium text-indigo-600 hover:text-indigo-800">
                                    <?php echo htmlspecialchars($order['customer_email']); ?>
                                </a>
                            </div>
                            <?php if ($order['customer_phone']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="text-sm text-gray-500">ID: #<?php echo str_pad($item['website_id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo formatPrice($item['price']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">Total:</td>
                                <td class="px-6 py-4 text-sm font-bold text-indigo-600">
                                    <?php echo formatPrice($order['total_amount']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Order Notes -->
            <?php if ($order['notes']): ?>
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Order Notes</h3>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Actions</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Customer
                    </a>
                    
                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="POST" action="orders.php" class="inline-block">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="status" value="completed">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                                <i class="fas fa-check mr-2"></i>
                                Mark as Completed
                            </button>
                        </form>
                        
                        <form method="POST" action="orders.php" class="inline-block">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times mr-2"></i>
                                Cancel Order
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                        <i class="fas fa-print mr-2"></i>
                        Print Order
                    </button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
