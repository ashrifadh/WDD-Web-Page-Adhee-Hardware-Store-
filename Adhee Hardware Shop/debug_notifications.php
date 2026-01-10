<?php
/**
 * Debug Notifications - Find out why notifications aren't working
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Notifications</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:20px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
.btn{display:inline-block;padding:10px 20px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔍 Debug Notifications System</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Check 1: Notifications table
    echo "<h2>Step 1: Check Notifications Table</h2>";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
    if (count($tableCheck) > 0) {
        echo "<div class='success'>✓ Notifications table exists</div>";
        
        // Check table structure
        $columns = $conn->query("SHOW COLUMNS FROM notifications")->fetchAll();
        echo "<div class='info'><strong>Table columns:</strong> ";
        echo implode(', ', array_column($columns, 'Field'));
        echo "</div>";
    } else {
        echo "<div class='error'>✗ Notifications table does NOT exist</div>";
        echo "<div class='warning'><strong>FIX:</strong> <a href='setup_notifications.php' class='btn'>Create Table Now</a></div>";
    }
    
    // Check 2: User session
    echo "<h2>Step 2: Check User Session</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✓ User logged in: ID {$_SESSION['user_id']}, Username: " . ($_SESSION['username'] ?? 'N/A') . "</div>";
        $userId = $_SESSION['user_id'];
    } else {
        echo "<div class='error'>✗ User NOT logged in</div>";
        echo "<div class='info'>Please <a href='login.php'>login</a> first to test notifications</div>";
        $userId = null;
    }
    
    // Check 3: Test API endpoint
    echo "<h2>Step 3: Test API Endpoint</h2>";
    if ($userId) {
        echo "<div class='info'>Testing: <code>api/get_notifications.php</code></div>";
        $testUrl = 'api/get_notifications.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data) {
                echo "<div class='success'>✓ API is working</div>";
                echo "<div class='info'>Unread count: " . ($data['unread_count'] ?? 0) . "</div>";
                echo "<div class='info'>Total notifications: " . count($data['notifications'] ?? []) . "</div>";
                if (isset($data['error'])) {
                    echo "<div class='error'>API Error: " . $data['error'] . "</div>";
                }
            } else {
                echo "<div class='error'>✗ API returned invalid JSON</div>";
                echo "<div class='info'>Response: <code>" . htmlspecialchars($response) . "</code></div>";
            }
        } else {
            echo "<div class='error'>✗ API request failed</div>";
        }
    }
    
    // Check 4: Recent orders
    echo "<h2>Step 4: Check Recent Orders</h2>";
    if ($userId) {
        $orderStmt = $conn->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
        $orderStmt->execute([$userId]);
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($orders) > 0) {
            echo "<div class='success'>✓ Found " . count($orders) . " order(s)</div>";
            echo "<table>";
            echo "<tr><th>Order ID</th><th>Amount</th><th>Status</th><th>Date</th><th>Has Notification?</th></tr>";
            foreach($orders as $order) {
                if (count($tableCheck) > 0) {
                    $notifCheck = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE order_id = ?");
                    $notifCheck->execute([$order['id']]);
                    $notifCount = $notifCheck->fetch(PDO::FETCH_ASSOC)['count'];
                    $hasNotif = $notifCount > 0 ? '✓ Yes' : '✗ No';
                } else {
                    $hasNotif = 'N/A (table missing)';
                }
                echo "<tr>";
                echo "<td>#{$order['id']}</td>";
                echo "<td>RS {$order['total_amount']}</td>";
                echo "<td>{$order['status']}</td>";
                echo "<td>{$order['created_at']}</td>";
                echo "<td>{$hasNotif}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>No orders found for this user</div>";
        }
    }
    
    // Check 5: All notifications
    if ($userId && count($tableCheck) > 0) {
        echo "<h2>Step 5: All Notifications for This User</h2>";
        $notifStmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $notifStmt->execute([$userId]);
        $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        
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
            echo "<div class='info'>No notifications found</div>";
        }
    }
    
    // Check 6: Manual test - create notification
    if (isset($_GET['create_test']) && $userId && count($tableCheck) > 0) {
        echo "<h2>Step 6: Creating Test Notification</h2>";
        $testMsg = "Test notification - " . date('Y-m-d H:i:s');
        $testStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'test')");
        $testStmt->execute([$userId, $testMsg]);
        echo "<div class='success'>✓ Test notification created!</div>";
        echo "<div class='info'>Now check the bell icon on the home page</div>";
    } else if ($userId && count($tableCheck) > 0) {
        echo "<h2>Step 6: Create Test Notification</h2>";
        echo "<div class='info'><a href='?create_test=1' class='btn'>Create Test Notification</a></div>";
    }
    
    // Summary
    echo "<h2>Summary & Next Steps</h2>";
    $issues = [];
    if (count($tableCheck) === 0) {
        $issues[] = "Notifications table doesn't exist";
    }
    if (!$userId) {
        $issues[] = "User not logged in";
    }
    
    if (count($issues) > 0) {
        echo "<div class='error'><strong>Issues Found:</strong><ul>";
        foreach($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul></div>";
    } else {
        echo "<div class='success'><strong>✓ All checks passed!</strong></div>";
        echo "<div class='info'>If notifications still don't show:</div>";
        echo "<ol>";
        echo "<li>Make sure you're logged in as the customer who placed the order</li>";
        echo "<li>Check browser console for JavaScript errors (F12)</li>";
        echo "<li>Try creating a test notification above</li>";
        echo "<li>Refresh the home page after admin updates order status</li>";
        echo "</ol>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='Index.php' class='btn'>Home Page</a> <a href='setup_notifications.php' class='btn'>Setup Notifications</a> <a href='test_notifications.php' class='btn'>Test Page</a></p>";
echo "</div></body></html>";
?>

