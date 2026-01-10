<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in through ADMIN login (separate from customer login)
if (!isset($_SESSION['admin_user_id']) || !isset($_SESSION['admin_login'])) {
    // Not logged in as admin, redirect to admin login
    header('Location: admin_login.php');
    exit();
}

// User is logged in as admin - allow access
// Use admin session variables, not regular user session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Adhee Hardware</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #2a3f54;
            --primary-light: #3a5068;
            --accent-yellow: #f8b739;
            --accent-light: #f8f9fa;
            --danger-color: #d62839;
            --success-color: #06d6a0;
            --gray-color: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--accent-light);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--primary-dark);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--primary-light);
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
            color: var(--accent-yellow);
        }

        .sidebar-header p {
            font-size: 14px;
            color: #ecf0f1;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            padding: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-light);
            border-left: 4px solid var(--accent-yellow);
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }

        .top-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 4px solid var(--accent-yellow);
        }

        .top-bar h1 {
            color: var(--primary-dark);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-top: 4px solid var(--accent-yellow);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: var(--accent-yellow);
            opacity: 0.1;
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .stat-card h3 {
            color: var(--gray-color);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-dark);
        }

        .stat-card i {
            float: right;
            font-size: 40px;
            color: var(--accent-yellow);
            opacity: 0.5;
        }

        .content-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none;
            border-top: 4px solid var(--accent-yellow);
        }

        .content-section.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-light);
        }

        .section-header h2 {
            color: var(--primary-dark);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-primary {
            background: var(--primary-dark);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-yellow);
            color: var(--primary-dark);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #05c090;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c01f2e;
        }

        .btn-warning {
            background: var(--accent-yellow);
            color: var(--primary-dark);
        }

        .btn-warning:hover {
            background: #ffc233;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-light);
        }

        table th {
            background: var(--primary-dark);
            font-weight: 600;
            color: var(--accent-yellow);
        }

        table tr:hover {
            background: rgba(248, 183, 57, 0.1);
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border: 1px solid var(--accent-light);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f8f9fa;
            padding: 20px;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
            min-height: 40px;
        }

        .product-id {
            font-size: 11px;
            color: var(--gray-color);
            background: var(--accent-light);
            padding: 3px 8px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 8px;
        }

        .product-category {
            display: inline-block;
            padding: 4px 10px;
            background: var(--accent-yellow);
            color: var(--primary-dark);
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 10px;
            margin-left: 5px;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .product-details {
            background: var(--accent-light);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .product-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .product-detail-row:last-child {
            border-bottom: none;
        }

        .product-detail-label {
            color: var(--gray-color);
            font-weight: 500;
        }

        .product-detail-value {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .product-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px;
            background: var(--accent-light);
            border-radius: 5px;
        }

        .product-stock span {
            font-size: 13px;
            color: var(--gray-color);
        }

        .product-supplier {
            font-size: 13px;
            color: var(--gray-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            padding: 8px;
            background: var(--accent-light);
            border-radius: 5px;
        }

        .product-supplier i {
            margin-right: 5px;
            color: var(--accent-yellow);
        }

        .product-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .product-actions .btn {
            width: 100%;
            padding: 8px;
            font-size: 12px;
        }

        .product-actions .btn-view {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #2a3f54 0%, #3a5068 100%);
        }

        .product-actions .btn-view:hover {
            background: linear-gradient(135deg, #f8b739 0%, #ffc233 100%);
            color: #2a3f54;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Custom Modal Styles */
        .custom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
            z-index: 10001;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .modal-header i {
            margin-right: 10px;
            color: var(--accent-yellow);
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px 25px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-yellow);
            background: white;
            box-shadow: 0 0 0 3px rgba(248, 183, 57, 0.1);
        }

        .form-group input:required {
            border-left: 4px solid var(--accent-yellow);
        }

        .modal-footer {
            background: #f8f9fa;
            padding: 20px 25px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            border-top: 1px solid #e1e8ed;
        }

        .modal-footer .btn {
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .modal-footer .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-container {
                width: 95%;
                margin: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .modal-footer {
                padding: 15px 20px;
                flex-direction: column;
            }
        }

        /* Notification Animations */
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        /* Status Update Modal Styles */
        .status-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .status-modal-overlay.show {
            display: flex;
        }

        .status-modal {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            animation: modalSlideIn 0.3s ease;
        }

        .status-modal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-modal-header h3 i {
            color: var(--accent-yellow);
        }

        .status-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .status-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .status-modal-body {
            padding: 30px 25px;
        }

        .status-modal-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
        }

        .status-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        .status-option {
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 15px;
            font-weight: 500;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-option:hover {
            border-color: var(--accent-yellow);
            background: rgba(248, 183, 57, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(248, 183, 57, 0.2);
        }

        .status-option.selected {
            border-color: var(--accent-yellow);
            background: var(--accent-yellow);
            color: var(--primary-dark);
            box-shadow: 0 4px 15px rgba(248, 183, 57, 0.4);
        }

        .status-option i {
            font-size: 18px;
        }

        .status-input-group {
            margin-top: 20px;
        }

        .status-input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-color);
            margin-bottom: 10px;
        }

        .status-input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .status-input-group input:focus {
            outline: none;
            border-color: var(--accent-yellow);
            box-shadow: 0 0 0 3px rgba(248, 183, 57, 0.1);
        }

        .status-modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-status {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-cancel {
            background: #f8f9fa;
            color: var(--gray-color);
        }

        .btn-cancel:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-confirm {
            background: var(--accent-yellow);
            color: var(--primary-dark);
        }

        .btn-confirm:hover {
            background: #e6a730;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(248, 183, 57, 0.4);
        }

        .btn-confirm:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 768px) {
            .status-modal {
                width: 95%;
                margin: 20px;
            }

            .status-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-tools"></i> Adhee Hardware</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#" class="menu-link active" data-section="overview"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#" class="menu-link" data-section="products"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="#" class="menu-link" data-section="suppliers"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li><a href="#" class="menu-link" data-section="customers"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="#" class="menu-link" data-section="orders"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="Index.php"><i class="fas fa-store"></i> View Store</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Admin Dashboard</h1>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="color: var(--primary-dark);">
                        <i class="fas fa-user-circle"></i> 
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                    </span>
                    <a href="logout.php" style="color: var(--danger-color); text-decoration: none; font-weight: 500;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Overview Section -->
            <div id="overview" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-box"></i>
                        <h3>Total Products</h3>
                        <div class="value" id="totalProducts">0</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-truck"></i>
                        <h3>Suppliers</h3>
                        <div class="value" id="totalSuppliers">0</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Customers</h3>
                        <div class="value" id="totalCustomers">0</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Total Orders</h3>
                        <div class="value" id="totalOrders">0</div>
                    </div>
                </div>

                <div class="section-header">
                    <h2>Low Stock Alert</h2>
                </div>
                <table id="lowStockTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Products Section -->
            <div id="products" class="content-section">
                <div class="section-header">
                    <h2>Product Management</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="productSearch" placeholder="Search products..." style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 250px;">
                        <select id="categoryFilter" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                            <option value="">All Categories</option>
                            <option value="Tools">Tools</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Paint">Paint</option>
                            <option value="Building Materials">Building Materials</option>
                            <option value="Safety Equipment">Safety Equipment</option>
                            <option value="Outdoor">Outdoor</option>
                        </select>
                        <button class="btn btn-primary" type="button" onclick="toggleAddProductForm()">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <!-- Inline Add Product Form -->
                <div id="addProductContainer" style="margin-top: 20px; display: none; background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid #e1e8ed;">
                    <h3 style="margin-bottom: 15px; color: var(--primary-dark);">
                        <i class="fas fa-plus-circle" style="color: var(--accent-yellow);"></i>
                        Add New Product
                    </h3>
                    <p style="margin-bottom: 15px; color: var(--gray-color); font-size: 13px;">
                        Fill in the details below and click <strong>Add Product</strong> to save it to your store.
                    </p>
                    <form id="addProductForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productName">Product Name *</label>
                                <input type="text" id="productName" required>
                            </div>
                            <div class="form-group">
                                <label for="productCategory">Category *</label>
                                <input type="text" id="productCategory" list="productCategoryOptions" required>
                                <datalist id="productCategoryOptions">
                                    <option value="Tools">
                                    <option value="Plumbing">
                                    <option value="Electrical">
                                    <option value="Hardware">
                                    <option value="Paint">
                                    <option value="Building Materials">
                                    <option value="Safety Equipment">
                                    <option value="Outdoor">
                                </datalist>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="productPrice">Price (RS) *</label>
                                <input type="number" id="productPrice" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="productStock">Initial Stock Quantity</label>
                                <input type="number" id="productStock" min="0" value="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="productImage">Image URL</label>
                                <input type="text" id="productImage" placeholder="https://...">
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                            <button type="button" class="btn btn-secondary" onclick="toggleAddProductForm()" style="background: #6c757d; color: white;">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" id="addProductSubmit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Product
                            </button>
                        </div>
                    </form>
                </div>

                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here -->
                </div>
            </div>

            <!-- Suppliers Section -->
            <div id="suppliers" class="content-section">
                <div class="section-header">
                    <h2>Supplier Management</h2>
                    <button class="btn btn-primary" onclick="showAddSupplierModal()"><i class="fas fa-plus"></i> Add Supplier</button>
                </div>
                <table id="suppliersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Custom Supplier Modal -->
            <div id="supplierModal" class="custom-modal" style="display: none;">
                <div class="modal-overlay"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h3><i class="fas fa-truck"></i> Add New Supplier</h3>
                        <button class="modal-close" onclick="closeSupplierModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="supplierForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="supplierName">Supplier Name *</label>
                                    <input type="text" id="supplierName" required>
                                </div>
                                <div class="form-group">
                                    <label for="companyName">Company Name</label>
                                    <input type="text" id="companyName">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactPerson">Contact Person</label>
                                    <input type="text" id="contactPerson">
                                </div>
                                <div class="form-group">
                                    <label for="supplierEmail">Email</label>
                                    <input type="email" id="supplierEmail">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="supplierPhone">Phone</label>
                                    <input type="tel" id="supplierPhone">
                                </div>
                                <div class="form-group">
                                    <label for="supplierAddress">Address</label>
                                    <input type="text" id="supplierAddress">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="closeSupplierModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="btn btn-primary" onclick="saveSupplier()">
                            <i class="fas fa-save"></i> Save Supplier
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customers Section -->
            <div id="customers" class="content-section">
                <div class="section-header">
                    <h2>Customer Management</h2>
                </div>
                <table id="customersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Total Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <div class="section-header">
                    <h2>Order Management</h2>
                </div>
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Product Details Modal -->
    <div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto;">
        <div style="max-width: 800px; margin: 50px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
            <button onclick="closeProductModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            <div id="productModalContent"></div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="status-modal-overlay">
        <div class="status-modal">
            <div class="status-modal-header">
                <h3><i class="fas fa-edit"></i> Update Order Status</h3>
                <button class="status-modal-close" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="status-modal-body">
                <p class="status-modal-label">Select new status:</p>
                <div class="status-options">
                    <button class="status-option" data-status="pending" onclick="selectStatus('pending')">
                        <i class="fas fa-clock"></i> Pending
                    </button>
                    <button class="status-option" data-status="processing" onclick="selectStatus('processing')">
                        <i class="fas fa-cog"></i> Processing
                    </button>
                    <button class="status-option" data-status="completed" onclick="selectStatus('completed')">
                        <i class="fas fa-check-circle"></i> Completed
                    </button>
                    <button class="status-option" data-status="cancelled" onclick="selectStatus('cancelled')">
                        <i class="fas fa-times-circle"></i> Cancelled
                    </button>
                </div>
                <div class="status-input-group">
                    <label for="statusInput">Or enter custom status:</label>
                    <input type="text" id="statusInput" placeholder="Enter status..." autocomplete="off">
                </div>
            </div>
            <div class="status-modal-footer">
                <button class="btn-status btn-cancel" onclick="closeStatusModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-status btn-confirm" onclick="confirmStatusUpdate()">
                    <i class="fas fa-check"></i> Update Status
                </button>
            </div>
        </div>
    </div>

    <script src="admin_dashboard.js"></script>
</body>
</html>
