<?php
/**
 * Fix Admin Login - Diagnostic and Fix Tool
 */
session_start();
require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Admin Login</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #27ae60;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #e74c3c;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #3498db;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #f39c12;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:30px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
.btn{display:inline-block;padding:12px 24px;background:#27ae60;color:white;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;border:none;cursor:pointer;font-size:16px;}
.btn:hover{background:#229954;}
.btn-primary{background:#3498db;}
.btn-primary:hover{background:#2980b9;}
input[type='text'],input[type='password']{padding:10px;width:250px;border:2px solid #ddd;border-radius:4px;font-size:14px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Fix Admin Login - Diagnostic Tool</h1>";

try {
    // Step 1: Check database connection
    echo "<h2>Step 1: Database Connection</h2>";
    echo "<div class='success'>✅ Database connected successfully</div>";
    
    // Step 2: Check if role column exists
    echo "<h2>Step 2: Check Role Column</h2>";
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    
    if (!$roleColumnExists) {
        echo "<div class='warning'>⚠️ Role column does NOT exist. Creating it now...</div>";
        $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
        echo "<div class='success'>✅ Role column created successfully!</div>";
        $roleColumnExists = true;
    } else {
        echo "<div class='success'>✅ Role column exists</div>";
    }
    
    // Step 3: Show all users
    echo "<h2>Step 3: All Users in Database</h2>";
    $stmt = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) === 0) {
        echo "<div class='error'>❌ No users found in database. Please create a user first.</div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Current Role</th><th>Action</th></tr>";
        $adminCount = 0;
        foreach($users as $u) {
            $role = $u['role'] ?? 'NULL';
            $isAdmin = (strtolower(trim($role)) === 'admin');
            if ($isAdmin) $adminCount++;
            $rowStyle = $isAdmin ? "background:#d4edda;" : "";
            echo "<tr style='$rowStyle'>";
            echo "<td>{$u['id']}</td>";
            echo "<td><strong>{$u['username']}</strong>" . ($isAdmin ? ' 👑' : '') . "</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td><strong>" . ($role ?: 'NULL') . "</strong></td>";
            if (!$isAdmin) {
                echo "<td><a href='?make_admin={$u['id']}' class='btn'>Make Admin</a></td>";
            } else {
                echo "<td><span style='color:#27ae60;font-weight:bold;'>✓ Already Admin</span></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        echo "<div class='info'>Total users: " . count($users) . " | Admin users: $adminCount</div>";
    }
    
    // Step 4: Handle make admin request
    if (isset($_GET['make_admin'])) {
        $userId = intval($_GET['make_admin']);
        $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $updateStmt->execute([$userId]);
        
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='success'>";
        echo "✅ <strong>SUCCESS!</strong> User '<strong>{$user['username']}</strong>' (ID: $userId) is now an admin!<br>";
        echo "You can now login through admin_login.php";
        echo "</div>";
        echo "<script>setTimeout(function(){ window.location.href='fix_admin_login_now.php'; }, 2000);</script>";
    }
    
    // Step 5: Test login form
    echo "<h2>Step 4: Test Admin Login</h2>";
    echo "<div class='info'>";
    echo "To login as admin:<br>";
    echo "1. Make sure your user has role='admin' (use 'Make Admin' button above if needed)<br>";
    echo "2. Go to <a href='admin_login.php' style='color:#3498db;font-weight:bold;'>admin_login.php</a><br>";
    echo "3. Enter your username and password";
    echo "</div>";
    
    // Step 6: Quick test
    echo "<h2>Step 5: Quick Test</h2>";
    if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
        $test_user = trim($_POST['test_username']);
        $test_pass = trim($_POST['test_password']);
        
        echo "<div class='info'>Testing login for user: <strong>$test_user</strong></div>";
        
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
        $stmt->execute([$test_user, $test_user]);
        $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testUser) {
            echo "<div class='success'>✅ User found!</div>";
            
            // Check password
            $passwordMatch = false;
            $storedPass = $testUser['password'];
            
            if (strlen($storedPass) >= 60 && password_verify($test_pass, $storedPass)) {
                $passwordMatch = true;
            } elseif (trim($test_pass) === trim($storedPass)) {
                $passwordMatch = true;
            } elseif (strtolower(trim($test_pass)) === strtolower(trim($storedPass))) {
                $passwordMatch = true;
            } elseif (strlen($storedPass) === 32 && md5($test_pass) === $storedPass) {
                $passwordMatch = true;
            }
            
            if ($passwordMatch) {
                echo "<div class='success'>✅ Password is correct!</div>";
                
                $userRole = isset($testUser['role']) && $testUser['role'] !== null ? strtolower(trim($testUser['role'])) : '';
                if ($userRole === 'admin') {
                    echo "<div class='success'>✅ User has admin role! Login should work.</div>";
                    echo "<div class='info'><a href='admin_login.php' class='btn btn-primary'>Go to Admin Login Page</a></div>";
                } else {
                    echo "<div class='error'>❌ User does NOT have admin role. Current role: '$userRole'</div>";
                    echo "<div class='warning'>Click 'Make Admin' button above to fix this.</div>";
                }
            } else {
                echo "<div class='error'>❌ Password is INCORRECT</div>";
            }
        } else {
            echo "<div class='error'>❌ User not found!</div>";
        }
    }
    
    echo "<form method='POST' style='margin:20px 0;padding:20px;background:#f8f9fa;border-radius:5px;'>";
    echo "<h3>Test Login Credentials:</h3>";
    echo "<p>Username: <input type='text' name='test_username' required></p>";
    echo "<p>Password: <input type='password' name='test_password' required></p>";
    echo "<button type='submit' class='btn'>Test Login</button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<div class='info'>";
    echo "<strong>Summary:</strong><br>";
    echo "• Role column: " . ($roleColumnExists ? "✅ Exists" : "❌ Missing") . "<br>";
    echo "• Total users: " . count($users) . "<br>";
    echo "• Admin users: $adminCount<br>";
    if ($adminCount === 0) {
        echo "<div class='warning' style='margin-top:10px;'><strong>⚠️ No admin users found!</strong> Use 'Make Admin' button above to create one.</div>";
    }
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>

