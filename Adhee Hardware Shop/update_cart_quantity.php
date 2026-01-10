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

if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

if (!isset($data['quantity_change']) || !is_numeric($data['quantity_change'])) {
    echo json_encode(["success" => false, "message" => "Invalid quantity change"]);
    exit;
}

$product_id = (int)$data['product_id'];
$quantity_change = (int)$data['quantity_change'];

// If user is logged in, use user_id, otherwise use session_id
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "UPDATE cart SET quantity = quantity + ? WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $quantity_change, $product_id, $user_id);
} else {
    // Create session ID if it doesn't exist
    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = session_id();
    }
    $session_id = $_SESSION['session_id'];
    
    $query = "UPDATE cart SET quantity = quantity + ? WHERE product_id = ? AND session_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $quantity_change, $product_id, $session_id);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Quantity updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update quantity"]);
}

$conn->close();
?>