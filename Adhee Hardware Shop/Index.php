
<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Home page is for customers only
// Admin sessions are separate - they don't interfere with customer sessions
// No need to clear anything - they use different session variables
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛠️ Adhee Hardware Store</title>
    <link rel="stylesheet" href="Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="stylesheet" href="search_product.php">
<style>
    .popup-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 300px;
        background-color: #4CAF50; /* Green for success */
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
        display: flex;
        align-items: center;
    }

    .popup-notification.show {
        opacity: 1; /* Make it visible */
    }

    .popup-notification i {
        margin-right: 10px;
        font-size: 20px;
    }

    .popup-notification .close-notification {
        background: none;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        margin-left: 10px;
    }
    
    /* Notification Icon Inline (near products) */
    .notification-icon-inline {
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: #f8f9fa;
        border-radius: 25px;
        border: 2px solid #f8b739;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .notification-icon-inline i {
        font-size: 20px;
        color: #f8b739;
    }
    
    .notification-icon-inline span:not(.notification-badge-inline) {
        color: #2a3f54;
        font-weight: 600;
        font-size: 14px;
    }
    
    .notification-icon-inline:hover {
        background: #f8b739;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(248, 183, 57, 0.4);
    }
    
    .notification-icon-inline:hover i {
        color: #2a3f54;
    }
    
    .notification-icon-inline:hover span:not(.notification-badge-inline) {
        color: #2a3f54;
    }
    
    .notification-badge-inline {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        min-width: 22px;
        height: 22px;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        padding: 0 6px;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    body {
        font-family: Arial, sans-serif;
    }

    .close-modal {
        position: absolute; /* Position it absolutely within the modal */
        top: 15px; /* Adjust this value to move it closer or further from the top */
        left: 560px; /* Adjust this value to move it closer or further from the right */
        font-size: 24px; /* Increase the size if needed */
        color: #333; /* Change color if needed */
        cursor: pointer; /* Change cursor to pointer */
        background: none; /* Remove background */
        border: none; /* Remove border */
    }
    .category-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 40px;
        }

    .category {
        background-color: #fff;
        border-radius: 8px;
        padding: 30px 20px;
        width: 220px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .category:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .category i {
        font-size: 48px;
        color: #2a3f54;
        margin-bottom: 15px;
    }

    .category h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2a3f54;
        margin-bottom: 8px;
    }

    .category p {
        font-size: 0.9rem;
        color: #666;
    }

    .section-title {
        text-align: center;
        margin-bottom: 40px;
        color: #2a3f54;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: #f8b739;
    }
    .products {
        padding: 3.5rem 2rem;
        background-color: #f8f9fa;
        text-align: center;
    }

    .product-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin: 40px auto;
        max-width: 1200px;
    }

    .product {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .product img {
        height: 200px;
        width: 100%;
        object-fit: contain;
        padding: 20px;
        background-color: #fff;
    }

    .product h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2a3f54;
        margin: 15px 15px 5px;
        text-align: left;
    }

    .product p {
        font-size: 14px;
        color: #666;
        margin: 0 15px 10px;
        text-align: left;
    }

    .product p:last-of-type {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin-bottom: 15px;
    }

    .product button, .add-to-cart {
        background-color: #2a3f54;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 10px;
        margin: 0 15px 15px;
        font-weight: 600;
        text-transform: uppercase;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .product button:hover, .add-to-cart:hover {
        background-color: #1d2d3d;
    }

    /* For search results */
    #searchResults {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin: 20px auto;
        max-width: 1200px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Product info area */
    .product-info {
        flex: 1;
    }
    .cart-item-actions {
    display: flex; /* Use flexbox for layout */
    align-items: center; /* Center items vertically */
    gap: 10px; /* Space between items */
}

.quantity-btn {
    background-color: #ffb733; /* Button background color */
    border: none; /* Remove border */
    color: #333; /* Text color */
    font-weight: bold; /* Bold text */
    width: 30px; /* Fixed width for buttons */
    height: 30px; /* Fixed height for buttons */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    display: flex; /* Flexbox for centering content */
    align-items: center; /* Center content vertically */
    justify-content: center; /* Center content horizontally */
    transition: background-color 0.3s ease; /* Smooth background color transition */
}

.quantity-btn:hover {
    background-color: #ffa500; /* Change background on hover */
}

.remove-btn {
    background-color: #f8f8f8; /* Background color for remove button */
    border: 1px solid #ddd; /* Border for remove button */
    color: #555; /* Text color */
    padding: 4px 8px; /* Padding for remove button */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: all 0.3s ease; /* Smooth transition */
}

.remove-btn:hover {
    background-color: #ffb733; /* Change background on hover */
    color: #333; /* Change text color on hover */
}

/* Track Order Modal */
.track-order-modal {
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

.track-order-modal .modal-content {
    animation: slideUp 0.3s ease;
    position: relative;
}

.track-order-header {
    background: linear-gradient(135deg, #2a3f54 0%, #1d2d3d 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 10px 10px 0 0;
    margin: -2rem -2rem 25px -2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.track-order-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f8b739;
}

.track-order-header h2 i {
    font-size: 1.3rem;
}

.track-order-header .close-modal {
    color: white;
    font-size: 28px;
    top: 20px;
    right: 25px;
    transition: transform 0.3s ease, color 0.3s ease;
}

.track-order-header .close-modal:hover {
    color: #f8b739;
    transform: rotate(90deg);
}

.track-order-input-section {
    margin-bottom: 30px;
}

.track-order-input-section p {
    color: #666;
    margin-bottom: 20px;
    font-size: 0.95rem;
    line-height: 1.6;
}

.track-order-input-wrapper {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.track-order-input-wrapper input {
    flex: 1;
    padding: 15px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.track-order-input-wrapper input:focus {
    outline: none;
    border-color: #f8b739;
    background: white;
    box-shadow: 0 0 0 3px rgba(248, 183, 57, 0.1);
}

.track-order-input-wrapper input::placeholder {
    color: #999;
}

.track-order-btn {
    padding: 15px 35px;
    background: linear-gradient(135deg, #f8b739 0%, #e6a730 100%);
    color: #2a3f54;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(248, 183, 57, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.track-order-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(248, 183, 57, 0.4);
    background: linear-gradient(135deg, #e6a730 0%, #f8b739 100%);
}

.track-order-btn:active {
    transform: translateY(0);
}

.track-order-btn i {
    font-size: 1rem;
}

.order-tracking-result {
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    padding: 30px;
    border-radius: 12px;
    margin-top: 25px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    animation: slideUp 0.4s ease;
}

.order-status-timeline {
    position: relative;
    padding: 25px 0;
}

.order-status-timeline::before {
    content: '';
    position: absolute;
    left: 22.5px;
    top: 45px;
    bottom: 45px;
    width: 2px;
    background: linear-gradient(to bottom, #27ae60 0%, #3498db 50%, #ddd 100%);
    z-index: 1;
}

/* Responsive Design for Track Order Modal */
@media (max-width: 768px) {
    .track-order-modal .modal-content {
        max-width: 95%;
        margin: 20px;
    }
    
    .track-order-header {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .track-order-header h2 {
        font-size: 1.3rem;
    }
    
    .track-order-input-wrapper {
        flex-direction: column;
    }
    
    .track-order-btn {
        width: 100%;
        justify-content: center;
    }
    
    .track-order-result {
        padding: 20px;
    }
    
    .order-status-timeline::before {
        left: 22.5px;
    }
}

.status-step {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}

.status-step:last-child {
    margin-bottom: 0;
}

.status-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 15px;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.status-icon:hover {
    transform: scale(1.1);
}

.status-icon.completed {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.status-icon.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.status-icon.pending {
    background: linear-gradient(135deg, #ddd 0%, #bbb 100%);
    color: #666;
}

.status-info {
    flex: 1;
}

.status-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.status-date {
    font-size: 14px;
    color: #7f8c8d;
}

.order-details-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border-left: 4px solid #f8b739;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease;
}

.order-details-box:hover {
    transform: translateX(5px);
}

.order-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.order-detail-row:last-child {
    border-bottom: none;
}

.order-detail-label {
    color: #7f8c8d;
    font-weight: 500;
}

.order-detail-value {
    color: #2c3e50;
    font-weight: 600;
}
</style>

</head>
<body>
<div class="container">
    <?php
    if (isset($_SESSION['notification'])) {
        $notification = htmlspecialchars($_SESSION['notification'], ENT_QUOTES);
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                showPopupNotification("' . $notification . '", "success");
            });
        </script>';
        unset($_SESSION['notification']); // Clear the notification after displaying
    }
    
    // Handle sessions cleared message
    if (isset($_GET['sessions_cleared']) && $_GET['sessions_cleared'] === 'true') {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                showPopupNotification("All sessions have been cleared. Please log in again.", "success");
                // Remove parameter from URL
                if (window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        </script>';
    }
    
    // Handle logout notification via URL parameter
    if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                showPopupNotification("You have been logged out successfully. Thank you for shopping with us!", "success");
                // Remove logout parameter from URL without reload
                if (window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        </script>';
    }
    
    // Handle error messages from URL parameters
    if (isset($_GET['error'])) {
        $errorMsg = '';
        if ($_GET['error'] === 'admin_access_required') {
            $errorMsg = 'Admin access required. You must log in as an administrator.';
        } elseif ($_GET['error'] === 'admin_login_required') {
            $errorMsg = 'Please use the admin login page to access the admin dashboard.';
        }
        if ($errorMsg) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    showPopupNotification("' . htmlspecialchars($errorMsg) . '", "error");
                    // Remove error parameter from URL without reload
                    if (window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.delete("error");
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    }
                });
            </script>';
        }
    }
    ?>
    <header>
        <div class="logo-container">
            <div class="logo-img">
                <img src="Adhee Hardware Store Small.jpg" alt="Adhee Hardware Store">
            </div>
            <div class="hamburger" id="hamburger">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </div>
        <nav class="nav-links" id="navLinks">
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="#categories">Categories</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="customer_orders.php">My Orders</a></li>
                    <li><a href="#" onclick="openTrackOrderModal(); return false;">Track Order</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="#" onclick="openTrackOrderModal(); return false;">Track Order</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                
            </ul>
        </nav>
        <div class="cart-search">
            <div class="search-container">
                <input type="text" placeholder="Search products..." id="searchInput">
                <button id="searchBtn" type="button"><i class="fas fa-search"></i></button>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="notification-icon" id="notificationIcon" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </div>
            <?php endif; ?>
            <div class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </div>
        </div>
        
        <!-- Notification Dropdown -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: fixed; top: 70px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 10000; min-width: 350px; max-width: 400px; max-height: 500px; overflow-y: auto;">
            <div style="padding: 15px; border-bottom: 2px solid #f8b739; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: #2a3f54;">Notifications</h3>
                <button id="markAllRead" style="background: none; border: none; color: #f8b739; cursor: pointer; font-size: 12px;">Mark all read</button>
            </div>
            <div id="notificationList" style="padding: 10px;">
                <div style="text-align: center; padding: 20px; color: #6c757d;">Loading notifications...</div>
            </div>
        </div>
        <?php endif; ?>
    </header>

    <main>
        <section class="welcome">
            <div class="welcome-content">
                <h2>Welcome to Adhee Hardware Store</h2>
                <p>Your trusted partner for quality tools and hardware solutions</p>
                <a href="#products" class="btn-shop">Shop Now</a>
            </div>
        </section>

        <section class="categories" id="categories">
            <h2 class="section-title">Our Categories</h2>
            <div class="category-container">
                <div class="category">
                    <i class="fas fa-box-open"></i>
                    <h3>All Products</h3>
                    <p>Browse our complete inventory</p>
                </div>
                <div class="category">
                    <i class="fas fa-tools"></i>
                    <h3>Hand Tools</h3>
                    <p>Hammers, screwdrivers, wrenches</p>
                </div>
                <div class="category">
                    <i class="fas fa-bolt"></i>
                    <h3>Power Tools</h3>
                    <p>Drills, saws, sanders</p>
                </div>
                <div class="category">
                    <i class="fas fa-ruler-combined"></i>
                    <h3>Measuring Tools</h3>
                    <p>Tapes, levels, lasers</p>
                </div>
            </div>
        </section>

        <section class="products" id="products">
            <div style="text-align: center; margin-bottom: 30px; position: relative; width: 100%;">
                <h2 style="margin: 0 0 20px 0; display: inline-block; text-align: center;">Featured Products</h2>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div style="position: absolute; top: 0; right: 0;">
                    <div class="notification-icon-inline" id="notificationIconInline" title="View Notifications">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <span class="notification-badge-inline" id="notificationBadgeInline" style="display: none;">0</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="product-container" id="productContainer">
                <!-- Products will be added dynamically with JavaScript -->
            </div>
            <div id="searchResults" class="search-results"></div>
        </section>

        <section class="about" id="about">
            <div class="about-container">
                <div class="about-content">
                    <h2>About Our Store</h2>
                    <p>Adhee Hardware has been serving professionals and DIY enthusiasts since 1998. We offer premium quality tools with expert advice to help you complete any project.</p>
                    <p>Our team of experienced professionals is always ready to assist you in finding the perfect tools for your needs.</p>
                </div>
                <div class="about-image">
                    <img src="Adhee Hardware Store name logo.jpg" alt="Our Store">
                </div>
            </div>
        </section>

        <section class="contact" id="contact">
            <h2>Contact Us</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    <p><i class="fas fa-map-marker-alt"></i>165/A Main Street Kalmunai</p>
                    <p><i class="fas fa-phone"></i>0714281872</p>
                    <p><i class="fas fa-envelope"></i> info@adheehardware.com</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <form>
                        <div class="form-group">
                            <input type="text" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn-send">Send Message</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <div class="cart-modal" id="cartModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Your Shopping Cart</h2>
            <div class="cart-items" id="cartItems">
                <p class="empty-cart">Your cart is empty</p>
            </div>
            <div class="cart-total">
                <p>Total: RS<span id="cartTotal">0.00</span></p>
                <a href="checkout.php" class="btn-checkout" style="text-decoration: none; display: block; text-align: center;">Proceed to Checkout</a>
            </div>
        </div>
    </div>

    <!-- Track Order Modal -->
    <div class="track-order-modal" id="trackOrderModal" style="display: none;">
        <div class="modal-content" style="max-width: 650px; position: relative;">
            <div class="track-order-header">
                <h2><i class="fas fa-map-marked-alt"></i> Track Your Order</h2>
                <span class="close-modal" onclick="closeTrackOrderModal()">&times;</span>
            </div>
            <div class="track-order-input-section">
                <p><i class="fas fa-info-circle" style="color: #f8b739; margin-right: 8px;"></i>Enter your order ID below to track your order status and view real-time updates</p>
                <div class="track-order-input-wrapper">
                    <input type="number" id="trackOrderId" placeholder="Enter Order ID (e.g., 1)" min="1">
                    <button onclick="trackOrder()" class="track-order-btn">
                        <i class="fas fa-search"></i> Track
                    </button>
                </div>
            </div>
            <div id="trackOrderResult"></div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#Login">Login</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Returns & Refunds</a></li>
                </ul>
            </div>`
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe for updates and special offers</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your Email Address" required>
                    <button type="submit" class="btn-subscribe">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Adhee Hardware Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Function to show popup notifications
        function showPopupNotification(message, type = 'success') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'popup-notification';
            
            // Icon based on notification type
            let icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-exclamation-circle';
            if (type === 'warning') icon = 'fa-exclamation-triangle';
            if (type === 'info') icon = 'fa-info-circle';
            
            // Set content
            notification.innerHTML = `
                <i class="fas ${icon}"></i>
                <div class="notification-content">${message}</div>
                <button class="close-notification">&times;</button>
            `;
            
            // Add to document
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Auto-remove after 3 seconds
            const timeout = setTimeout(() => {
                closeNotification(notification);
            }, 3000);
            
            // Close button functionality
            const closeButton = notification.querySelector('.close-notification');
            closeButton.addEventListener('click', () => {
                clearTimeout(timeout);
                closeNotification(notification);
            });
        }

        // Function to close and remove notification
        function closeNotification(notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300); // Match with CSS transition duration
        }
    </script>
    <script>
        // Function to open the cart modal
        function openCartModal() {
            const cartModal = document.getElementById('cartModal'); // Ensure this matches your modal's ID
            if (!cartModal) return; // Check if cartModal is defined
            
            loadCartFromDB(); // Load cart items from the database
            cartModal.style.display = 'block'; // Show the modal
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        // Function to close the cart modal
        function closeCartModal() {
            const cartModal = document.getElementById('cartModal'); // Ensure this matches your modal's ID
            if (!cartModal) return; // Check if cartModal is defined
            
            cartModal.style.display = 'none'; // Hide the modal
            document.body.style.overflow = 'auto'; // Restore background scrolling
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cartIcon = document.querySelector('.cart-icon'); // Ensure this selector matches your HTML
            const closeModalButton = document.querySelector('.close-modal'); // Ensure this selector matches your HTML

            // Open cart modal when cart icon is clicked
            cartIcon.addEventListener('click', openCartModal);

            // Close cart modal when close button is clicked
            closeModalButton.addEventListener('click', closeCartModal);
        });
    </script>


</div>
</body>
<script>
    // Function to open the cart modal
    function openCartModal() {
        const cartModal = document.getElementById('cartModal'); // Ensure this matches your modal's ID
        if (!cartModal) return; // Check if cartModal is defined
        
        loadCartFromDB(); // Load cart items from the database
        cartModal.style.display = 'block'; // Show the modal
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    // Function to close the cart modal
    function closeCartModal() {
        const cartModal = document.getElementById('cartModal'); // Ensure this matches your modal's ID
        if (!cartModal) return; // Check if cartModal is defined
        
        cartModal.style.display = 'none'; // Hide the modal
        document.body.style.overflow = 'auto'; // Restore background scrolling
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cartIcon = document.querySelector('.cart-icon'); // Ensure this selector matches your HTML
        const closeModalButton = document.querySelector('.close-modal'); // Ensure this selector matches your HTML

        // Open cart modal when cart icon is clicked
        cartIcon.addEventListener('click', openCartModal);

        // Close cart modal when close button is clicked
        closeModalButton.addEventListener('click', closeCartModal);
    });
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchBtn').addEventListener('click', searchProducts);
    document.getElementById('searchInput').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            searchProducts();
        }
    });
});
function searchProducts(event) {
    if (event) {
        event.preventDefault();
    }
    const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
    const searchResultsContainer = document.getElementById('searchResults');
    const productContainer = document.getElementById('productContainer');

    // Clear previous results and featured products
    searchResultsContainer.innerHTML = '';
    productContainer.innerHTML = '';

    if (searchTerm === '') {
        return;
    }

    fetch('search_product.php?term=' + encodeURIComponent(searchTerm))
    .then(response => response.json())
    .then(data => {
        if (Array.isArray(data)) {
            data.forEach(product => {
                const productElement = document.createElement('div');
                productElement.classList.add('product');
                productElement.innerHTML = `
                    <img src="${product.image}" alt="${product.name}">
                    <h3>${product.name}</h3>
                    <p>Category: ${product.category}</p>
                    <p>Price: RS ${product.price}</p>
                    <button class="add-to-cart" data-id="${product.id}">ADD TO CART</button>
                `;
                searchResultsContainer.appendChild(productElement);
            });
            
            // Apply consistent styling
            const searchProducts = searchResultsContainer.querySelectorAll('.product');
            searchProducts.forEach(product => {
                product.style.backgroundColor = '#fff';
                product.style.borderRadius = '8px';
                product.style.padding = '20px';
                product.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
                product.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
                product.style.cursor = 'pointer';
            });
            
            // Add hover effect
            searchProducts.forEach(product => {
                product.addEventListener('mouseover', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.12)';
                });
                product.addEventListener('mouseout', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
                });
            });
            
            // Add event listeners to the newly created "Add to Cart" buttons
            const addToCartButtons = searchResultsContainer.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    addToCart(productId);
                });
            });
        } else {
            console.error('Expected an array but received:', data);
            searchResultsContainer.innerHTML = '<p>No products found.</p>';
        }
    })
    .catch(error => {
        console.error('Error fetching products:', error);
        searchResultsContainer.innerHTML = '<p>Error fetching products.</p>';
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const productContainer = document.getElementById('productContainer');

    // Fetch and display featured products
    fetch('featured_products.php')
        .then(response => response.json())
        .then(products => {
            if (Array.isArray(products)) {
                products.forEach(product => {
                    const productElement = document.createElement('div');
                    productElement.classList.add('product');
                    productElement.innerHTML = `
                        <img src="${product.image}" alt="${product.name}">
                        <h3>${product.name}</h3>
                        <p>Category: ${product.category}</p>
                        <p>Price: RS ${product.price}</p>
                        <button class="add-to-cart" data-id="${product.id}">ADD TO CART</button>
                    `;
                    productContainer.appendChild(productElement);
                });

                // Apply consistent styling to featured products
                const featuredProducts = productContainer.querySelectorAll('.product');
                featuredProducts.forEach(product => {
                    product.style.backgroundColor = '#fff';
                    product.style.borderRadius = '8px';
                    product.style.padding = '20px';
                    product.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
                    product.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
                    product.style.cursor = 'pointer';
                });
                
                // Add hover effect
                featuredProducts.forEach(product => {
                    product.addEventListener('mouseover', function() {
                        this.style.transform = 'translateY(-5px)';
                        this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.12)';
                    });
                    product.addEventListener('mouseout', function() {
                        this.style.transform = 'translateY(0)';
                        this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
                    });
                });

                // Add event listeners to "Add to Cart" buttons
                const addToCartButtons = document.querySelectorAll('.add-to-cart');
                addToCartButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-id');
                        addToCart(productId);
                    });
                });
            }
        })
        .catch(error => console.error('Error fetching featured products:', error));
});
function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response from server:', data);
        if (data.success) {
            console.log('Cart updated successfully!');
            loadCartFromDB();
            const product = products.find(p => p.id == productId);
            const productName = product ? product.name : 'Product';
            console.log(`Adding ${productName} to cart`);
            showPopupNotification(`${productName} added to cart`, 'success');
        } else {
            console.error('Failed to add to cart:', data.message);
            showPopupNotification('Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showPopupNotification('Error adding product to cart', 'error');
    });
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartIcon = document.querySelector('.cart-icon'); // Ensure this selector matches your HTML
        const closeModalButton = document.querySelector('.close-modal'); // Ensure this selector matches your HTML

        // Open cart modal when cart icon is clicked
        cartIcon.addEventListener('click', openCartModal);

        // Close cart modal when close button is clicked
        closeModalButton.addEventListener('click', closeCartModal);
    });
</script>
<script>
    // Set up event listeners
    function setupEventListeners() {
        // Add to cart buttons using event delegation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                const productId = parseInt(e.target.getAttribute('data-id'));
                addToCart(productId);
            }
        });

        // Cart icon click
        if (cartIcon) {
            cartIcon.addEventListener('click', openCartModal);
        }

        // Close modal
        if (closeModal) {
            closeModal.addEventListener('click', closeCartModal);
        }
        window.addEventListener('click', (e) => {
            if (e.target === cartModal) {
                closeCartModal();
            }
        });

        // Hamburger menu for mobile
        if (hamburger) {
            hamburger.addEventListener('click', toggleMobileMenu);
        }

        // Search functionality
        if (searchBtn) {
            searchBtn.addEventListener('click', searchProducts);
        }
        
        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    searchProducts();
                }
            });
        }
    }
</script>

<script>
// Track Order Functions
function openTrackOrderModal() {
    document.getElementById('trackOrderModal').style.display = 'flex';
    document.getElementById('trackOrderId').value = '';
    document.getElementById('trackOrderResult').innerHTML = '';
    document.body.style.overflow = 'hidden';
}

function closeTrackOrderModal() {
    document.getElementById('trackOrderModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function trackOrder() {
    const orderId = document.getElementById('trackOrderId').value;
    const resultDiv = document.getElementById('trackOrderResult');
    
    if (!orderId) {
        resultDiv.innerHTML = `
            <div style="text-align: center; padding: 30px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; color: #856404;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px; color: #ffc107;"></i>
                <h3 style="margin-bottom: 10px; color: #856404;">Please Enter an Order ID</h3>
                <p style="color: #856404;">Enter your order ID in the field above to track your order.</p>
            </div>
        `;
        return;
    }
    
    resultDiv.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #f8b739;"></i><p style="margin-top: 15px; color: #666;">Loading order information...</p></div>';
    
    fetch('api/track_order.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultDiv.innerHTML = `
                    <div style="text-align: center; padding: 40px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 12px; color: #721c24;">
                        <i class="fas fa-exclamation-circle" style="font-size: 64px; margin-bottom: 20px; color: #dc3545;"></i>
                        <h3 style="margin-bottom: 15px; color: #721c24; font-size: 1.3rem;">Order Not Found</h3>
                        <p style="color: #721c24; font-size: 1rem;">${data.error}</p>
                        <p style="color: #856404; margin-top: 15px; font-size: 0.9rem;"><i class="fas fa-info-circle"></i> Please check your order ID and try again.</p>
                    </div>
                `;
                return;
            }
            
            // Display order tracking information
            const statusColors = {
                'pending': '#f39c12',
                'processing': '#3498db',
                'completed': '#27ae60',
                'cancelled': '#e74c3c'
            };
            
            const statusIcons = {
                'pending': 'fa-clock',
                'processing': 'fa-cog fa-spin',
                'completed': 'fa-check-circle',
                'cancelled': 'fa-times-circle'
            };
            
            resultDiv.innerHTML = `
                <div class="order-tracking-result">
                    <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef;">
                        <h3 style="color: #2a3f54; margin-bottom: 10px; font-size: 1.4rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <i class="fas fa-box" style="color: #f8b739;"></i> Order #${data.id}
                        </h3>
                        <span style="display: inline-block; padding: 6px 15px; background: ${statusColors[data.status]}; color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas ${statusIcons[data.status]}"></i> ${data.status}
                        </span>
                    </div>
                    
                    <div class="order-details-box">
                        <div class="order-detail-row">
                            <span class="order-detail-label"><i class="fas fa-calendar-alt" style="margin-right: 8px; color: #f8b739;"></i>Order Date</span>
                            <span class="order-detail-value">${new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                        </div>
                        <div class="order-detail-row">
                            <span class="order-detail-label"><i class="fas fa-dollar-sign" style="margin-right: 8px; color: #f8b739;"></i>Total Amount</span>
                            <span class="order-detail-value" style="color: #27ae60; font-size: 1.1rem;">RS ${parseFloat(data.total_amount).toFixed(2)}</span>
                        </div>
                        <div class="order-detail-row">
                            <span class="order-detail-label"><i class="fas fa-clock" style="margin-right: 8px; color: #f8b739;"></i>Last Updated</span>
                            <span class="order-detail-value">${new Date(data.updated_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                    </div>
                    
                    <h4 style="color: #2a3f54; margin: 25px 0 20px; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-route" style="color: #f8b739;"></i> Order Status Timeline
                    </h4>
                    <div class="order-status-timeline">
                        <div class="status-step">
                            <div class="status-icon ${data.status !== 'cancelled' ? 'completed' : 'pending'}">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="status-info">
                                <div class="status-title">Order Placed</div>
                                <div class="status-date">${new Date(data.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
                            </div>
                        </div>
                        
                        <div class="status-step">
                            <div class="status-icon ${data.status === 'processing' || data.status === 'completed' ? 'completed' : data.status === 'pending' ? 'active' : 'pending'}">
                                <i class="fas fa-cog ${data.status === 'processing' ? 'fa-spin' : ''}"></i>
                            </div>
                            <div class="status-info">
                                <div class="status-title">Processing</div>
                                <div class="status-date">${data.status === 'pending' ? 'Waiting...' : new Date(data.updated_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
                            </div>
                        </div>
                        
                        <div class="status-step">
                            <div class="status-icon ${data.status === 'completed' ? 'completed' : data.status === 'cancelled' ? 'pending' : 'pending'}">
                                <i class="fas ${data.status === 'completed' ? 'fa-check-circle' : data.status === 'cancelled' ? 'fa-times-circle' : 'fa-truck'}"></i>
                            </div>
                            <div class="status-info">
                                <div class="status-title">${data.status === 'cancelled' ? 'Cancelled' : 'Completed'}</div>
                                <div class="status-date">${data.status === 'completed' || data.status === 'cancelled' ? new Date(data.updated_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Pending...'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px; padding-top: 25px; border-top: 2px solid #e9ecef; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="order_details.php?id=${data.id}" class="track-order-btn" style="text-decoration: none; padding: 12px 25px; background: linear-gradient(135deg, #2a3f54 0%, #1d2d3d 100%); color: white; box-shadow: 0 4px 15px rgba(42, 63, 84, 0.3);">
                            <i class="fas fa-eye"></i> View Full Details
                        </a>
                        <button onclick="closeTrackOrderModal()" class="track-order-btn" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = `
                <div style="text-align: center; padding: 40px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 12px; color: #721c24;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 64px; margin-bottom: 20px; color: #dc3545;"></i>
                    <h3 style="margin-bottom: 15px; color: #721c24; font-size: 1.3rem;">Connection Error</h3>
                    <p style="color: #721c24; font-size: 1rem;">Failed to track order. Please check your connection and try again.</p>
                </div>
            `;
        });
}

// Allow Enter key to track order
document.addEventListener('DOMContentLoaded', function() {
    const trackInput = document.getElementById('trackOrderId');
    if (trackInput) {
        trackInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                trackOrder();
            }
        });
    }
    
    // Close modal when clicking outside
    const trackModal = document.getElementById('trackOrderModal');
    if (trackModal) {
        trackModal.addEventListener('click', function(e) {
            if (e.target === trackModal) {
                closeTrackOrderModal();
            }
        });
    }
});
</script>

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    // Notification System
    let notificationCheckInterval;
    
    // Load notifications
    function loadNotifications() {
        console.log('Loading notifications...');
        fetch('api/get_notifications.php')
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Notifications data:', data);
                const badge = document.getElementById('notificationBadge');
                const list = document.getElementById('notificationList');
                
                if (!badge || !list) {
                    console.error('Notification elements not found!');
                    return;
                }
                
                // Update badge (header)
                if (badge) {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.style.display = 'flex';
                        console.log('Unread count:', data.unread_count);
                    } else {
                        badge.style.display = 'none';
                    }
                }
                
                // Update inline badge (near products)
                const badgeInline = document.getElementById('notificationBadgeInline');
                if (badgeInline) {
                    if (data.unread_count > 0) {
                        badgeInline.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badgeInline.style.display = 'flex';
                    } else {
                        badgeInline.style.display = 'none';
                    }
                }
                
                // Update notification list
                if (data.notifications && data.notifications.length === 0) {
                    list.innerHTML = '<div style="text-align: center; padding: 30px; color: #6c757d;"><i class="fas fa-bell-slash" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i><p>No notifications</p></div>';
                } else if (data.notifications) {
                    list.innerHTML = data.notifications.map(notif => `
                        <div class="notification-item" style="padding: 12px; border-bottom: 1px solid #e9ecef; cursor: pointer; ${notif.is_read == 0 ? 'background: #f8f9fa; font-weight: 500;' : ''}" onclick="markNotificationRead(${notif.id})">
                            <div style="display: flex; align-items: start; gap: 10px;">
                                <i class="fas fa-${notif.type === 'order_update' ? 'shopping-bag' : 'info-circle'}" style="color: #f8b739; margin-top: 3px;"></i>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; color: #2a3f54; margin-bottom: 5px;">${notif.message}</div>
                                    <div style="font-size: 11px; color: #6c757d;">${new Date(notif.created_at).toLocaleString()}</div>
                                </div>
                                ${notif.is_read == 0 ? '<div style="width: 8px; height: 8px; background: #f8b739; border-radius: 50%; margin-top: 5px;"></div>' : ''}
                            </div>
                        </div>
                    `).join('');
                    console.log('Loaded', data.notifications.length, 'notifications');
                }
                
                if (data.error) {
                    console.error('API Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                const list = document.getElementById('notificationList');
                if (list) {
                    list.innerHTML = '<div style="text-align: center; padding: 20px; color: #e74c3c;">Error loading notifications. Check console.</div>';
                }
            });
    }
    
    // Mark notification as read
    function markNotificationRead(notificationId) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({notification_id: notificationId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        });
    }
    
    // Mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.stopPropagation();
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        });
    });
    
    // Toggle notification dropdown (header icon)
    document.getElementById('notificationIcon')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            loadNotifications();
        } else {
            dropdown.style.display = 'none';
        }
    });
    
    // Toggle notification dropdown (inline icon near products)
    document.getElementById('notificationIconInline')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            loadNotifications();
        } else {
            dropdown.style.display = 'none';
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notificationDropdown');
        const icon = document.getElementById('notificationIcon');
        const iconInline = document.getElementById('notificationIconInline');
        if (dropdown && !dropdown.contains(e.target) && 
            !(icon && icon.contains(e.target)) && 
            !(iconInline && iconInline.contains(e.target))) {
            dropdown.style.display = 'none';
        }
    });
    
    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, initializing notifications...');
        // Wait a bit for page to fully load
        setTimeout(function() {
            loadNotifications();
            // Check for new notifications every 30 seconds
            notificationCheckInterval = setInterval(loadNotifications, 30000);
        }, 500);
    });
    
    // Also try loading immediately if DOM is already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadNotifications, 1000);
        });
    } else {
        setTimeout(loadNotifications, 1000);
    }
    </script>
    <?php endif; ?>

</html>