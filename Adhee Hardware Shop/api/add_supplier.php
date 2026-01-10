<?php
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("INSERT INTO suppliers (name, email, phone, address, company_name, contact_person) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['email'] ?? null,
        $data['phone'] ?? null,
        $data['address'] ?? null,
        $data['company_name'] ?? null,
        $data['contact_person'] ?? null
    ]);
    
    echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
