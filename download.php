<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('HTTP/1.0 404 Not Found');
    echo 'Download not found.';
    exit;
}

// Get download record from database
$stmt = $conn->prepare("SELECT d.*, o.status, o.order_reference, o.total_amount FROM downloads d JOIN orders o ON d.order_id = o.id WHERE d.token = ?");
$stmt->execute([$token]);
$download = $stmt->fetch();

if (!$download) {
    header('HTTP/1.0 404 Not Found');
    echo 'Download not found or invalid token.';
    exit;
}

// Check if order is completed
if ($download['status'] !== 'completed') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Order not yet approved.';
    exit;
}

// Check download limit
if ($download['download_count'] >= $download['max_downloads']) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Download limit exceeded. You have already downloaded this file ' . $download['max_downloads'] . ' times.';
    exit;
}

// Check if expired
if ($download['expires_at'] && strtotime($download['expires_at']) < time()) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Download link has expired.';
    exit;
}

// Check if file exists in protected folder
$file_path = __DIR__ . '/protected_downloads/' . $download['file_path'];
if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found.';
    exit;
}

// Increment download count
$stmt = $conn->prepare("UPDATE downloads SET download_count = download_count + 1 WHERE id = ?");
$stmt->execute([$download['id']]);

// Send file to browser
$mime_type = mime_content_type($file_path);
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($file_path);
exit;
?>
