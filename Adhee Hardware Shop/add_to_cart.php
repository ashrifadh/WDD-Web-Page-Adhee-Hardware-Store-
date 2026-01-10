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

// Check if request is POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get data from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid product ID"]);
        exit;
    }
    
    $product_id = (int)$data['product_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    
    // If user is logged in, use user_id, otherwise use session_id
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $session_id = null;
    } else {
        $user_id = null;
        // Create session ID if it doesn't exist
        if (!isset($_SESSION['session_id'])) {
            $_SESSION['session_id'] = session_id();
        }
        $session_id = $_SESSION['session_id'];
    }
    
    // Check if product exists in cart already
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND session_id = ?");
        $stmt->bind_param("is", $product_id, $session_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Product exists in cart, update quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        
        if ($user_id) {
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ? AND user_id = ?");
            $update->bind_param("iii", $new_quantity, $product_id, $user_id);
        } else {
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ? AND session_id = ?");
            $update->bind_param("iis", $new_quantity, $product_id, $session_id);
        }
        
        if ($update->execute()) {
            echo json_encode(["success" => true, "message" => "Cart updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update cart"]);
        }
    } else {
        // Product doesn't exist in cart, add it
        if ($user_id) {
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $user_id, $product_id, $quantity);
        } else {
            $insert = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("sii", $session_id, $product_id, $quantity);
        }
        
        // After adding or updating the product in the cart
        if ($insert->execute()) {
            // Fetch product name for the response
            $product_stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product_name = $product_result->fetch_assoc()['name'];

            echo json_encode(["success" => true, "message" => "$product_name added to cart"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add product to cart"]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
