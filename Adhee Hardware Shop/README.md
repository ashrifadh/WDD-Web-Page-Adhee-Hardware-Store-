# 🛠️ Adhee Hardware Store - Complete System Documentation

## 📋 Project Overview

**Adhee Hardware Store** is a full-featured e-commerce platform designed for hardware products with comprehensive admin management capabilities. The system provides a complete shopping experience for customers and powerful administrative tools for managing products, suppliers, customers, orders, and inventory.

**Project Type:** E-Commerce Web Application  
**Status:** ✅ Production Ready  
**Version:** 2.0  
**Last Updated:** December 2025

---

## 🚀 Technologies & Languages Used

### Backend Technologies
- **PHP 7.4+** - Server-side scripting language for business logic
- **MySQL 5.7+** - Relational database management system
- **PDO (PHP Data Objects)** - Secure database abstraction layer
- **Session Management** - User authentication and session handling
- **RESTful API** - API endpoints for AJAX operations

### Frontend Technologies
- **HTML5** - Semantic markup structure
- **CSS3** - Advanced styling with Flexbox, Grid, and animations
- **JavaScript (ES6+)** - Modern JavaScript for client-side interactivity
- **AJAX** - Asynchronous data fetching and updates
- **Fetch API** - Modern API for HTTP requests

### External Libraries & Frameworks
- **Font Awesome 6.4.0** - Comprehensive icon library
- **Google Fonts** - Poppins and Montserrat typography
- **Bootstrap-inspired** - Custom responsive grid system

### Development Environment
- **XAMPP** - Local development server (Apache, MySQL, PHP)
- **phpMyAdmin** - Web-based database administration
- **VS Code / Any IDE** - Code editor

### Database
- **MySQL** - Primary database
- **InnoDB Engine** - Transaction support
- **UTF-8 Encoding** - Multi-language support

---

## 🗄️ Database Structure

### Core Tables

#### 1. **users**
- User accounts (customers and admins)
- Fields: `id`, `username`, `email`, `password`, `role`, `created_at`
- Role-based access control (customer/admin)

#### 2. **products**
- Product catalog with inventory management
- Fields: `id`, `name`, `description`, `price`, `category`, `image`, `stock_quantity`, `min_stock_level`
- Stock tracking and low stock alerts

#### 3. **suppliers**
- Supplier information management
- Fields: `id`, `name`, `email`, `phone`, `address`
- Supplier-product relationships

#### 4. **product_suppliers**
- Many-to-many relationship between products and suppliers
- Fields: `product_id`, `supplier_id`

#### 5. **cart**
- Shopping cart functionality
- Fields: `id`, `user_id`, `product_id`, `quantity`, `created_at`
- Session-based cart management

#### 6. **orders**
- Customer order records
- Fields: `id`, `user_id`, `total_amount`, `status`, `created_at`, `updated_at`
- Order status tracking (pending, processing, completed, cancelled)

#### 7. **order_items**
- Individual order line items
- Fields: `id`, `order_id`, `product_id`, `quantity`, `price`
- Detailed order breakdown

#### 8. **notifications**
- Customer notification system
- Fields: `id`, `user_id`, `order_id`, `message`, `type`, `is_read`, `created_at`
- Real-time order status updates

### Database Configuration
```php
// db_config.php
$host = '127.0.0.1';
$dbname = 'adhee_hardware';
$username = 'root';
$password = '';
```

---

## 🎯 Complete Feature List

### 👥 Customer-Facing Features

#### 1. **Home Page** (`Index.php`)
- ✅ **Product Browsing** - Beautiful card grid layout displaying all products
- ✅ **Real-time Search** - Instant product search with AJAX
- ✅ **Category Filtering** - Filter products by category (All Products, Hand Tools, Power Tools, Measuring Tools)
- ✅ **Product Details** - View detailed product information
- ✅ **Add to Cart** - One-click add to cart functionality
- ✅ **Shopping Cart Icon** - Cart count badge showing items
- ✅ **Notification System** - Bell icon with unread notification count
- ✅ **User Authentication** - Login/Registration system
- ✅ **Responsive Design** - Mobile-friendly interface
- ✅ **Welcome Banner** - Hero section with call-to-action
- ✅ **Category Cards** - Visual category navigation
- ✅ **Featured Products** - Highlighted product section

