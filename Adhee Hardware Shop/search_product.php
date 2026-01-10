<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'adhee_hardware';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $term = isset($_GET['term']) ? $_GET['term'] : '';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :term");
    $stmt->execute(['term' => '%' . $term . '%']);
    $products = $stmt->fetchAll();

    if ($products) {
        echo json_encode($products); // Return products as JSON array
    } else {
        echo json_encode([]); // Return an empty array if no products found
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>