<?php
header('Content-Type: application/json');
require_once '../db_config.php';

try {
    // First check if supplier tables exist
    $tables = $conn->query("SHOW TABLES LIKE 'suppliers'")->fetchAll();
    
    if (count($tables) > 0) {
        // New version with suppliers
        try {
            $stmt = $conn->query("SELECT p.*, s.name as supplier_name 
                                  FROM products p 
                                  LEFT JOIN product_suppliers ps ON p.id = ps.product_id 
                                  LEFT JOIN suppliers s ON ps.supplier_id = s.id 
                                  ORDER BY p.id DESC");
        } catch(PDOException $e) {
            // If join fails, just get products
            $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
        }
    } else {
        // Fallback for basic products table only
        $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure stock_quantity and min_stock_level have default values
    foreach ($products as &$product) {
        if (!isset($product['stock_quantity']) || $product['stock_quantity'] === null) {
            $product['stock_quantity'] = 0;
        }
        if (!isset($product['min_stock_level']) || $product['min_stock_level'] === null) {
            $product['min_stock_level'] = 10;
        }
        if (!isset($product['supplier_name'])) {
            $product['supplier_name'] = null;
        }
    }
    
    echo json_encode($products);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
