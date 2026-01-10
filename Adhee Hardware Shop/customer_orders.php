<?php
session_start();
require_once 'db_config.php';

// Check if viewing as admin or as logged-in customer
$customer_id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if (!$customer_id) {
    header('Location: login.php');
    exit();
}

// Get customer info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get orders - check if created_at column exists
try {
    $columns = $conn->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_ASSOC);
    $hasCreatedAt = false;
    $hasUpdatedAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'created_at') $hasCreatedAt = true;
        if ($column['Field'] === 'updated_at') $hasUpdatedAt = true;
    }
    
    if ($hasCreatedAt) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    }
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $orders = [];
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo htmlspecialchars($customer['username']); ?></title>
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
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }

        .header h1 {
            color: #2c3e50;
        }

        .btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .order-card {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .order-id {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .order-date {
            color: #7f8c8d;
            font-size: 14px;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
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

        .view-details-btn {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .view-details-btn:hover {
            background: #2980b9;
        }

        .no-orders {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .no-orders i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-shopping-bag"></i> Order History</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Customer: <?php echo htmlspecialchars($customer['username']); ?></p>
            </div>
            <a href="<?php echo isset($_GET['id']) ? 'admin_dashboard.php' : 'Index.php'; ?>" class="btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <i class="fas fa-shopping-cart"></i>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date">
                                <i class="fas fa-calendar"></i> 
                                <?php 
                                if (isset($order['created_at'])) {
                                    echo date('F j, Y, g:i a', strtotime($order['created_at']));
                                } else {
                                    echo 'Order #' . $order['id'];
                                }
                                ?>
                            </div>
                        </div>
                        <span class="badge badge-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label">Total Amount</span>
                            <span class="detail-value">RS <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Last Updated</span>
                            <span class="detail-value"><?php 
                            if (isset($order['updated_at'])) {
                                echo date('M j, Y', strtotime($order['updated_at']));
                            } else {
                                echo 'N/A';
                            }
                            ?></span>
                        </div>
                    </div>

                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-details-btn">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
