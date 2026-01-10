<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Check if stock columns exist
    $columns = $conn->query("SHOW COLUMNS FROM products LIKE 'stock_quantity'")->fetchAll();
    
    if (count($columns) > 0) {
        // New version with stock columns
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock_quantity, min_stock_level, image) 
                                VALUES (?, ?, ?, ?, 10, ?)");
        $stmt->execute([
            $data['name'],
            $data['category'],
            $data['price'],
            $data['stock_quantity'] ?? 0,
            $data['image']
        ]);
    } else {
        // Fallback for basic products table
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, image) 
                                VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['category'],
            $data['price'],
            $data['image']
        ]);
    }
    
    echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
