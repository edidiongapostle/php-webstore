<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_id = sanitizeInput($_POST['website_id'] ?? '');
    $rating = sanitizeInput($_POST['rating'] ?? '');
    $review = sanitizeInput($_POST['review'] ?? '');
    
    // Validation
    if (empty($website_id) || empty($rating) || empty($review)) {
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit;
    }
    
    if (!in_array($rating, ['1', '2', '3', '4', '5'])) {
        $response['message'] = 'Invalid rating';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($review) < 10) {
        $response['message'] = 'Review must be at least 10 characters long';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Create reviews table if it doesn't exist
        $conn->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            website_id INTEGER NOT NULL,
            rating INTEGER NOT NULL,
            review TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (website_id) REFERENCES websites (id)
        )");
        
        // Insert review
        $stmt = $conn->prepare("INSERT INTO reviews (website_id, rating, review) VALUES (?, ?, ?)");
        $stmt->execute([$website_id, $rating, $review]);
        
        $response['success'] = true;
        $response['message'] = 'Review submitted successfully!';
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
