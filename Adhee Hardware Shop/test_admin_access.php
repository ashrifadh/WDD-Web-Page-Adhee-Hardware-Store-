<?php
/**
 * Admin Access Diagnostic Tool
 * This script helps identify why admin page is not opening
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Access Diagnostic</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

// Test 1: Database Connection
echo "<h3>1. Testing Database Connection</h3>";
try {
    require_once 'db_config.php';
    echo "<p class='success'>✓ Database connection successful</p>";
} catch(Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if users table exists
echo "<h3>2. Checking Users Table</h3>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Users table exists</p>";
    } else {
        echo "<p class='error'>✗ Users table does not exist</p>";
        exit;
    }
} catch(Exception $e) {
    echo "<p class='error'>✗ Error checking users table: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Check if role column exists
echo "<h3>3. Checking Role Column</h3>";
try {
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    if (count($columns) > 0) {
        echo "<p class='success'>✓ Role column exists</p>";
    } else {
        echo "<p class='error'>✗ Role column does NOT exist</p>";
        echo "<p class='info'>You need to run database_update.sql to add the role column</p>";
        echo "<pre>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</pre>";
    }
} catch(Exception $e) {
    echo "<p class='error'>✗ Error checking role column: " . $e->getMessage() . "</p>";
}

// Test 4: List all users
echo "<h3>4. Current Users in Database</h3>";
try {
    $stmt = $conn->query("SELECT id, username, email, role FROM users LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        foreach($users as $user) {
            $role = $user['role'] ?? '<em>NULL (not set)</em>';
            $rowClass = ($role === 'admin') ? 'success' : '';
            echo "<tr class='$rowClass'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><strong>$role</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ No users found in database</p>";
    }
} catch(Exception $e) {
    echo "<p class='error'>✗ Error fetching users: " . $e->getMessage() . "</p>";
}

// Test 5: Check for admin users
echo "<h3>5. Admin Users</h3>";
try {
    $stmt = $conn->query("SELECT id, username, email FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) > 0) {
        echo "<p class='success'>✓ Found " . count($admins) . " admin user(s):</p>";
        echo "<ul>";
        foreach($admins as $admin) {
            echo "<li>ID: {$admin['id']}, Username: <strong>{$admin['username']}</strong>, Email: {$admin['email']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ No admin users found</p>";
        echo "<p class='info'>To create an admin user, run this SQL in phpMyAdmin:</p>";
        echo "<pre>UPDATE users SET role = 'admin' WHERE id = 1;</pre>";
        echo "<p class='info'>Or if role column doesn't exist, first run:</p>";
        echo "<pre>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</pre>";
        echo "<pre>UPDATE users SET role = 'admin' WHERE id = 1;</pre>";
    }
} catch(Exception $e) {
    echo "<p class='error'>✗ Error checking admin users: " . $e->getMessage() . "</p>";
}

// Test 6: Session check
echo "<h3>6. Current Session Status</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p class='info'>Session active - User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p class='info'>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
    echo "<p class='info'>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
} else {
    echo "<p class='info'>No active session</p>";
}

// Test 7: File existence check
echo "<h3>7. Required Files Check</h3>";
$files = ['admin_login.php', 'admin_dashboard.php', 'db_config.php'];
foreach($files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file exists</p>";
    } else {
        echo "<p class='error'>✗ $file is missing</p>";
    }
}

// Test 8: Quick fix suggestions
echo "<h3>8. Quick Fix</h3>";
echo "<p class='info'><strong>If role column is missing, run this SQL:</strong></p>";
echo "<pre>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</pre>";
echo "<p class='info'><strong>To make user ID 1 an admin:</strong></p>";
echo "<pre>UPDATE users SET role = 'admin' WHERE id = 1;</pre>";
echo "<p class='info'><strong>To test login, use:</strong></p>";
echo "<pre>Username: (any username from users table)<br>Password: (the password stored in database)</pre>";

echo "<hr>";
echo "<p><a href='admin_login.php'>Go to Admin Login</a> | <a href='Index.php'>Go to Home</a></p>";
?>

