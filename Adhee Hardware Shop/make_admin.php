<?php
/**
 * Script to make a user an admin
 * Usage: http://localhost/Adhee%20Hardware%20Shop/make_admin.php?user_id=1
 * Or edit the $user_id variable below
 */

require_once 'db_config.php';

// Get user ID from URL parameter or set it here
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1; // Default to user ID 1

try {
    // Check if role column exists
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    
    if (!$roleColumnExists) {
        // Add role column if it doesn't exist
        $conn->exec("ALTER TABLE users ADD COLUMN role ENUM('customer', 'admin') DEFAULT 'customer'");
        echo "✓ Role column added to users table<br>";
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("❌ Error: User with ID $user_id not found!");
    }
    
    // Update user role to admin
    $update_stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $update_stmt->execute([$user_id]);
    
    echo "<h2>✓ Success!</h2>";
    echo "<p>User <strong>" . htmlspecialchars($user['username']) . "</strong> (ID: $user_id) has been set as admin.</p>";
    echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
    echo "<p><a href='admin_login.php'>Go to Admin Login</a></p>";
    echo "<p><a href='Index.php'>Go to Home Page</a></p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>