#### 2. **Shopping Cart System**
- ✅ **Add to Cart** (`add_to_cart.php`) - Add products to cart
- ✅ **View Cart** (`get_cart.php`) - Display cart contents with modal
- ✅ **Update Quantity** (`update_cart_quantity.php`) - Modify item quantities
- ✅ **Remove Items** (`remove_from_cart.php`) - Delete cart items
- ✅ **Cart Modal** - Beautiful popup cart interface
- ✅ **Real-time Updates** - Cart updates without page refresh
- ✅ **Price Calculation** - Automatic total calculation

#### 3. **Checkout System** (`checkout.php`)
- ✅ **Order Placement** - Complete purchase process
- ✅ **Customer Information** - Collect delivery details
- ✅ **Order Summary** - Review cart before checkout
- ✅ **Order Confirmation** - Success notifications
- ✅ **Automatic Stock Reduction** - Stock updated on order placement
- ✅ **Order ID Generation** - Unique order tracking

#### 4. **Order Management**
- ✅ **Order History** (`customer_orders.php`) - View all past orders
- ✅ **Order Details** (`order_details.php`) - Comprehensive order information
- ✅ **Order Tracking** - Track order status in real-time
- ✅ **Status Badges** - Visual order status indicators
- ✅ **Order Timeline** - Visual status progression
- ✅ **Order Search** - Track order by order ID

#### 5. **User Authentication**
- ✅ **Registration** (`register.php`, `signup.php`) - New user account creation
- ✅ **Login** (`login.php`) - Secure user authentication
- ✅ **Session Management** - Secure user sessions
- ✅ **Logout** (`logout.php`) - Secure logout functionality
- ✅ **Password Security** - Secure password handling
- ✅ **Role-Based Access** - Customer vs Admin separation

#### 6. **Notification System** ⭐ NEW
- ✅ **Real-time Notifications** - Order status update notifications
- ✅ **Notification Bell Icon** - Header notification button
- ✅ **Unread Badge** - Red badge showing unread count
- ✅ **Notification Dropdown** - Beautiful notification list
- ✅ **Mark as Read** - Individual and bulk mark as read
- ✅ **Auto-refresh** - Notifications update every 30 seconds
- ✅ **Order Status Updates** - Automatic notifications when admin updates status

#### 7. **Product Search & Filter**
- ✅ **Search Products** (`search_product.php`) - Real-time search
- ✅ **Category Filter** - Filter by product category
- ✅ **Search Results** - Dynamic search results display
- ✅ **No Results Handling** - User-friendly empty state

---

### 🔧 Admin Dashboard Features (`admin_dashboard.php`)

#### 1. **Dashboard Overview**
- ✅ **Statistics Cards** - Total products, suppliers, customers, orders
- ✅ **Real-time Metrics** - Live data updates
- ✅ **Low Stock Alerts** - Automatic inventory warnings
- ✅ **Quick Actions** - Fast access to common tasks
- ✅ **Visual Dashboard** - Beautiful card-based layout

#### 2. **Product Management**
- ✅ **View All Products** - Card grid display with images
- ✅ **Add New Products** (`api/add_product_admin.php`) - Product creation form
- ✅ **Edit Products** (`api/update_product.php`) - Product modification
- ✅ **Delete Products** (`api/delete_product.php`) - Product removal
- ✅ **Stock Management** (`api/update_stock.php`) - Update stock quantities
- ✅ **Low Stock Tracking** - Automatic low stock detection
- ✅ **Supplier Assignment** - Link products to suppliers
- ✅ **Search & Filter** - Product search and category filtering
- ✅ **Product Details Modal** - Comprehensive product view
- ✅ **Image Management** - Product image support

#### 3. **Supplier Management**
- ✅ **View All Suppliers** - Complete supplier listing
- ✅ **Add New Suppliers** (`api/add_supplier.php`) - Supplier registration
- ✅ **Edit Supplier Info** - Update supplier details
- ✅ **Delete Suppliers** (`api/delete_supplier.php`) - Remove supplier accounts
- ✅ **Product Assignment** (`api/assign_supplier.php`) - Assign suppliers to products
- ✅ **Supplier Details** - Contact information management
- ✅ **Supplier Modal** - Beautiful form interface

#### 4. **Customer Management**
- ✅ **View All Customers** - Complete customer database
- ✅ **Customer Details** - Contact information display
- ✅ **Order History** - Customer order tracking
- ✅ **Registration Tracking** - Customer acquisition data
- ✅ **Order Count** - Total orders per customer
- ✅ **Customer Table** - Sortable customer list

