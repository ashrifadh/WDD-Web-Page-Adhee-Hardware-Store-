<?php
session_start();
require_once 'db_config.php';

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    header('Location: Index.php');
    exit();
}

// Fetch order + customer
$stmt = $conn->prepare(
    "SELECT o.*, u.username, u.email 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = ?"
);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: Index.php');
    exit();
}

// Fetch order items with product details
$stmt = $conn->prepare(
    "SELECT oi.*, p.name, p.category, p.image 
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?"
);
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for status badge class
function getStatusClass($status)
{
    switch ($status) {
        case 'completed':
            return 'badge-completed';
        case 'processing':
            return 'badge-processing';
        case 'cancelled':
            return 'badge-cancelled';
        default:
            return 'badge-pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['id']); ?> Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        .header-title {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
        }
        .header-subtitle {
            font-size: 14px;
            color: #7f8c8d;
        }
        .btn {
            padding: 10px 18px;
            background: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .meta-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .meta-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        .badge-processing {
            background: #cce5ff;
            color: #004085;
        }
        .badge-completed {
            background: #d4edda;
            color: #155724;
        }
        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: middle;
        }
        .items-table th {
            background: #2c3e50;
            color: #f8b739;
            font-weight: 600;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #ecf0f1;
            padding: 5px;
        }
        .product-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .product-category {
            font-size: 12px;
            color: #7f8c8d;
        }
        .summary {
            display: flex;
            justify-content: flex-end;
        }
        .summary-card {
            min-width: 260px;
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
            margin-top: 10px;
        }
        .summary-label {
            margin-right: 10px;
        }
        .summary-value {
            font-weight: 600;
        }
        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .summary {
                justify-content: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-receipt"></i> Order #<?php echo htmlspecialchars($order['id']); ?> Details</h1>
                <div class="header-subtitle">
                    Customer: <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)
                </div>
            </div>
            <div>
                <?php
                // If opened from admin (customer_orders.php uses ?id=...), go back accordingly
                $backUrl = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin_dashboard.php') !== false
                    ? 'admin_dashboard.php'
                    : (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'
                        ? 'admin_dashboard.php'
                        : 'customer_orders.php');
                ?>
                <a href="<?php echo htmlspecialchars($backUrl); ?>" class="btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="order-meta">
            <div class="meta-card">
                <div class="meta-label">ORDER ID</div>
                <div class="meta-value">#<?php echo htmlspecialchars($order['id']); ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">ORDER DATE</div>
                <div class="meta-value">
                    <?php 
                    if (isset($order['created_at']) && $order['created_at']) {
                        echo date('F j, Y, g:i a', strtotime($order['created_at']));
                    } else {
                        echo 'Order #' . $order['id'];
                    }
                    ?>
                </div>
            </div>
            <div class="meta-card">
                <div class="meta-label">STATUS</div>
                <div class="meta-value">
                    <span class="badge <?php echo getStatusClass($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>
            <div class="meta-card">
                <div class="meta-label">LAST UPDATED</div>
                <div class="meta-value">
                    <?php 
                    if (isset($order['updated_at']) && $order['updated_at']) {
                        echo date('F j, Y, g:i a', strtotime($order['updated_at']));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
        </div>

        <h2 style="margin-bottom: 12px; color: #2c3e50;">
            <i class="fas fa-boxes"></i> Ordered Products
        </h2>

        <?php if (empty($items)): ?>
            <p style="padding: 30px; text-align: center; color: #7f8c8d;">
                No items found for this order.
            </p>
        <?php else: ?>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price (RS)</th>
                        <th>Quantity</th>
                        <th>Subtotal (RS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $calculatedTotal = 0;
                    foreach ($items as $item):
                        // Calculate subtotal if it doesn't exist in the database
                        $subtotal = isset($item['subtotal']) && $item['subtotal'] !== null 
                            ? (float)$item['subtotal'] 
                            : (float)$item['price'] * (int)$item['quantity'];
                        $calculatedTotal += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img
                                        src="<?php echo htmlspecialchars($item['image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        class="product-img"
                                        onerror="this.src='https://via.placeholder.com/60?text=No+Image';"
                                    >
                                    <div>
                                        <div class="product-name">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                        <div class="product-category">
                                            ID: <?php echo (int)$item['product_id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo number_format((float)$item['price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-card">
                    <div class="summary-row">
                        <div class="summary-label">Items Total</div>
                        <div class="summary-value">RS <?php echo number_format($calculatedTotal, 2); ?></div>
                    </div>
                    <div class="summary-row total">
                        <div class="summary-label">Order Total</div>
                        <div class="summary-value">RS <?php echo number_format((float)$order['total_amount'], 2); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>










