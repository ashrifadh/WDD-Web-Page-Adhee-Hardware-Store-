<?php
require_once '../db_config.php';

try {
    $stmt = $conn->query("SELECT id, name, category, stock_quantity, min_stock_level 
                          FROM products 
                          WHERE stock_quantity < min_stock_level 
                          ORDER BY stock_quantity ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