#### 5. **Order Management** ⭐ ENHANCED
- ✅ **View All Orders** - Complete order listing
- ✅ **Order Details** - Comprehensive order information
- ✅ **Status Updates** - Beautiful modal for status changes
- ✅ **Status Options** - Pending, Processing, Completed, Cancelled
- ✅ **Customer Information** - Order customer details
- ✅ **Product Tracking** - Ordered product details
- ✅ **Order Search** - Find orders quickly
- ✅ **Status Badges** - Color-coded status indicators
- ✅ **Styled Status Modal** - Modern UI for status updates

#### 6. **Admin Authentication**
- ✅ **Admin Login** (`admin_login.php`) - Separate admin login
- ✅ **Admin Dashboard Access** - Secure admin panel
- ✅ **Session Separation** - Admin and customer sessions isolated
- ✅ **Admin Logout** (`admin_logout.php`) - Secure logout
- ✅ **Access Control** - Role-based access enforcement

---

## 🔧 API Endpoints (18 Total)

### Statistics & Dashboard
- `api/get_stats.php` - Dashboard statistics (products, suppliers, customers, orders)
- `api/get_low_stock.php` - Low stock product alerts

### Product Management
- `api/get_products_admin.php` - Get all products for admin view
- `api/add_product_admin.php` - Add new product
- `api/update_product.php` - Update product details
- `api/update_stock.php` - Update product stock quantity
- `api/delete_product.php` - Delete product

### Supplier Management
- `api/get_suppliers.php` - Get all suppliers
- `api/add_supplier.php` - Add new supplier
- `api/delete_supplier.php` - Delete supplier
- `api/assign_supplier.php` - Assign supplier to product

### Order Management
- `api/get_orders_admin.php` - Get all orders for admin
- `api/update_order_status.php` - Update order status (creates notifications)
- `api/place_order.php` - Place new order
- `api/track_order.php` - Track order by order ID

### Customer Management
- `api/get_customers.php` - Get all customers with order counts

### Notification System ⭐ NEW
- `api/get_notifications.php` - Get user notifications
- `api/mark_notification_read.php` - Mark notification as read

---

## 📁 Complete File Structure

### Main Application Files
```
Index.php                    # Home page (Customer-facing)
admin_dashboard.php          # Admin panel (Complete management)
admin_login.php              # Admin authentication
admin_logout.php             # Admin logout
checkout.php                 # Checkout process
customer_orders.php          # Customer order history
order_details.php            # Detailed order view
login.php                    # Customer login
register.php                 # Customer registration
signup.php                   # Alternative registration
logout.php                   # Customer logout
```

### API Directory (`/api/`)
```
get_stats.php                # Dashboard statistics
get_low_stock.php            # Low stock alerts
get_products_admin.php       # Admin product list
add_product_admin.php        # Add product
update_product.php           # Update product
update_stock.php             # Update stock
delete_product.php           # Delete product
get_suppliers.php            # Supplier list
add_supplier.php             # Add supplier
delete_supplier.php          # Delete supplier
assign_supplier.php          # Assign supplier
get_orders_admin.php         # Admin order list
update_order_status.php      # Update order status
place_order.php              # Place order
track_order.php              # Track order
get_customers.php            # Customer list
get_notifications.php        # Get notifications ⭐ NEW
mark_notification_read.php   # Mark notification read ⭐ NEW
```

### JavaScript Files
```
script.js                    # Main frontend functionality
admin_dashboard.js           # Admin dashboard functionality
admin_scripts.js             # Additional admin scripts
Login Script.js              # Login page scripts
```

### CSS Stylesheets
```
Style.css                    # Main stylesheet (Responsive design)
sty.css                      # Additional styles
mk.css                       # Admin-specific styles
Login Style.css              # Login page styles
```

### Database Files
```
db_config.php                # Database configuration
adhee_hardware (2).sql       # Base database schema
database_update.sql          # Database updates
create_notifications_table.sql # Notifications table ⭐ NEW
sample_data.sql              # Sample test data
```

### Utility Files
```
search_product.php           # Product search
featured_products.php        # Featured products API
add_to_cart.php              # Add to cart
get_cart.php                 # Get cart items
update_cart_quantity.php     # Update cart
remove_from_cart.php         # Remove from cart
```

---

## 🎨 Frontend Features

