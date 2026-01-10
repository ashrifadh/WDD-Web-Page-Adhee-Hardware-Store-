<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "adhee_hardware";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// If user is logged in, use user_id, otherwise use session_id
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
} else {
    // Create session ID if it doesn't exist
    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = session_id();
    }
    $session_id = $_SESSION['session_id'];
    
    $query = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.session_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $session_id);
}

// Execute the statement and check for errors
if (!$stmt->execute()) {
    die(json_encode(["success" => false, "message" => "Query execution failed: " . $stmt->error]));
}

$result = $stmt->get_result();
$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $item_total = $row['price'] * $row['quantity'];
    $total += $item_total;
    
    $cart_items[] = [
        'id' => $row['id'],
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image' => $row['image'],
        'item_total' => $item_total
    ];
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total' => $total,
    'count' => count($cart_items)
]);

$conn->close();
?>