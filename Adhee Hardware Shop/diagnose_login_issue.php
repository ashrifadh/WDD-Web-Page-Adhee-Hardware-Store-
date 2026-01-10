<?php
/**
 * Complete Login Diagnosis - Find out why only Fathima Adheena works
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Login Diagnosis</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:1000px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:20px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
tr:nth-child(even){background:#f9f9f9;}
.btn{display:inline-block;padding:10px 20px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;cursor:pointer;border:none;}
.btn:hover{background:#ffc233;}
.btn-success{background:#27ae60;color:white;}
.btn-danger{background:#e74c3c;color:white;}
input[type='text'],input[type='password']{padding:10px;width:300px;border:2px solid #ddd;border-radius:5px;margin:5px 0;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔍 Complete Login Diagnosis</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Get all users
    $stmt = $conn->query("SELECT id, username, email, password, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) === 0) {
        echo "<div class='error'>No users found</div>";
    } else {
        echo "<div class='info'>Found " . count($users) . " user(s)</div>";
        
        // Test login simulation for each user
        echo "<h2>Login Test Results</h2>";
        
        if (isset($_POST['test_all'])) {
            echo "<div class='warning'>";
            echo "<h3>Testing Login for All Users</h3>";
            echo "<p>Enter a test password to check which users can login with it:</p>";
            echo "<form method='POST'>";
            echo "<input type='hidden' name='test_all' value='1'>";
            echo "<p><label>Test Password: <input type='password' name='test_password' required></label></p>";
            echo "<button type='submit' class='btn btn-success'>Test All Users</button>";
            echo "</form>";
            echo "</div>";
            
            if (isset($_POST['test_password'])) {
                $testPassword = $_POST['test_password'];
                echo "<h3>Results for password: '{$testPassword}'</h3>";
                echo "<table>";
                echo "<tr><th>Username</th><th>Password Match</th><th>Method</th><th>Can Login?</th></tr>";
                
                foreach($users as $user) {
                    $storedPass = $user['password'];
                    $match = false;
                    $method = '';
                    
                    // Test all methods
                    if (strlen($storedPass) >= 60 && password_verify($testPassword, $storedPass)) {
                        $match = true;
                        $method = 'password_verify (hashed)';
                    } elseif (trim($testPassword) === trim($storedPass)) {
                        $match = true;
                        $method = 'Direct (plain text)';
                    } elseif (strtolower(trim($testPassword)) === strtolower(trim($storedPass))) {
                        $match = true;
                        $method = 'Case-insensitive';
                    } elseif (strlen($storedPass) === 32 && md5($testPassword) === $storedPass) {
                        $match = true;
                        $method = 'MD5 hash';
                    }
                    
                    $canLogin = $match ? '✅ YES' : '❌ NO';
                    $rowStyle = $match ? "background:#d4edda;" : "background:#f8d7da;";
                    
                    echo "<tr style='$rowStyle'>";
                    echo "<td><strong>{$user['username']}</strong></td>";
                    echo "<td>" . ($match ? '✅ MATCH' : '❌ NO MATCH') . "</td>";
                    echo "<td>$method</td>";
                    echo "<td><strong>$canLogin</strong></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<form method='POST'>";
            echo "<input type='hidden' name='test_all' value='1'>";
            echo "<p><label>Enter a password to test: <input type='password' name='test_password' required></label></p>";
            echo "<button type='submit' class='btn btn-success'>Test This Password for All Users</button>";
            echo "</form>";
        }
        
        // Show all users with details
        echo "<h2>All Users in Database</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Length</th><th>Password Type</th><th>Role</th><th>Actions</th></tr>";
        
        foreach($users as $user) {
            $password = $user['password'];
            $passwordLength = strlen($password);
            
            if ($passwordLength >= 60) {
                $passwordType = 'Hashed (bcrypt)';
            } elseif ($passwordLength === 32) {
                $passwordType = 'MD5 Hash';
            } elseif ($passwordLength > 0) {
                $passwordType = 'Plain Text';
            } else {
                $passwordType = 'Empty';
            }
            
            $role = $user['role'] ?? 'NULL';
            $isAdmin = (strtolower(trim($role)) === 'admin');
            $rowStyle = $isAdmin ? "background:#d4edda;" : "";
            
            echo "<tr style='$rowStyle'>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong>" . ($isAdmin ? ' 👑' : '') . "</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>$passwordLength</td>";
            echo "<td>$passwordType</td>";
            echo "<td>" . ($role ?: 'NULL') . "</td>";
            echo "<td>";
            echo "<a href='?reset_password={$user['id']}' class='btn' style='background:#e74c3c;color:white;padding:5px 10px;font-size:12px;'>Reset Password</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Handle password reset
        if (isset($_GET['reset_password'])) {
            $userId = intval($_GET['reset_password']);
            $resetStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $resetStmt->execute([$userId]);
            $resetUser = $resetStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resetUser && isset($_POST['new_password'])) {
                $newPassword = $_POST['new_password'];
                // Store as plain text for easy login
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$newPassword, $userId]);
                echo "<div class='success'>✓ Password reset for '{$resetUser['username']}' to: '{$newPassword}'</div>";
            } else {
                echo "<div class='warning'>";
                echo "<h3>Reset Password for: {$resetUser['username']}</h3>";
                echo "<form method='POST'>";
                echo "<p><label>New Password (will be stored as plain text):<br>";
                echo "<input type='password' name='new_password' required></label></p>";
                echo "<button type='submit' class='btn btn-success'>Reset Password</button>";
                echo "</form>";
                echo "</div>";
            }
        }
        
        // Quick fix: Make all passwords plain text
        echo "<div class='info' style='margin-top:30px;'>";
        echo "<h2>Quick Fix Option</h2>";
        echo "<p>If users can't login, you can reset all passwords to be plain text (easier for testing).</p>";
        echo "<form method='POST' onsubmit='return confirm(\"This will reset all passwords to 'password123'. Continue?\");'>";
        echo "<input type='hidden' name='reset_all_passwords' value='1'>";
        echo "<p><label>Set all passwords to: <input type='text' name='default_password' value='password123' required></label></p>";
        echo "<button type='submit' class='btn btn-danger'>Reset All Passwords</button>";
        echo "</form>";
        echo "</div>";
        
        // Handle reset all passwords
        if (isset($_POST['reset_all_passwords'])) {
            $defaultPassword = $_POST['default_password'];
            $updateAll = $conn->prepare("UPDATE users SET password = ?");
            $updateAll->execute([$defaultPassword]);
            echo "<div class='success'>✓ All passwords reset to: '{$defaultPassword}'</div>";
            echo "<p>All users can now login with this password.</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='Index.php' class='btn'>Home</a></p>";
echo "</div></body></html>";
?>

