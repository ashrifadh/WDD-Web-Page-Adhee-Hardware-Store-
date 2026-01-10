<?php
/**
 * Test User Login - Check which users can login and why
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test User Login</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:10px;border-radius:5px;margin:5px 0;}
.error{color:#721c24;background:#f8d7da;padding:10px;border-radius:5px;margin:5px 0;}
.info{color:#0c5460;background:#d1ecf1;padding:10px;border-radius:5px;margin:5px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:5px 0;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
tr:nth-child(even){background:#f9f9f9;}
.password-type{font-size:12px;color:#6c757d;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔍 User Login Test</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Get all users
    $stmt = $conn->query("SELECT id, username, email, password, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) === 0) {
        echo "<div class='error'>No users found in database</div>";
    } else {
        echo "<h2>All Users in Database</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Type</th><th>Password Length</th><th>Role</th><th>Can Login?</th></tr>";
        
        foreach($users as $user) {
            $password = $user['password'];
            $passwordLength = strlen($password);
            
            // Determine password type
            $passwordType = 'Unknown';
            $canLogin = '❓';
            $loginNote = '';
            
            if ($passwordLength > 60) {
                $passwordType = 'Hashed (bcrypt)';
                $canLogin = '✅ Yes (if password_verify works)';
            } elseif ($passwordLength > 20) {
                $passwordType = 'Possibly Hashed';
                $canLogin = '⚠️ Maybe';
            } elseif ($passwordLength > 0) {
                $passwordType = 'Plain Text';
                $canLogin = '✅ Yes (direct comparison)';
            } else {
                $passwordType = 'Empty';
                $canLogin = '❌ No';
            }
            
            $role = $user['role'] ?? 'NULL';
            $isAdmin = (strtolower($role) === 'admin');
            $rowStyle = $isAdmin ? "background:#d4edda;" : "";
            
            echo "<tr style='$rowStyle'>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong>" . ($isAdmin ? ' 👑' : '') . "</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><span class='password-type'>$passwordType</span></td>";
            echo "<td>$passwordLength</td>";
            echo "<td>" . ($role ?: 'NULL') . "</td>";
            echo "<td>$canLogin</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='info'>";
        echo "<h3>Password Verification Info:</h3>";
        echo "<ul>";
        echo "<li><strong>Hashed passwords</strong> (length > 60): Use password_verify() function</li>";
        echo "<li><strong>Plain text passwords</strong> (length < 20): Use direct comparison</li>";
        echo "<li>If a user can't login, their password might be in a different format</li>";
        echo "</ul>";
        echo "</div>";
        
        // Test login for specific user
        if (isset($_GET['test_user'])) {
            $testUsername = $_GET['test_user'];
            echo "<div class='warning'>";
            echo "<h3>Testing Login for: $testUsername</h3>";
            
            $testStmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
            $testStmt->execute([$testUsername, $testUsername]);
            $testUser = $testStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($testUser) {
                echo "<p>User found: ID {$testUser['id']}</p>";
                echo "<p>Password stored: " . substr($testUser['password'], 0, 20) . "... (length: " . strlen($testUser['password']) . ")</p>";
                echo "<p>Role: " . ($testUser['role'] ?? 'NULL') . "</p>";
                
                if (isset($_POST['test_password'])) {
                    $testPassword = $_POST['test_password'];
                    $storedPass = $testUser['password'];
                    
                    $valid1 = password_verify($testPassword, $storedPass);
                    $valid2 = (trim($testPassword) === trim($storedPass));
                    $valid3 = (strtolower(trim($testPassword)) === strtolower(trim($storedPass)));
                    
                    echo "<div class='info'>";
                    echo "<h4>Password Test Results:</h4>";
                    echo "<p>password_verify(): " . ($valid1 ? '✅ MATCH' : '❌ NO MATCH') . "</p>";
                    echo "<p>Direct comparison: " . ($valid2 ? '✅ MATCH' : '❌ NO MATCH') . "</p>";
                    echo "<p>Case-insensitive: " . ($valid3 ? '✅ MATCH' : '❌ NO MATCH') . "</p>";
                    echo "</div>";
                } else {
                    echo "<form method='POST'>";
                    echo "<p><label>Test Password: <input type='password' name='test_password' required></label></p>";
                    echo "<button type='submit'>Test This Password</button>";
                    echo "</form>";
                }
            } else {
                echo "<p class='error'>User not found</p>";
            }
            echo "</div>";
        }
        
        // Show test links
        echo "<div class='info'>";
        echo "<h3>Test Login for Specific User:</h3>";
        echo "<ul>";
        foreach($users as $u) {
            echo "<li><a href='?test_user=" . urlencode($u['username']) . "'>Test: {$u['username']}</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php'>Admin Login</a> | <a href='Index.php'>Home</a></p>";
echo "</div></body></html>";
?>

