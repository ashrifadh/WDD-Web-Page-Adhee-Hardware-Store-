<?php
require_once '../db_config.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    echo json_encode(['error' => 'Please provide a valid order ID']);
    exit();
}

try {
    // Get order details
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['error' => 'Order not found. Please check your order ID.']);
        exit();
    }
    
    // Get order items count
    $stmt = $conn->prepare("SELECT COUNT(*) as item_count FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $itemCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $order['item_count'] = $itemCount['item_count'];
    
    echo json_encode($order);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
