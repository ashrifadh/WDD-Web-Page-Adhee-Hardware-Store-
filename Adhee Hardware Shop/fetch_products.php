<?php
$host = 'localhost'; 
$db = 'adhee_hardware'; 
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4'; 

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Prepare and execute the SQL query
    $stmt = $pdo->query("SELECT * FROM products"); // Ensure 'products' is the correct table name
    $products = $stmt->fetchAll();

    // Return the products as a JSON response
    echo json_encode($products);

} catch (\PDOException $e) {
    // Return an error message if the connection fails
    echo json_encode(['error' => $e->getMessage()]);
}
?>