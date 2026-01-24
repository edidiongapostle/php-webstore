<?php
require_once 'config.php';

try {
    // Create categories table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME
        )
    ");
    
    // Insert default categories
    $default_categories = ['E-Commerce', 'Portfolio', 'Blog', 'Restaurant', 'Real Estate', 'Education', 'Business', 'Other'];
    
    foreach ($default_categories as $category) {
        // Check if category already exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category]);
        
        if (!$stmt->fetch()) {
            // Insert category if it doesn't exist
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$category]);
        }
    }
    
    echo "Categories table created successfully with default categories!";
    
} catch (Exception $e) {
    echo "Error creating categories table: " . $e->getMessage();
}
?>
