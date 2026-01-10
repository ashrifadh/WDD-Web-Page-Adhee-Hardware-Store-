<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $conn->beginTransaction();
    
    // Get cart items
    $stmt = $conn->prepare("SELECT c.*, p.price, p.stock_quantity 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        // Check stock only if stock_quantity column exists
        if (isset($item['stock_quantity']) && $item['stock_quantity'] !== null) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception('Insufficient stock for product ID: ' . $item['product_id']);
            }
        }
        $total += $item['price'] * $item['quantity'];
    }
    
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $total]);
    $order_id = $conn->lastInsertId();
    
    // Add order items and update stock
    foreach ($cart_items as $item) {
        // Check if order_items table has subtotal column
        $columns = $conn->query("SHOW COLUMNS FROM order_items LIKE 'subtotal'")->fetchAll();
        
        if (count($columns) > 0) {
            // New version with subtotal
            $subtotal = $item['price'] * $item['quantity'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal]);
        } else {
            // Fallback without subtotal
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        
        // Update product stock (only if stock_quantity column exists)
        $stockColumns = $conn->query("SHOW COLUMNS FROM products LIKE 'stock_quantity'")->fetchAll();
        if (count($stockColumns) > 0) {
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
    }
    
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
} catch(Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
