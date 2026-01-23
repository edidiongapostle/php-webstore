<?php
require_once 'config.php';

try {
    // Add screenshots column to websites table
    $conn->exec("ALTER TABLE websites ADD COLUMN screenshots TEXT");
    
    echo "Screenshots column added successfully to websites table.\n";
} catch (Exception $e) {
    echo "Error adding screenshots column: " . $e->getMessage() . "\n";
}
?>
