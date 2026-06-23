<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded extensions:\n";
print_r(get_loaded_extensions());
echo "\n\nTesting SQLite connection:\n";
try {
    $conn = new PDO('sqlite:database/webstore.db');
    echo "SQLite connection successful!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
