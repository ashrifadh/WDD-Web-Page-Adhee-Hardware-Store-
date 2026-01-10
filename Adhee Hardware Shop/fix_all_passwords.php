<?php
/**
 * Fix All Passwords - Make all passwords plain text for easy login
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix All Passwords</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:700px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
.btn{display:inline-block;padding:12px 24px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;cursor:pointer;border:none;font-size:16px;}
.btn:hover{background:#ffc233;}
.btn-success{background:#27ae60;color:white;}
.btn-danger{background:#e74c3c;color:white;}
input[type='text'],input[type='password']{padding:12px;width:100%;max-width:400px;border:2px solid #ddd;border-radius:5px;margin:10px 0;font-size:16px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Fix All User Passwords</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Get all users
    $stmt = $conn->query("SELECT id, username, email, password FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) === 0) {
        echo "<div class='error'>No users found in database</div>";
    } else {
        echo "<div class='info'>Found " . count($users) . " user(s) in database</div>";
        
        // Show current status
        echo "<h2>Current Password Status</h2>";
        echo "<div class='warning'>";
        echo "<p><strong>Problem:</strong> Users have passwords stored in different formats (hashed vs plain text), which causes login issues.</p>";
        echo "<p><strong>Solution:</strong> Set all passwords to the same plain text value so everyone can login easily.</p>";
        echo "</div>";
        
        // Handle password reset
        if (isset($_POST['set_password'])) {
            $newPassword = trim($_POST['new_password']);
            
            if (empty($newPassword)) {
                echo "<div class='error'>Password cannot be empty</div>";
            } else {
                // Update all passwords to plain text
                $updateStmt = $conn->prepare("UPDATE users SET password = ?");
                $updateStmt->execute([$newPassword]);
                
                echo "<div class='success'>";
                echo "<h2>✅ Success!</h2>";
                echo "<p><strong>All " . count($users) . " user passwords have been reset!</strong></p>";
                echo "<p>All users can now login with:</p>";
                echo "<ul>";
                echo "<li><strong>Username:</strong> Their username or email</li>";
                echo "<li><strong>Password:</strong> <code>$newPassword</code></li>";
                echo "</ul>";
                echo "</div>";
                
                echo "<div class='info'>";
                echo "<h3>Next Steps:</h3>";
                echo "<ol>";
                echo "<li>Go to <a href='admin_login.php' style='color:#f8b739;font-weight:bold;'>Admin Login Page</a></li>";
                echo "<li>Login with any username and password: <strong>$newPassword</strong></li>";
                echo "<li>You should be able to access the admin dashboard</li>";
                echo "</ol>";
                echo "</div>";
                
                echo "<p><a href='admin_login.php' class='btn btn-success'>Go to Admin Login Now</a></p>";
            }
        } else {
            // Show form
            echo "<form method='POST'>";
            echo "<h2>Set Password for All Users</h2>";
            echo "<div class='info'>";
            echo "<p>Enter a password that all users will use. This will make it easy for everyone to login.</p>";
            echo "<p><strong>Recommended:</strong> Use a simple password like 'password123' or 'admin123' for testing.</p>";
            echo "</div>";
            
            echo "<p><label><strong>New Password for All Users:</strong><br>";
            echo "<input type='password' name='new_password' value='password123' required placeholder='Enter password'></label></p>";
            
            echo "<p><button type='submit' name='set_password' class='btn btn-success'>Set This Password for All Users</button></p>";
            echo "</form>";
            
            // Show current users
            echo "<h2>Current Users</h2>";
            echo "<div class='info'>";
            echo "<ul>";
            foreach($users as $user) {
                $passType = strlen($user['password']) >= 60 ? 'Hashed' : 'Plain Text';
                echo "<li><strong>{$user['username']}</strong> ({$user['email']}) - Password: $passType</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='diagnose_login_issue.php' class='btn'>Diagnose Issues</a> <a href='Index.php' class='btn'>Home</a></p>";
echo "</div></body></html>";
?>

