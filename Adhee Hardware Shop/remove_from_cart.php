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

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart_id']) || !is_numeric($data['cart_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid cart ID"]);
    exit;
}

$cart_id = (int)$data['cart_id'];

$query = "DELETE FROM cart WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cart_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Item removed from cart successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to remove item from cart"]);
}

$conn->close();
?>