<?php
/**
 * Fix Notifications - Complete diagnostic and auto-fix
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Notifications</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;font-size:16px;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;font-size:16px;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;font-size:16px;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;font-size:16px;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:20px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
.btn{display:inline-block;padding:12px 24px;background:#27ae60;color:white;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;font-size:16px;}
.btn:hover{background:#229954;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔔 Fix Notifications System</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Step 1: Create notifications table
    echo "<h2>Step 1: Creating Notifications Table</h2>";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
    
    if (count($tableCheck) > 0) {
        echo "<div class='success'>✓ Notifications table already exists</div>";
    } else {
        echo "<div class='warning'>⚠ Table doesn't exist. Creating it now...</div>";
        try {
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
            
            $conn->exec($sql);
            echo "<div class='success'>✓ Notifications table created successfully!</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>✗ Error creating table: " . $e->getMessage() . "</div>";
            echo "<div class='info'>Please run this SQL manually in phpMyAdmin:</div>";
            echo "<pre><code>$sql</code></pre>";
        }
    }
    
    // Step 2: Check user session
    echo "<h2>Step 2: Check User Session</h2>";
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'] ?? 'N/A';
        echo "<div class='success'>✓ User logged in: ID $userId, Username: $username</div>";
    } else {
        echo "<div class='error'>✗ User NOT logged in</div>";
        echo "<div class='info'>Please <a href='login.php'>login</a> first to test notifications</div>";
        $userId = null;
    }
    
    // Step 3: Test API
    if ($userId) {
        echo "<h2>Step 3: Test Notification API</h2>";
        $testStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
        $testStmt->execute([$userId]);
        $notifCount = $testStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<div class='info'>You have $notifCount notification(s) in database</div>";
        
        // Test API endpoint
        echo "<div class='info'>Testing API: <a href='api/get_notifications.php' target='_blank'>api/get_notifications.php</a></div>";
    }
    
    // Step 4: Create test notification
    if (isset($_GET['create_test']) && $userId) {
        echo "<h2>Step 4: Creating Test Notification</h2>";
        $testMsg = "Test notification - " . date('Y-m-d H:i:s');
        try {
            $testStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'test')");
            $testStmt->execute([$userId, $testMsg]);
            echo "<div class='success'>✓ Test notification created!</div>";
            echo "<div class='info'>Now go to <a href='Index.php'>Home Page</a> and check the bell icon</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
        }
    } else if ($userId) {
        echo "<h2>Step 4: Create Test Notification</h2>";
        echo "<div class='info'><a href='?create_test=1' class='btn'>Create Test Notification</a></div>";
    }
    
    // Step 5: Check recent orders and create notifications
    if ($userId) {
        echo "<h2>Step 5: Your Recent Orders</h2>";
        $orderStmt = $conn->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 10");
        $orderStmt->execute([$userId]);
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($orders) > 0) {
            echo "<table>";
            echo "<tr><th>Order ID</th><th>Amount</th><th>Status</th><th>Date</th><th>Notification</th><th>Action</th></tr>";
            foreach($orders as $order) {
                $notifCheck = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE order_id = ?");
                $notifCheck->execute([$order['id']]);
                $hasNotif = $notifCheck->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                
                echo "<tr>";
                echo "<td>#{$order['id']}</td>";
                echo "<td>RS {$order['total_amount']}</td>";
                echo "<td>{$order['status']}</td>";
                echo "<td>{$order['created_at']}</td>";
                echo "<td>" . ($hasNotif ? '✓ Yes' : '✗ No') . "</td>";
                echo "<td>";
                if (!$hasNotif) {
                    echo "<a href='?create_order_notif={$order['id']}' style='color:#27ae60;font-weight:bold;'>Create Notification</a>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>No orders found</div>";
        }
        
        // Create notification for specific order
        if (isset($_GET['create_order_notif'])) {
            $orderId = intval($_GET['create_order_notif']);
            $orderStmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
            $orderStmt->execute([$orderId, $userId]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orderData) {
                $status = strtolower($orderData['status']);
                $statusMessages = [
                    'pending' => 'Your order is pending',
                    'processing' => 'Your order is being processed',
                    'completed' => 'Your order has been completed! 🎉',
                    'cancelled' => 'Your order has been cancelled'
                ];
                
                $message = isset($statusMessages[$status]) 
                    ? $statusMessages[$status] 
                    : "Your order status: " . ucfirst($status);
                $message .= " (Order #$orderId)";
                
                $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, order_id, message, type) VALUES (?, ?, ?, 'order_update')");
                $notifStmt->execute([$userId, $orderId, $message]);
                
                echo "<div class='success'>✓ Notification created for Order #$orderId!</div>";
                echo "<div class='info'>Go to <a href='Index.php'>Home Page</a> and check the bell icon</div>";
            }
        }
    }
    
    // Step 6: Show all notifications
    if ($userId) {
        echo "<h2>Step 6: All Your Notifications</h2>";
        $notifStmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $notifStmt->execute([$userId]);
        $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($notifications) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Order ID</th><th>Message</th><th>Read</th><th>Created</th></tr>";
            foreach($notifications as $notif) {
                echo "<tr>";
                echo "<td>{$notif['id']}</td>";
                echo "<td>" . ($notif['order_id'] ?: 'N/A') . "</td>";
                echo "<td>{$notif['message']}</td>";
                echo "<td>" . ($notif['is_read'] ? 'Yes' : 'No') . "</td>";
                echo "<td>{$notif['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>No notifications found. Create a test notification above.</div>";
        }
    }
    
    // Summary
    echo "<h2>Summary</h2>";
    if (count($tableCheck) > 0 && $userId) {
        echo "<div class='success'>";
        echo "<h3>✅ Everything is set up!</h3>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Go to <a href='Index.php' style='color:#27ae60;font-weight:bold;'>Home Page</a></li>";
        echo "<li>Look for the bell icon in the header (next to search)</li>";
        echo "<li>Click the bell to see notifications</li>";
        echo "<li>When admin updates order status, you'll get a notification automatically</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='Index.php' class='btn'>Home Page</a> <a href='debug_notifications.php' class='btn'>Debug</a> <a href='admin_dashboard.php' class='btn'>Admin Dashboard</a></p>";
echo "</div></body></html>";
?>

