<?php
// Database configuration
define('DB_PATH', __DIR__ . '/database/webstore.db');

// Create database connection
try {
    $conn = new PDO('sqlite:' . DB_PATH);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application settings - UPDATE THESE FOR YOUR ENVIRONMENT
define('SITE_NAME', 'WebStore');
define('SITE_EMAIL', 'admin@webstore.com');
define('CURRENCY', 'USD');

// Environment settings
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Error reporting - DISABLE IN PRODUCTION
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set timezone
date_default_timezone_set('UTC');

// Security settings
define('ENFORCE_HTTPS', false); // Set to true in production with HTTPS
define('SESSION_LIFETIME', 7200); // 2 hours in seconds

// Optional: Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            if (!defined(trim($key))) {
                define(trim($key), trim($value));
            }
        }
    }
}
?>
