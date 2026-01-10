<?php
require_once '../db_config.php';

try {
    $stats = [];
    
    // Total products
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $stmt->fetch()['count'];
    
    // Total suppliers (check if table exists)
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM suppliers");
        $stats['suppliers'] = $stmt->fetch()['count'];
    } catch(PDOException $e) {
        $stats['suppliers'] = 0;
    }
    
    // Total customers
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' OR role IS NULL");
        $stats['customers'] = $stmt->fetch()['count'];
    } catch(PDOException $e) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['customers'] = $stmt->fetch()['count'];
    }
    
    // Total orders (check if table exists)
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders'] = $stmt->fetch()['count'];
    } catch(PDOException $e) {
        $stats['orders'] = 0;
    }
    
    echo json_encode($stats);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
