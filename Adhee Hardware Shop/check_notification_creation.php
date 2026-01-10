<?php
/**
 * Check if notifications are being created when order status is updated
 */
require_once 'db_config.php';

echo "<h2>Check Notification Creation</h2>";

// Get recent order status updates
$orders = $conn->query("SELECT id, user_id, status, updated_at FROM orders ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Recent Orders:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Order ID</th><th>User ID</th><th>Status</th><th>Updated</th><th>Has Notification?</th></tr>";

foreach($orders as $order) {
    // Check if notification exists
    $notifCheck = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE order_id = ?");
    $notifCheck->execute([$order['id']]);
    $notifCount = $notifCheck->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<tr>";
    echo "<td>#{$order['id']}</td>";
    echo "<td>{$order['user_id']}</td>";
    echo "<td>{$order['status']}</td>";
    echo "<td>{$order['updated_at']}</td>";
    echo "<td>" . ($notifCount > 0 ? "✓ Yes ($notifCount)" : "✗ No") . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test creating a notification
if (isset($_GET['test_create'])) {
    $orderId = intval($_GET['test_create']);
    $order = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
    $order->execute([$orderId]);
    $orderData = $order->fetch(PDO::FETCH_ASSOC);
    
    if ($orderData) {
        $message = "Test notification for order #$orderId";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, order_id, message, type) VALUES (?, ?, ?, 'order_update')");
        $notif->execute([$orderData['user_id'], $orderId, $message]);
        echo "<div style='color:green;'>Test notification created!</div>";
    }
}

echo "<hr>";
echo "<p><a href='debug_notifications.php'>Debug Notifications</a> | <a href='setup_notifications.php'>Setup Table</a></p>";
?>

