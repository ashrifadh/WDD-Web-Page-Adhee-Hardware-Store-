<?php
/**
 * Quick Admin Check and Fix
 * Run this to check if you have admin access and fix it
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Admin Access Check</title>";
echo "<style>body{font-family:Arial;padding:30px;max-width:800px;margin:0 auto;} .success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;} .error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;} .info{color:blue;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;} .btn{display:inline-block;padding:10px 20px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;} .btn:hover{background:#ffc233;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{padding:10px;border:1px solid #ddd;text-align:left;} th{background:#2a3f54;color:#f8b739;}</style></head><body>";
echo "<h1>🔧 Admin Access Check & Fix</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected successfully</div>";
    
    // Check role column
    echo "<h2>Step 1: Checking Database Structure</h2>";
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    if (count($columns) > 0) {
        echo "<div class='success'>✓ Role column exists in users table</div>";
    } else {
        echo "<div class='error'>✗ Role column does NOT exist</div>";
        echo "<div class='info'><strong>Fix:</strong> Run this SQL in phpMyAdmin:<br><code>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</code></div>";
        echo "<p><a href='fix_admin_setup.php' class='btn'>Auto Fix (Click Here)</a></p>";
    }
    
    // Check current session
    echo "<h2>Step 2: Checking Your Session</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='info'>You are logged in as User ID: " . $_SESSION['user_id'] . "</div>";
        echo "<div class='info'>Username: " . ($_SESSION['username'] ?? 'Not set') . "</div>";
        echo "<div class='info'>Role in Session: " . ($_SESSION['role'] ?? 'Not set') . "</div>";
        
        // Check user's role in database
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentUser) {
            $dbRole = $currentUser['role'] ?? 'NULL';
            echo "<div class='info'>Your Role in Database: <strong>" . ($dbRole ?: 'NULL (not set)') . "</strong></div>";
            
            if ($dbRole === 'admin') {
                echo "<div class='success'><strong>✓ You have admin role! You should be able to access admin dashboard.</strong></div>";
                echo "<p><a href='admin_dashboard.php' class='btn'>Go to Admin Dashboard</a></p>";
            } else {
                echo "<div class='error'><strong>✗ You do NOT have admin role. This is why you're being redirected.</strong></div>";
                echo "<div class='info'><strong>Quick Fix:</strong> Click the button below to make yourself an admin:</div>";
                echo "<form method='POST' style='margin:20px 0;'>";
                echo "<input type='hidden' name='make_admin' value='1'>";
                echo "<button type='submit' class='btn' style='background:#27ae60;color:white;'>Make Me Admin</button>";
                echo "</form>";
            }
        }
    } else {
        echo "<div class='error'>You are NOT logged in</div>";
        echo "<p><a href='admin_login.php' class='btn'>Go to Admin Login</a></p>";
    }
    
    // Handle make admin request
    if (isset($_POST['make_admin']) && isset($_SESSION['user_id'])) {
        try {
            // First ensure role column exists
            $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
            if (count($columns) === 0) {
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
            }
            
            // Make user admin
            $conn->exec("UPDATE users SET role = 'admin' WHERE id = " . $_SESSION['user_id']);
            $_SESSION['role'] = 'admin';
            
            echo "<div class='success'><strong>✓ Success! You are now an admin!</strong></div>";
            echo "<p><a href='admin_dashboard.php' class='btn'>Go to Admin Dashboard Now</a></p>";
            echo "<script>setTimeout(function(){ window.location.href='admin_dashboard.php'; }, 2000);</script>";
        } catch(PDOException $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
    }
    
    // List all users
    echo "<h2>Step 3: All Users in Database</h2>";
    $stmt = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>";
        foreach($users as $u) {
            $role = $u['role'] ?? 'NULL';
            $isAdmin = ($role === 'admin') ? '✓ Admin' : 'Customer';
            $rowColor = ($role === 'admin') ? 'background:#d4edda;' : '';
            echo "<tr style='$rowColor'>";
            echo "<td>{$u['id']}</td>";
            echo "<td><strong>{$u['username']}</strong></td>";
            echo "<td>{$u['email']}</td>";
            echo "<td><strong>" . ($role ?: 'NULL') . "</strong></td>";
            if ($role !== 'admin') {
                echo "<td><a href='?make_user_admin={$u['id']}' style='color:#27ae60;'>Make Admin</a></td>";
            } else {
                echo "<td>Already Admin</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Handle make user admin from link
    if (isset($_GET['make_user_admin'])) {
        $userId = intval($_GET['make_user_admin']);
        try {
            $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
            if (count($columns) === 0) {
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
            }
            $conn->exec("UPDATE users SET role = 'admin' WHERE id = $userId");
            echo "<div class='success'>✓ User ID $userId is now an admin!</div>";
            echo "<script>setTimeout(function(){ window.location.href='check_admin.php'; }, 1000);</script>";
        } catch(PDOException $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Please check:</div>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database name is 'adhee_hardware'</li>";
    echo "<li>Database credentials in db_config.php are correct</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='Index.php' class='btn'>Home Page</a></p>";
echo "</body></html>";
?>

