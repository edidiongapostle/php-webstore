<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    if (empty($errors)) {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['login'] = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WebStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100">
                <i class="fas fa-lock text-indigo-600"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Admin Login</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Sign in to manage your website store
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php if (isset($errors['login'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $errors['login']; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['username'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['username']; ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['password'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Sign In
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Store
                </a>
            </div>
        </div>
        
        <div class="text-center text-sm text-gray-500">
            <p>Default credentials: admin / admin123</p>
        </div>
    </div>
</body>
</html>
