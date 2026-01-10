<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("UPDATE products 
                            SET name = ?, category = ?, price = ?, stock_quantity = ?, image = ? 
                            WHERE id = ?");
    $stmt->execute([
        $data['name'],
        $data['category'],
        $data['price'],
        $data['stock_quantity'],
        $data['image'],
        $data['id']
    ]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
