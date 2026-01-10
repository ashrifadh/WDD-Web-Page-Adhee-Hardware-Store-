<?php
/**
 * Quick Admin Setup Fix
 * This script automatically fixes common admin access issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Setup Fix</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

try {
    require_once 'db_config.php';
    echo "<p class='success'>✓ Database connected</p>";
    
    // Step 1: Add role column if it doesn't exist
    echo "<h3>Step 1: Checking role column...</h3>";
    try {
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
        if (count($columns) === 0) {
            echo "<p class='info'>Role column not found. Adding it now...</p>";
            $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
            echo "<p class='success'>✓ Role column added successfully</p>";
        } else {
            echo "<p class='success'>✓ Role column already exists</p>";
        }
    } catch(PDOException $e) {
        // Try with IF NOT EXISTS syntax (MySQL 8.0+)
        try {
            $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'customer'");
            echo "<p class='success'>✓ Role column added/verified</p>";
        } catch(PDOException $e2) {
            echo "<p class='error'>Could not add role column: " . $e2->getMessage() . "</p>";
            echo "<p class='info'>Please run this SQL manually in phpMyAdmin:</p>";
            echo "<pre>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</pre>";
        }
    }
    
    // Step 2: Check if any users exist
    echo "<h3>Step 2: Checking users...</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($userCount == 0) {
        echo "<p class='error'>No users found in database. Please create a user first through the signup page.</p>";
        echo "<p><a href='admin_login.php?mode=signup'>Go to Signup</a></p>";
    } else {
        echo "<p class='success'>✓ Found $userCount user(s)</p>";
        
        // Step 3: Check for admin users
        echo "<h3>Step 3: Checking for admin users...</h3>";
        $stmt = $conn->query("SELECT id, username, email FROM users WHERE role = 'admin'");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($admins) == 0) {
            echo "<p class='info'>No admin users found. Making first user an admin...</p>";
            
            // Get first user
            $stmt = $conn->query("SELECT id, username, email FROM users ORDER BY id LIMIT 1");
            $firstUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($firstUser) {
                $conn->exec("UPDATE users SET role = 'admin' WHERE id = " . $firstUser['id']);
                echo "<p class='success'>✓ User '{$firstUser['username']}' (ID: {$firstUser['id']}) is now an admin!</p>";
                echo "<p class='info'>You can now login with:</p>";
                echo "<ul>";
                echo "<li><strong>Username:</strong> {$firstUser['username']}</li>";
                echo "<li><strong>Email:</strong> {$firstUser['email']}</li>";
                echo "<li><strong>Password:</strong> (your password)</li>";
                echo "</ul>";
            }
        } else {
            echo "<p class='success'>✓ Found " . count($admins) . " admin user(s):</p>";
            echo "<ul>";
            foreach($admins as $admin) {
                echo "<li>ID: {$admin['id']}, Username: <strong>{$admin['username']}</strong>, Email: {$admin['email']}</li>";
            }
            echo "</ul>";
        }
    }
    
    // Step 4: Summary
    echo "<h3>Step 4: Summary</h3>";
    echo "<p class='success'>Setup complete! You should now be able to access the admin page.</p>";
    echo "<p><a href='admin_login.php' style='background:#f8b739;color:#2a3f54;padding:10px 20px;text-decoration:none;border-radius:5px;font-weight:bold;'>Go to Admin Login</a></p>";
    
} catch(PDOException $e) {
    echo "<p class='error'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Please check:</p>";
    echo "<ul>";
    echo "<li>Database name is 'adhee_hardware'</li>";
    echo "<li>MySQL is running in XAMPP</li>";
    echo "<li>Database credentials in db_config.php are correct</li>";
    echo "</ul>";
}
?>

