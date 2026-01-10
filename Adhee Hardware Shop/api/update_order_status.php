<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Get order details to find customer
    $orderStmt = $conn->prepare("SELECT user_id, total_amount FROM orders WHERE id = ?");
    $orderStmt->execute([$data['order_id']]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['order_id']]);
        
        // Create notification for customer
        try {
            // Check if notifications table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
            if (count($tableCheck) > 0) {
                // Normalize status (handle "complete" and "completed")
                $status = strtolower(trim($data['status']));
                if ($status === 'complete') {
                    $status = 'completed';
                }
                
                $statusMessages = [
                    'pending' => 'Your order is pending',
                    'processing' => 'Your order is being processed',
                    'completed' => 'Your order has been completed! 🎉',
                    'cancelled' => 'Your order has been cancelled'
                ];
                
                $message = isset($statusMessages[$status]) 
                    ? $statusMessages[$status] 
                    : "Your order status has been updated to: " . ucfirst($status);
                
                $message .= " (Order #{$data['order_id']})";
                
                // Insert notification
                $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, order_id, message, type) VALUES (?, ?, ?, 'order_update')");
                $notifStmt->execute([$order['user_id'], $data['order_id'], $message]);
                
                // Log success (for debugging)
                error_log("Notification created for user {$order['user_id']}, order {$data['order_id']}, status: {$status}");
            } else {
                // Table doesn't exist - try to create it
                try {
                    $conn->exec("CREATE TABLE IF NOT EXISTS `notifications` (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
                    
                    // Now create the notification
                    $status = strtolower(trim($data['status']));
                    if ($status === 'complete') {
                        $status = 'completed';
                    }
                    
                    $statusMessages = [
                        'pending' => 'Your order is pending',
                        'processing' => 'Your order is being processed',
                        'completed' => 'Your order has been completed! 🎉',
                        'cancelled' => 'Your order has been cancelled'
                    ];
                    
                    $message = isset($statusMessages[$status]) 
                        ? $statusMessages[$status] 
                        : "Your order status has been updated to: " . ucfirst($status);
                    
                    $message .= " (Order #{$data['order_id']})";
                    
                    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, order_id, message, type) VALUES (?, ?, ?, 'order_update')");
                    $notifStmt->execute([$order['user_id'], $data['order_id'], $message]);
                    
                    error_log("Notifications table created and notification added for user {$order['user_id']}");
                } catch(PDOException $e2) {
                    error_log("Failed to create notifications table: " . $e2->getMessage());
                }
            }
        } catch(PDOException $e) {
            // Log error but don't fail the status update
            error_log("Error creating notification: " . $e->getMessage());
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
