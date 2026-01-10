<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$host = '127.0.0.1';
$dbname = 'adhee_hardware';
$username = 'root';
$password = '';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->id) && isset($data->name) && isset($data->category) && isset($data->price) && isset($data->image)) {
    $id = $data->id;
    $name = $data->name;
    $category = $data->category;
    $price = $data->price;
    $image = $data->image;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, image = ? WHERE id = ?");
        $result = $stmt->execute([$name, $category, $price, $image, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or product not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>
