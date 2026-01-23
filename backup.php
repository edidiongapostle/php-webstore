<?php
require_once 'config.php';
require_once 'functions.php';

function createBackup() {
    $backup_dir = __DIR__ . '/backups';
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    // Database backup
    $db_path = DB_PATH;
    $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.db';
    
    try {
        // Copy database file
        if (copy($db_path, $backup_file)) {
            // Create backup log
            $log_file = $backup_dir . '/backup_log.txt';
            $log_entry = date('Y-m-d H:i:s') . " - Database backup created: " . basename($backup_file) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
            
            // Clean old backups (keep last 10)
            $files = glob($backup_dir . '/backup_*.db');
            if (count($files) > 10) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $old_files = array_slice($files, 10);
                foreach ($old_files as $file) {
                    unlink($file);
                }
            }
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Backup failed: " . $e->getMessage());
        return false;
    }
    
    return false;
}

// Check if auto backup is enabled
$auto_backup = getSetting('auto_backup', '1');

if ($auto_backup === '1') {
    // Run backup if not run in last 24 hours
    $backup_file = __DIR__ . '/backups/last_backup.txt';
    $last_backup = file_exists($backup_file) ? file_get_contents($backup_file) : 0;
    $current_time = time();
    
    if ($current_time - $last_backup > 86400) { // 24 hours
        if (createBackup()) {
            file_put_contents($backup_file, $current_time);
            echo "Backup created successfully\n";
        } else {
            echo "Backup failed\n";
        }
    } else {
        echo "Backup already run recently\n";
    }
} else {
    echo "Auto backup is disabled\n";
}

// Manual backup trigger
if (isset($_GET['manual']) && $_GET['manual'] === '1') {
    if (createBackup()) {
        echo "Manual backup created successfully\n";
    } else {
        echo "Manual backup failed\n";
    }
}
?>
