<?php
require_once '../db_config.php';

try {
    // Check if orders table exists
    $tables = $conn->query("SHOW TABLES LIKE 'orders'")->fetchAll();
    
    if (count($tables) > 0) {
        // With orders table
        $stmt = $conn->query("SELECT u.*, COUNT(o.id) as order_count 
                              FROM users u 
                              LEFT JOIN orders o ON u.id = o.user_id 
                              GROUP BY u.id 
                              ORDER BY u.id DESC");
    } else {
        // Without orders table
        $stmt = $conn->query("SELECT u.*, 0 as order_count 
                              FROM users u 
                              ORDER BY u.id DESC");
    }
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default values for missing columns
    foreach ($customers as &$customer) {
        if (!isset($customer['created_at'])) {
            $customer['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($customer['order_count'])) {
            $customer['order_count'] = 0;
        }
    }
    
    echo json_encode($customers);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
