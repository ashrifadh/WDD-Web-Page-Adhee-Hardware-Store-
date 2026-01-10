<?php
/**
 * Grant Admin Access to User
 * This script will make "Fathima Adheena" (or any user) an admin
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Grant Admin Access</title>";
echo "<style>body{font-family:Arial;padding:30px;max-width:600px;margin:0 auto;background:#f5f5f5;} .container{background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);} .success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;} .error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;} .info{color:blue;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;} .btn{display:inline-block;padding:12px 24px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;border:none;cursor:pointer;} .btn:hover{background:#ffc233;} h1{color:#2a3f54;} form{margin:20px 0;} input[type='text']{padding:10px;width:300px;border:2px solid #ddd;border-radius:5px;font-size:14px;}</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Grant Admin Access</h1>";

try {
    require_once 'db_config.php';
    
    // Get username from URL or form
    $username = $_GET['username'] ?? $_POST['username'] ?? 'Fathima Adheena';
    
    if ($_POST && isset($_POST['username'])) {
        $username = $_POST['username'];
        
        // Check if role column exists, if not add it
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
        if (count($columns) === 0) {
            try {
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                echo "<div class='success'>✓ Role column added to database</div>";
            } catch(PDOException $e) {
                // Try without IF NOT EXISTS for older MySQL
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                echo "<div class='success'>✓ Role column added to database</div>";
            }
        }
        
        // Find user by username
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Make user admin
            $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            echo "<div class='success'>";
            echo "<h2>✅ Success!</h2>";
            echo "<p><strong>User '{$user['username']}' is now an admin!</strong></p>";
            echo "<p>User ID: {$user['id']}</p>";
            echo "<p>Email: {$user['email']}</p>";
            echo "<p>Role: <strong>admin</strong></p>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<h3>Next Steps:</h3>";
            echo "<ol>";
            echo "<li>Go to <a href='admin_login.php' style='color:#f8b739;font-weight:bold;'>Admin Login Page</a></li>";
            echo "<li>Login with username: <strong>{$user['username']}</strong></li>";
            echo "<li>Enter your password</li>";
            echo "<li>You will be redirected to the admin dashboard</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<p><a href='admin_login.php' class='btn'>Go to Admin Login</a></p>";
            
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ User Not Found</h3>";
            echo "<p>No user found with username or email: <strong>$username</strong></p>";
            echo "<p>Please check the username and try again.</p>";
            echo "</div>";
            
            // Show all users
            $allUsers = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
            $users = $allUsers->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo "<div class='info'>";
                echo "<h3>Available Users:</h3>";
                echo "<ul>";
                foreach($users as $u) {
                    $role = $u['role'] ?? 'customer';
                    echo "<li><strong>{$u['username']}</strong> ({$u['email']}) - Role: $role</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
        }
    } else {
        // Show form
        echo "<div class='info'>";
        echo "<p>This tool will grant admin access to a user account.</p>";
        echo "<p><strong>Default:</strong> Will make 'Fathima Adheena' an admin</p>";
        echo "</div>";
        
        echo "<form method='POST'>";
        echo "<p><label><strong>Enter Username or Email:</strong></label><br>";
        echo "<input type='text' name='username' value='$username' placeholder='Enter username or email' required></p>";
        echo "<button type='submit' class='btn'>Grant Admin Access</button>";
        echo "</form>";
        
        // Show current users
        try {
            $allUsers = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
            $users = $allUsers->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo "<div class='info'>";
                echo "<h3>Current Users:</h3>";
                echo "<ul>";
                foreach($users as $u) {
                    $role = $u['role'] ?? 'customer';
                    $adminBadge = ($role === 'admin') ? ' ✅ ADMIN' : '';
                    echo "<li><strong>{$u['username']}</strong> ({$u['email']}) - Role: $role$adminBadge</li>";
                }
                echo "</ul>";
                echo "<p><em>Click on a username above to quickly grant admin access</em></p>";
                echo "</div>";
            }
        } catch(PDOException $e) {
            // Ignore if table doesn't exist
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database 'adhee_hardware' exists</li>";
    echo "<li>Database credentials in db_config.php are correct</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>

