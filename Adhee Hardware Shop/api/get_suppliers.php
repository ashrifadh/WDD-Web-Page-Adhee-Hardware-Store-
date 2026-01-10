<?php
require_once '../db_config.php';

try {
    $stmt = $conn->query("SELECT * FROM suppliers ORDER BY name ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($suppliers);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
