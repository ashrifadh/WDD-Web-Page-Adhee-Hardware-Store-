<?php
require_once '../db_config.php';

try {
    // Check what columns exist in orders table
    $columns = $conn->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_ASSOC);
    $hasCreatedAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'created_at') {
            $hasCreatedAt = true;
            break;
        }
    }
    
    // Build query based on available columns
    if ($hasCreatedAt) {
        $stmt = $conn->query("SELECT o.*, u.username 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC");
    } else {
        $stmt = $conn->query("SELECT o.*, u.username 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              ORDER BY o.id DESC");
    }
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default values for missing columns
    foreach ($orders as &$order) {
        if (!isset($order['created_at'])) {
            $order['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($order['updated_at'])) {
            $order['updated_at'] = date('Y-m-d H:i:s');
        }
    }
    
    echo json_encode($orders);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
