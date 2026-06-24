<?php
require_once 'config.php';

// Add anonymous_checkout column to orders table
try {
    $conn->exec("ALTER TABLE orders ADD COLUMN anonymous_checkout INTEGER DEFAULT 0");
    echo "Successfully added anonymous_checkout column to orders table.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'duplicate column') !== false) {
        echo "Column anonymous_checkout already exists in orders table.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}
?>
