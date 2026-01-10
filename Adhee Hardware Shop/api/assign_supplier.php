<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Remove existing assignment
    $stmt = $conn->prepare("DELETE FROM product_suppliers WHERE product_id = ?");
    $stmt->execute([$data['product_id']]);
    
    // Add new assignment
    $stmt = $conn->prepare("INSERT INTO product_suppliers (product_id, supplier_id) VALUES (?, ?)");
    $stmt->execute([$data['product_id'], $data['supplier_id']]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
