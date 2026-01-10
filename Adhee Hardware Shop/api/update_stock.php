<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    $stmt->execute([$data['stock_quantity'], $data['product_id']]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
