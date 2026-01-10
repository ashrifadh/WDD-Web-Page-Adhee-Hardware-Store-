<?php
// DB connection
$host = '127.0.0.1';
$dbname = 'adhee_hardware';
$username = 'root';
$password = '';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->name, $data->category, $data->price, $data->image)) {
    $name = $data->name;
    $category = $data->category;
    $price = $data->price;
    $image = $data->image;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $image]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>
