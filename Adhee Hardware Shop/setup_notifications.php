<?php
/**
 * Setup Notifications Table - Run this once to create the notifications table
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Notifications</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;} .error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;} .info{color:blue;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;} h1{color:#2a3f54;}</style></head><body>";
echo "<h1>🔔 Setup Notifications System</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
    
    if (count($tableCheck) > 0) {
        echo "<div class='info'>✓ Notifications table already exists</div>";
    } else {
        echo "<div class='info'>Creating notifications table...</div>";
        
        // Create table
        $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `order_id` int(11) DEFAULT NULL,
          `message` text NOT NULL,
          `type` varchar(50) DEFAULT 'order_update',
          `is_read` tinyint(1) DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        try {
            $conn->exec($sql);
            echo "<div class='success'>✓ Notifications table created successfully!</div>";
        } catch(PDOException $e) {
            // Try without foreign keys if they fail
            $sql2 = "CREATE TABLE IF NOT EXISTS `notifications` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `order_id` int(11) DEFAULT NULL,
              `message` text NOT NULL,
              `type` varchar(50) DEFAULT 'order_update',
              `is_read` tinyint(1) DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              KEY `order_id` (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            $conn->exec($sql2);
            echo "<div class='success'>✓ Notifications table created successfully! (without foreign keys)</div>";
        }
    }
    
    // Verify table structure
    $columns = $conn->query("SHOW COLUMNS FROM notifications")->fetchAll();
    echo "<div class='info'><strong>Table Structure:</strong><ul>";
    foreach($columns as $col) {
        echo "<li>{$col['Field']} ({$col['Type']})</li>";
    }
    echo "</ul></div>";
    
    echo "<div class='success'><h2>✅ Setup Complete!</h2>";
    echo "<p>Notifications system is now ready. When admin updates an order status, customers will receive notifications.</p>";
    echo "<p><a href='test_notifications.php' style='background:#f8b739;color:#2a3f54;padding:10px 20px;text-decoration:none;border-radius:5px;font-weight:bold;'>Test Notifications</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'><strong>Manual Fix:</strong> Run the SQL from create_notifications_table.sql in phpMyAdmin</div>";
}

echo "<hr>";
echo "<p><a href='Index.php'>Home Page</a> | <a href='admin_dashboard.php'>Admin Dashboard</a></p>";
echo "</body></html>";
?>

