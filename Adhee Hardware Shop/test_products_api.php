<?php
// Test if products API works
require_once 'db_config.php';

echo "<h2>Testing Products API</h2>";

try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Success! Found " . count($products) . " products</h3>";
    echo "<pre>";
    print_r($products);
    echo "</pre>";
    
    echo "<h3>JSON Output:</h3>";
    echo "<pre>";
    echo json_encode($products, JSON_PRETTY_PRINT);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
