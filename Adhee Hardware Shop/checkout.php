<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Adhee Hardware</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
            border-radius: 5px;
            padding: 5px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .item-price {
            color: #7f8c8d;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .summary-row.total {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: none;
            margin-top: 10px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn:hover {
            background: #229954;
        }

        .btn-secondary {
            background: #3498db;
        }

        .btn-secondary:hover {
            background: #2980b9;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .empty-cart i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Custom Modal Styles */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(42, 63, 84, 0.85);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .custom-modal-overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .custom-modal {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
            overflow: hidden;
        }

        .custom-modal-header {
            background: linear-gradient(135deg, #2a3f54 0%, #1d2d3d 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .custom-modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #f8b739;
        }

        .custom-modal-header h3 i {
            font-size: 1.3rem;
        }

        .custom-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            transition: transform 0.3s ease, color 0.3s ease;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .custom-modal-close:hover {
            color: #f8b739;
            transform: rotate(90deg);
            background: rgba(255, 255, 255, 0.1);
        }

        .custom-modal-body {
            padding: 30px;
            text-align: center;
        }

        .custom-modal-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .custom-modal-icon.success {
            color: #27ae60;
        }

        .custom-modal-icon.error {
            color: #e74c3c;
        }

        .custom-modal-icon.question {
            color: #f8b739;
        }

        .custom-modal-message {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .custom-modal-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .custom-modal-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-modal-btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .custom-modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .custom-modal-btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .custom-modal-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }

        .custom-modal-btn-cancel {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
        }

        .custom-modal-btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.4);
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .custom-modal {
                max-width: 95%;
                margin: 20px;
            }

            .custom-modal-header {
                padding: 20px;
            }

            .custom-modal-header h3 {
                font-size: 1.2rem;
            }

            .custom-modal-body {
                padding: 25px 20px;
            }

            .custom-modal-footer {
                padding: 15px 20px;
                flex-direction: column-reverse;
            }

            .custom-modal-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>

        <?php if (empty($cart_items)): ?>
            <div class="card">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Add some products to your cart before checking out.</p>
                    <a href="Index.php" class="btn btn-secondary" style="display: inline-block; width: auto; padding: 10px 20px; margin-top: 20px; text-decoration: none;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="checkout-grid">
                <div class="card">
                    <h2>Order Items</h2>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-price">
                                    RS <?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div style="font-weight: bold; color: #2c3e50;">
                                RS <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RS <?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>RS <?php echo number_format($total, 2); ?></span>
                    </div>

                    <button class="btn" onclick="placeOrder()">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                    <a href="Index.php" class="btn btn-secondary" style="text-decoration: none; display: block; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Custom Confirmation Modal -->
    <div class="custom-modal-overlay" id="confirmModal">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3><i class="fas fa-question-circle"></i> Confirm Order</h3>
                <button class="custom-modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="custom-modal-body">
                <div class="custom-modal-icon question">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="custom-modal-message">
                    Are you sure you want to place this order?<br>
                    <strong>Total: RS <?php echo number_format($total, 2); ?></strong>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="custom-modal-btn custom-modal-btn-cancel" onclick="closeConfirmModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="custom-modal-btn custom-modal-btn-primary" onclick="confirmPlaceOrder()">
                    <i class="fas fa-check"></i> Confirm Order
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Success/Error Modal -->
    <div class="custom-modal-overlay" id="messageModal">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3 id="messageModalTitle"><i class="fas fa-check-circle"></i> Success</h3>
                <button class="custom-modal-close" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="custom-modal-body">
                <div class="custom-modal-icon" id="messageModalIcon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="custom-modal-message" id="messageModalText">
                    Order placed successfully!
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="custom-modal-btn custom-modal-btn-primary" onclick="closeMessageModal()" id="messageModalBtn">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>

    <script>
        let isProcessingOrder = false;

        function placeOrder() {
            if (isProcessingOrder) return;
            showConfirmModal();
        }

        function showConfirmModal() {
            document.getElementById('confirmModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function confirmPlaceOrder() {
            closeConfirmModal();
            
            // Disable button and show loading state
            const btn = document.querySelector('.btn[onclick="placeOrder()"]');
            if (btn) {
                btn.classList.add('loading');
                btn.disabled = true;
            }
            isProcessingOrder = true;

            fetch('api/place_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessageModal('success', 'Order Placed Successfully!', 'Your order has been placed successfully. You will be redirected to your orders page.', true);
                } else {
                    showMessageModal('error', 'Order Failed', data.error || 'Failed to place order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageModal('error', 'Connection Error', 'Failed to place order. Please check your connection and try again.');
            })
            .finally(() => {
                // Re-enable button
                if (btn) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
                isProcessingOrder = false;
            });
        }

        function showMessageModal(type, title, message, redirect = false) {
            const modal = document.getElementById('messageModal');
            const titleEl = document.getElementById('messageModalTitle');
            const iconEl = document.getElementById('messageModalIcon');
            const messageEl = document.getElementById('messageModalText');
            const btnEl = document.getElementById('messageModalBtn');

            // Remove previous icon classes
            iconEl.className = 'custom-modal-icon';
            iconEl.innerHTML = '';

            if (type === 'success') {
                iconEl.classList.add('success');
                iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
                titleEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + title;
                btnEl.innerHTML = '<i class="fas fa-check"></i> OK';
                btnEl.className = 'custom-modal-btn custom-modal-btn-primary';
                messageEl.innerHTML = message + '<br><small style="color: #7f8c8d; margin-top: 10px; display: block;">Redirecting to your orders...</small>';
            } else {
                iconEl.classList.add('error');
                iconEl.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                titleEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + title;
                btnEl.innerHTML = '<i class="fas fa-times"></i> Close';
                btnEl.className = 'custom-modal-btn custom-modal-btn-secondary';
                messageEl.innerHTML = message;
            }

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            // Store redirect flag in button
            btnEl.dataset.redirect = redirect ? 'true' : 'false';
        }

        function closeMessageModal() {
            const modal = document.getElementById('messageModal');
            const btnEl = document.getElementById('messageModalBtn');
            const shouldRedirect = btnEl.dataset.redirect === 'true';
            
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';

            if (shouldRedirect) {
                setTimeout(() => {
                    window.location.href = 'customer_orders.php';
                }, 300);
            }
        }

        // Close modals when clicking outside
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
                closeMessageModal();
            }
        });
    </script>
</body>
</html>
