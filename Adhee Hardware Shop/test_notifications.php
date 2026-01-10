<?php
/**
 * Test Notifications - Check if everything is working
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Notifications</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;} .error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;} .info{color:blue;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{padding:10px;border:1px solid #ddd;} th{background:#2a3f54;color:#f8b739;}</style></head><body>";
echo "<h1>Notification System Test</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Check 1: Notifications table exists
    echo "<h2>Step 1: Check Notifications Table</h2>";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
    if (count($tableCheck) > 0) {
        echo "<div class='success'>✓ Notifications table exists</div>";
    } else {
        echo "<div class='error'>✗ Notifications table does NOT exist</div>";
        echo "<div class='info'><strong>Fix:</strong> Run this SQL in phpMyAdmin:</div>";
        echo "<pre>";
        readfile('create_notifications_table.sql');
        echo "</pre>";
    }
    
    // Check 2: Check if user is logged in
    echo "<h2>Step 2: Check User Session</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✓ User logged in: User ID {$_SESSION['user_id']}</div>";
        echo "<div class='info'>Username: " . ($_SESSION['username'] ?? 'Not set') . "</div>";
    } else {
        echo "<div class='error'>✗ User not logged in</div>";
        echo "<div class='info'>Please login first: <a href='login.php'>Login</a></div>";
    }
    
    // Check 3: Get notifications
    if (isset($_SESSION['user_id']) && count($tableCheck) > 0) {
        echo "<h2>Step 3: Check Notifications</h2>";
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($notifications) > 0) {
            echo "<div class='success'>✓ Found " . count($notifications) . " notification(s)</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Order ID</th><th>Message</th><th>Read</th><th>Created</th></tr>";
            foreach($notifications as $notif) {
                echo "<tr>";
                echo "<td>{$notif['id']}</td>";
                echo "<td>{$notif['order_id']}</td>";
                echo "<td>{$notif['message']}</td>";
                echo "<td>" . ($notif['is_read'] ? 'Yes' : 'No') . "</td>";
                echo "<td>{$notif['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>No notifications found for this user</div>";
        }
        
        // Check 4: Test API endpoint
        echo "<h2>Step 4: Test API Endpoint</h2>";
        echo "<div class='info'>Testing: <a href='api/get_notifications.php' target='_blank'>api/get_notifications.php</a></div>";
    }
    
    // Check 5: Recent orders
    echo "<h2>Step 5: Recent Orders</h2>";
    if (isset($_SESSION['user_id'])) {
        $orderStmt = $conn->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
        $orderStmt->execute([$_SESSION['user_id']]);
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($orders) > 0) {
            echo "<table>";
            echo "<tr><th>Order ID</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
            foreach($orders as $order) {
                echo "<tr>";
                echo "<td>#{$order['id']}</td>";
                echo "<td>RS {$order['total_amount']}</td>";
                echo "<td>{$order['status']}</td>";
                echo "<td>{$order['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>No orders found</div>";
        }
    }
    
    // Manual test: Create notification
    if (isset($_GET['create_test'])) {
        echo "<h2>Step 6: Create Test Notification</h2>";
        if (isset($_SESSION['user_id'])) {
            $testMsg = "Test notification created at " . date('Y-m-d H:i:s');
            $testStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'test')");
            $testStmt->execute([$_SESSION['user_id'], $testMsg]);
            echo "<div class='success'>✓ Test notification created!</div>";
            echo "<div class='info'>Refresh the page to see it</div>";
        }
    } else {
        echo "<h2>Step 6: Create Test Notification</h2>";
        echo "<div class='info'><a href='?create_test=1'>Click here to create a test notification</a></div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='Index.php'>Home Page</a> | <a href='login.php'>Login</a></p>";
echo "</body></html>";
?>