### Design & UI
- ✅ **Modern Card-Based Design** - Beautiful product cards
- ✅ **Responsive Grid Layout** - Adapts to all screen sizes
- ✅ **Smooth Animations** - CSS transitions and animations
- ✅ **Color Scheme** - Professional hardware store theme (Dark Blue #2a3f54, Yellow #f8b739)
- ✅ **Typography** - Poppins and Montserrat fonts
- ✅ **Icons** - Font Awesome 6.4.0 integration
- ✅ **Modal Dialogs** - Beautiful popup modals
- ✅ **Toast Notifications** - Success/error messages
- ✅ **Loading States** - AJAX loading indicators

### User Experience
- ✅ **Real-time Search** - Instant product search
- ✅ **Category Navigation** - Easy category browsing
- ✅ **Shopping Cart Modal** - Quick cart access
- ✅ **Notification System** - Real-time order updates
- ✅ **Order Tracking** - Visual order status timeline
- ✅ **Form Validation** - Client-side validation
- ✅ **Error Handling** - User-friendly error messages
- ✅ **Accessibility** - Semantic HTML, ARIA labels

### Responsive Design
- ✅ **Mobile-First** - Optimized for mobile devices
- ✅ **Tablet Support** - Tablet-friendly layouts
- ✅ **Desktop Optimized** - Full desktop experience
- ✅ **Flexible Grid** - CSS Grid and Flexbox
- ✅ **Media Queries** - Breakpoint-based design

---

## 🔐 Security Features

### Authentication & Authorization
- ✅ **Session Management** - Secure PHP sessions
- ✅ **Role-Based Access** - Admin/Customer role separation
- ✅ **Password Security** - Password hashing (password_hash)
- ✅ **SQL Injection Prevention** - PDO prepared statements
- ✅ **XSS Protection** - Input sanitization (htmlspecialchars)
- ✅ **CSRF Protection** - Session-based token system
- ✅ **Admin Session Isolation** - Separate admin sessions

### Data Validation
- ✅ **Input Sanitization** - Clean user inputs
- ✅ **Form Validation** - Client and server-side validation
- ✅ **Error Handling** - Graceful error management
- ✅ **Database Transactions** - ACID compliance
- ✅ **Data Type Validation** - Type checking

---

## 🚀 Installation & Setup

### System Requirements
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Apache:** 2.4 or higher (via XAMPP)
- **Web Browser:** Modern browser (Chrome, Firefox, Edge, Safari)
- **XAMPP:** Latest version recommended

### Installation Steps

#### 1. **Download & Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install XAMPP on your system
   - Start Apache and MySQL services

#### 2. **Database Setup**
   ```sql
   -- Step 1: Create database
   CREATE DATABASE adhee_hardware;
   
   -- Step 2: Import base schema
   -- Import: adhee_hardware (2).sql
   
   -- Step 3: Run updates
   -- Import: database_update.sql
   
   -- Step 4: Create notifications table
   -- Import: create_notifications_table.sql
   
   -- Step 5: (Optional) Add sample data
   -- Import: sample_data.sql
   ```

#### 3. **Project Setup**
   - Copy project folder to `C:\xampp\htdocs\Adhee Hardware Shop\`
   - Update `db_config.php` with your database credentials:
     ```php
     $host = '127.0.0.1';
     $dbname = 'adhee_hardware';
     $username = 'root';
     $password = '';
     ```

#### 4. **Access Application**
   - **Home Page:** `http://localhost/Adhee%20Hardware%20Shop/Index.php`
   - **Admin Dashboard:** `http://localhost/Adhee%20Hardware%20Shop/admin_dashboard.php`
   - **Admin Login:** `http://localhost/Adhee%20Hardware%20Shop/admin_login.php`
   - **Database Admin:** `http://localhost/phpmyadmin`

#### 5. **Create Admin User**
   - Register a user through the registration page
   - Update user role in database:
     ```sql
     UPDATE users SET role = 'admin' WHERE username = 'your_username';
     ```
   - Or use admin login page to create admin account

---

## 📊 System Capabilities

### Performance
- ✅ **AJAX-Powered** - Asynchronous operations (no page reloads)
- ✅ **Optimized Queries** - Efficient database operations
- ✅ **Lazy Loading** - Images and content loading
- ✅ **CDN Integration** - Font Awesome via CDN
- ✅ **Caching Ready** - Structure for caching implementation

### Scalability
- ✅ **Modular Architecture** - Separate API endpoints
- ✅ **Database Normalization** - Efficient data structure
- ✅ **API-First Design** - Ready for mobile apps
- ✅ **Component-Based CSS** - Maintainable styles
- ✅ **Separation of Concerns** - Clean code structure

### Business Functions
- ✅ **Inventory Management** - Real-time stock tracking
- ✅ **Order Processing** - Complete order lifecycle
- ✅ **Customer Management** - Customer database
- ✅ **Supplier Management** - Supplier network
- ✅ **Reporting** - Dashboard statistics
- ✅ **Notifications** - Real-time customer updates

---

## 🎯 Key Features Highlight

### ⭐ Notification System (NEW)
- Real-time order status notifications
- Bell icon in header with unread count
- Beautiful notification dropdown
- Auto-refresh every 30 seconds
- Mark as read functionality
- Automatic notification creation on status update

### ⭐ Styled Status Modal (NEW)
- Beautiful modal for order status updates
- Status button selection (Pending, Processing, Completed, Cancelled)
- Custom status input field
- Smooth animations
- Keyboard shortcuts (Enter, Escape)

### ⭐ Admin Dashboard
- Complete product management
- Supplier management
- Customer management
- Order management with status updates
- Low stock alerts
- Real-time statistics

### ⭐ Shopping Experience
- Product browsing and search
- Shopping cart functionality
- Checkout process
- Order tracking
- Order history
- Real-time notifications

---

## 🔮 Future Enhancements

### Planned Features
- **Payment Gateway Integration** - Online payment processing (Stripe, PayPal)
- **Email Notifications** - Automated email alerts
- **Advanced Reporting** - Business intelligence and analytics
- **Mobile App** - Native mobile application
- **Multi-Store Support** - Multiple location management
- **Inventory Forecasting** - Predictive stock management
- **Customer Reviews** - Product review system
- **Wishlist** - Save products for later
- **Coupon System** - Discount codes and promotions

### Technical Improvements
- **API Documentation** - Complete API documentation (Swagger)
- **Unit Testing** - Comprehensive test suite (PHPUnit)
- **Performance Optimization** - Speed enhancements
- **Security Hardening** - Advanced security features
- **Docker Support** - Containerization
- **CI/CD Pipeline** - Automated deployment

---

## 📞 Support & Maintenance

### File Locations
- **Main Application:** `C:\xampp\htdocs\Adhee Hardware Shop\`
- **Database:** MySQL database `adhee_hardware`
- **Admin Panel:** `admin_dashboard.php`
- **API Endpoints:** `/api/` directory

### Quick Access URLs
- **Home Page:** `http://localhost/Adhee%20Hardware%20Shop/Index.php`
- **Admin Dashboard:** `http://localhost/Adhee%20Hardware%20Shop/admin_dashboard.php`
- **Admin Login:** `http://localhost/Adhee%20Hardware%20Shop/admin_login.php`
- **Database Admin:** `http://localhost/phpmyadmin`

### Troubleshooting
- **Database Connection Issues:** Check `db_config.php` credentials
- **Session Problems:** Clear browser cache and cookies
- **Admin Access:** Ensure user has `role = 'admin'` in database
- **Notifications Not Showing:** Run `setup_notifications.php` to create table

---

## 📝 Development Notes

### Code Structure
- **MVC Pattern** - Model-View-Controller architecture
- **RESTful APIs** - Standard API design
- **PDO Database** - Secure database access
- **Session Management** - Secure user sessions
- **Error Handling** - Comprehensive error management

### Best Practices
- ✅ Prepared statements for SQL queries
- ✅ Input sanitization and validation
- ✅ Password hashing
- ✅ Session security
- ✅ Responsive design
- ✅ Clean code structure
- ✅ Commented code

---

## 🎉 Project Status

**System Status:** ✅ FULLY FUNCTIONAL  
**Last Updated:** December 2025  
**Version:** 2.0  
**Total Files:** 100+  
**Total API Endpoints:** 18  
**Database Tables:** 8  
**Features Implemented:** 50+  

---

## 📄 License

This project is proprietary software developed for Adhee Hardware Store.

---

## 👨‍💻 Development Team

**Project:** Adhee Hardware Store E-Commerce Platform  
**Type:** Full-Stack Web Application  
**Status:** Production Ready  

---

**This comprehensive e-commerce platform is ready for production use with all core business functions implemented, tested, and documented.**
