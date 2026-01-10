<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "127.0.0.1";
$username   = "root";  // Replace with your actual username
$password   = "";  // Replace with your actual password
$dbname     = "adhee_hardware";   // Your database name based on the screenshot

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use SQL aliases to change the column names as required
$sql = "SELECT 
            id AS product_id, 
            name AS product_name, 
            category AS product_category, 
            price AS product_price, 
            image AS product_image 
        FROM products";
$result = $conn->query($sql);
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
    <style>
        /* Inline CSS for the product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }
        .product {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .product img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
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
                <li><a href="login.php">Login</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        <div class="cart-search">
            <div class="search-container">
                <input type="text" placeholder="Search products..." id="searchInput">
                <button id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <div class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </div>
        </div>
    </header>
    
    <main>
        <section class="welcome">
            <div class="welcome-content">
                <h2>Welcome to Adhee Hardware</h2>
                <p>Your trusted partner for quality tools and hardware solutions</p>
                <a href="#products" class="btn-shop">Shop Now</a>
            </div>
        </section>
        
        <!-- Categories Section (to be enhanced later if needed) -->
        <section class="categories" id="categories">
            <h2>Our Categories</h2>
            <div class="category-container" id="categoryContainer">
                <!-- Categories can be dynamically loaded here -->
            </div>
        </section>
        
        <!-- Products Section -->
        <section class="products" id="products">
            <h2>Featured Products</h2>
            <div class="product-container" id="productContainer">
                <?php
                if ($result && $result->num_rows > 0) {
                    echo '<div class="product-grid">';
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="product">';
                        echo '<img src="' . $row['image'] . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                        echo '<p>Category: ' . htmlspecialchars($row['category']) . '</p>';
                        echo '<p>Price: $' . number_format($row['price'], 2) . '</p>';
                        echo '<button>Add to Cart</button>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No products found matching your criteria.</p>';
                }
                ?>
            </div>
        </section>
        
        <!-- About Section -->
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
        
        <!-- Contact Section -->
        <section class="contact" id="contact">
            <h2>Contact Us</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    <p><i class="fas fa-map-marker-alt"></i>165/A Main Street Kalmunai</p>
                    <p><i class="fas fa-phone"></i>(+94)714281872</p>
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
    
    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Your Shopping Cart</h2>
            <div class="cart-items" id="cartItems">
                <p class="empty-cart">Your cart is empty</p>
            </div>
            <div class="cart-total">
                <p>Total: $<span id="cartTotal">0.00</span></p>
                <button class="btn-checkout">Proceed to Checkout</button>
            </div>
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
            </div>
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
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>