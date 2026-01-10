<?php
/**
 * Make Me Admin - Quick one-click admin access grant
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Make Me Admin</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
.btn{display:inline-block;padding:15px 30px;background:#27ae60;color:white;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;font-size:18px;border:none;cursor:pointer;}
.btn:hover{background:#229954;}
.btn-large{font-size:20px;padding:20px 40px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>👑 Grant Admin Access</h1>";

try {
    require_once 'db_config.php';
    
    // Get username from session or URL
    $username = $_GET['username'] ?? $_SESSION['temp_username'] ?? '';
    
    if (isset($_POST['grant_admin']) || isset($_GET['auto_grant'])) {
        // Auto-grant admin to logged in user or specified user
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        } elseif ($username) {
            $findStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $findStmt->execute([$username, $username]);
            $foundUser = $findStmt->fetch(PDO::FETCH_ASSOC);
            if ($foundUser) {
                $userId = $foundUser['id'];
            }
        }
        
        if (isset($userId)) {
            // Ensure role column exists
            try {
                $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
                if (count($columns) === 0) {
                    $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                }
            } catch(PDOException $e) {
                // Column might already exist
            }
            
            // Make user admin
            $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $updateStmt->execute([$userId]);
            
            // Get user info
            $userStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            // Update session
            $_SESSION['role'] = 'admin';
            
            echo "<div class='success'>";
            echo "<h2>✅ Success!</h2>";
            echo "<p><strong>User '{$user['username']}' is now an admin!</strong></p>";
            echo "<p>You can now access the admin dashboard.</p>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<h3>Next Steps:</h3>";
            echo "<ol>";
            echo "<li>Go to <a href='admin_login.php' style='color:#27ae60;font-weight:bold;'>Admin Login Page</a></li>";
            echo "<li>Login with your username and password</li>";
            echo "<li>You will be redirected to the admin dashboard</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<p><a href='admin_login.php' class='btn btn-large'>Go to Admin Login Now</a></p>";
            
            // Auto-redirect after 3 seconds
            echo "<script>setTimeout(function(){ window.location.href='admin_login.php'; }, 3000);</script>";
        } else {
            echo "<div class='error'>Could not find user. Please login first or specify username.</div>";
        }
    } else {
        // Show form
        if ($username) {
            echo "<div class='info'>";
            echo "<p>You tried to login as: <strong>$username</strong></p>";
            echo "<p>This account doesn't have admin privileges yet.</p>";
            echo "</div>";
            
            echo "<form method='POST'>";
            echo "<p><button type='submit' name='grant_admin' class='btn btn-large'>Grant Admin Access to '$username'</button></p>";
            echo "</form>";
        } else {
            echo "<div class='info'>";
            echo "<p>This tool will grant admin access to your account.</p>";
            echo "</div>";
            
            // Show all users
            $allUsers = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
            $users = $allUsers->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo "<h2>Select User to Make Admin:</h2>";
                echo "<ul style='list-style:none;padding:0;'>";
                foreach($users as $u) {
                    $role = $u['role'] ?? 'customer';
                    $isAdmin = (strtolower(trim($role)) === 'admin');
                    $adminBadge = $isAdmin ? ' ✅ Already Admin' : '';
                    echo "<li style='padding:10px;margin:5px 0;background:#f8f9fa;border-radius:5px;'>";
                    echo "<strong>{$u['username']}</strong> ({$u['email']})$adminBadge";
                    if (!$isAdmin) {
                        echo " <a href='?auto_grant=1&username=" . urlencode($u['username']) . "' class='btn' style='padding:8px 16px;font-size:14px;margin-left:10px;'>Make Admin</a>";
                    }
                    echo "</li>";
                }
                echo "</ul>";
            }
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='Index.php' class='btn'>Home</a></p>";
echo "</div></body></html>";
?>

