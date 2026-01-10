<?php
/**
 * Force Admin Access - This WILL work, guaranteed!
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Force Admin Access</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:700px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:20px;border-radius:5px;margin:15px 0;border:2px solid #c3e6cb;font-size:18px;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
.btn{display:inline-block;padding:15px 30px;background:#27ae60;color:white;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;font-size:18px;border:none;cursor:pointer;}
.btn:hover{background:#229954;}
.btn-large{font-size:20px;padding:20px 40px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
input[type='text']{padding:12px;width:300px;border:2px solid #ddd;border-radius:5px;font-size:16px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Force Admin Access - Guaranteed Fix</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='info'>✓ Database connected</div>";
    
    $username = $_GET['username'] ?? $_POST['username'] ?? '';
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    // Step 1: Ensure role column exists
    echo "<h2>Step 1: Checking Role Column</h2>";
    try {
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
        if (count($columns) === 0) {
            echo "<div class='warning'>⚠ Role column missing. Adding it now...</div>";
            try {
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                echo "<div class='success'>✓ Role column added!</div>";
            } catch(PDOException $e) {
                // Try alternative
                try {
                    $conn->exec("ALTER TABLE users ADD COLUMN role ENUM('customer','admin') DEFAULT 'customer'");
                    echo "<div class='success'>✓ Role column added!</div>";
                } catch(PDOException $e2) {
                    echo "<div class='error'>✗ Could not add role column: " . $e2->getMessage() . "</div>";
                    echo "<div class='info'>Please run this SQL manually: <code>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</code></div>";
                }
            }
        } else {
            echo "<div class='success'>✓ Role column exists</div>";
        }
    } catch(PDOException $e) {
        echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
    
    // Step 2: Find user
    echo "<h2>Step 2: Finding User</h2>";
    $user = null;
    $userId = null;
    
    if ($username) {
        // Try exact match first
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Try case-insensitive
            $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    // If no username provided, show all users
    if (!$user && !$username) {
        echo "<div class='info'>Enter your username or select from list:</div>";
        echo "<form method='GET'>";
        echo "<p><label>Username: <input type='text' name='username' required></label></p>";
        echo "<button type='submit' class='btn'>Find User</button>";
        echo "</form>";
        
        // Show all users
        $allUsers = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
        $users = $allUsers->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<h3>Or select from existing users:</h3>";
            echo "<table>";
            echo "<tr><th>Username</th><th>Email</th><th>Current Role</th><th>Action</th></tr>";
            foreach($users as $u) {
                $role = $u['role'] ?? 'NULL';
                $isAdmin = (strtolower(trim($role)) === 'admin');
                $rowStyle = $isAdmin ? "background:#d4edda;" : "";
                echo "<tr style='$rowStyle'>";
                echo "<td><strong>{$u['username']}</strong></td>";
                echo "<td>{$u['email']}</td>";
                echo "<td>" . ($role ?: 'NULL') . ($isAdmin ? ' ✅' : '') . "</td>";
                if (!$isAdmin) {
                    echo "<td><a href='?username=" . urlencode($u['username']) . "&action=make_admin' class='btn' style='padding:8px 16px;font-size:14px;'>Make Admin</a></td>";
                } else {
                    echo "<td>Already Admin</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Step 3: Make admin
    if ($user || ($action === 'make_admin' && $username)) {
        if (!$user && $username) {
            $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($user) {
            echo "<div class='success'>✓ Found user: <strong>{$user['username']}</strong> (ID: {$user['id']})</div>";
            
            $currentRole = $user['role'] ?? 'NULL';
            echo "<div class='info'>Current role: " . ($currentRole ?: 'NULL') . "</div>";
            
            if ($action === 'make_admin' || isset($_POST['confirm'])) {
                echo "<h2>Step 3: Granting Admin Access</h2>";
                
                // Make user admin - try multiple methods
                $success = false;
                
                // Method 1: Direct update
                try {
                    $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    $success = true;
                    echo "<div class='success'>✓ Method 1: Direct update successful</div>";
                } catch(PDOException $e) {
                    echo "<div class='warning'>Method 1 failed: " . $e->getMessage() . "</div>";
                }
                
                // Method 2: Case-insensitive update
                if (!$success) {
                    try {
                        $conn->exec("UPDATE users SET role = 'admin' WHERE id = " . $user['id']);
                        $success = true;
                        echo "<div class='success'>✓ Method 2: Direct SQL successful</div>";
                    } catch(PDOException $e) {
                        echo "<div class='warning'>Method 2 failed: " . $e->getMessage() . "</div>";
                    }
                }
                
                // Verify
                $verifyStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                $verifyStmt->execute([$user['id']]);
                $verified = $verifyStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($verified && strtolower(trim($verified['role'])) === 'admin') {
                    echo "<div class='success' style='font-size:20px;padding:30px;'>";
                    echo "<h2>✅ SUCCESS!</h2>";
                    echo "<p><strong>User '{$user['username']}' is now an ADMIN!</strong></p>";
                    echo "<p>You can now login to the admin dashboard.</p>";
                    echo "</div>";
                    
                    // Update session if logged in
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) {
                        $_SESSION['role'] = 'admin';
                    }
                    
                    echo "<div class='info'>";
                    echo "<h3>Next Steps:</h3>";
                    echo "<ol style='font-size:16px;line-height:2;'>";
                    echo "<li>Go to <a href='admin_login.php' style='color:#27ae60;font-weight:bold;font-size:18px;'>Admin Login Page</a></li>";
                    echo "<li>Login with username: <strong>{$user['username']}</strong></li>";
                    echo "<li>Enter your password</li>";
                    echo "<li>You will be redirected to admin dashboard ✅</li>";
                    echo "</ol>";
                    echo "</div>";
                    
                    echo "<p><a href='admin_login.php' class='btn btn-large'>Go to Admin Login Now</a></p>";
                    
                    // Auto-redirect
                    echo "<script>setTimeout(function(){ window.location.href='admin_login.php'; }, 2000);</script>";
                } else {
                    echo "<div class='error'>";
                    echo "<h3>❌ Verification Failed</h3>";
                    echo "<p>Role after update: " . ($verified['role'] ?? 'NULL') . "</p>";
                    echo "<p>Please try the SQL method manually in phpMyAdmin:</p>";
                    echo "<code>UPDATE users SET role = 'admin' WHERE id = {$user['id']};</code>";
                    echo "</div>";
                }
            } else {
                // Show confirmation
                echo "<h2>Step 3: Confirm Admin Access</h2>";
                echo "<div class='warning'>";
                echo "<p>You are about to grant admin access to: <strong>{$user['username']}</strong></p>";
                echo "</div>";
                echo "<form method='POST'>";
                echo "<input type='hidden' name='username' value='{$user['username']}'>";
                echo "<input type='hidden' name='confirm' value='1'>";
                echo "<button type='submit' class='btn btn-large'>YES, Grant Admin Access</button>";
                echo "</form>";
            }
        } else {
            echo "<div class='error'>User not found: $username</div>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='Index.php' class='btn'>Home</a></p>";
echo "</div></body></html>";
?>

