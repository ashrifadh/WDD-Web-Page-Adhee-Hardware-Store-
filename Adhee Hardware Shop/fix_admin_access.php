<?php
// Fix Admin Access - Run this script once to fix the database structure
require_once 'db_config.php';

echo "<h2>Fixing Admin Access Issue</h2>";

try {
    // Check if role column exists
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    
    if (!$roleColumnExists) {
        echo "<p>❌ Role column doesn't exist. Adding it now...</p>";
        
        // Add role column to users table
        $conn->exec("ALTER TABLE users ADD COLUMN role enum('customer','admin') DEFAULT 'customer'");
        echo "<p>✅ Role column added successfully!</p>";
    } else {
        echo "<p>✅ Role column already exists.</p>";
    }
    
    // Check current users
    $stmt = $conn->query("SELECT id, username, email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Users:</h3>";
    if (empty($users)) {
        echo "<p>❌ No users found in database.</p>";
        echo "<p>Please create a user account first by going to the signup page.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>";
        
        foreach ($users as $user) {
            $role = $user['role'] ?? 'customer';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($role) . "</td>";
            
            if ($role !== 'admin') {
                echo "<td><a href='?make_admin=" . $user['id'] . "' style='color: blue;'>Make Admin</a></td>";
            } else {
                echo "<td><strong>Admin</strong></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Handle make admin request
    if (isset($_GET['make_admin'])) {
        $userId = (int)$_GET['make_admin'];
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo "<p>✅ User ID $userId has been made an admin!</p>";
        echo "<p><a href='fix_admin_access.php'>Refresh Page</a></p>";
    }
    
    // Check if we have at least one admin
    $stmt = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['admin_count'];
    
    if ($adminCount > 0) {
        echo "<h3>✅ Admin Access Fixed!</h3>";
        echo "<p>You now have $adminCount admin user(s). You can now access the admin panel.</p>";
        echo "<p><a href='admin_login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    } else {
        echo "<h3>⚠️ No Admin Users</h3>";
        echo "<p>Please make at least one user an admin using the table above, or create a new account first.</p>";
        echo "<p><a href='admin_login.php?mode=signup' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create New Admin Account</a></p>";
    }
    
} catch(PDOException $e) {
    echo "<p>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
table { width: 100%; }
th, td { padding: 8px 12px; text-align: left; }
th { background: #f8f9fa; }
</style>