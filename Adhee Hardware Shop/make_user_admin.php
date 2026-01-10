<?php
/**
 * Make a user admin - Simple tool to set user role to admin
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Make User Admin</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
.btn{display:inline-block;padding:10px 20px;background:#27ae60;color:white;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;}
.btn:hover{background:#229954;}
input[type='text']{padding:8px;width:200px;border:1px solid #ddd;border-radius:4px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>👑 Make User Admin</h1>";

try {
    // Check if role column exists, if not create it
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    if (count($columns) === 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
        echo "<div class='info'>✓ Role column created</div>";
    }
    
    // Handle making user admin
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Update user role to admin
            $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            echo "<div class='success'>";
            echo "✅ <strong>SUCCESS!</strong> User '<strong>{$user['username']}</strong>' (ID: {$user['id']}) is now an admin!<br>";
            echo "You can now login through admin_login.php with username: <strong>{$user['username']}</strong>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ User '$username' not found in database. Please check the username or email.</div>";
        }
    }
    
    // Show all users
    echo "<h2>All Users</h2>";
    $stmt = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Current Role</th></tr>";
        foreach($users as $u) {
            $role = $u['role'] ?? 'NULL';
            $isAdmin = (strtolower(trim($role)) === 'admin');
            $rowStyle = $isAdmin ? "background:#d4edda;" : "";
            echo "<tr style='$rowStyle'>";
            echo "<td>{$u['id']}</td>";
            echo "<td><strong>{$u['username']}</strong>" . ($isAdmin ? ' 👑' : '') . "</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td><strong>" . ($role ?: 'NULL') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Form to make user admin
    echo "<h2>Make User Admin</h2>";
    echo "<form method='POST' style='margin:20px 0;'>";
    echo "<p>Enter username or email to make them admin:</p>";
    echo "<input type='text' name='username' placeholder='Enter username or email' required>";
    echo "<button type='submit' class='btn'>Make Admin</button>";
    echo "</form>";
    
    echo "<div class='info'>";
    echo "<strong>Instructions:</strong><br>";
    echo "1. Enter the username or email (e.g., 'Sabee')<br>";
    echo "2. Click 'Make Admin' button<br>";
    echo "3. Then you can login through admin_login.php";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>

