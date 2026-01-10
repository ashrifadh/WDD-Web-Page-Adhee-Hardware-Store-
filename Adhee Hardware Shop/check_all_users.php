<?php
/**
 * Check All Users - See all users and their login status
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Check All Users</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:1000px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:10px;border-radius:5px;margin:5px 0;}
.error{color:#721c24;background:#f8d7da;padding:10px;border-radius:5px;margin:5px 0;}
.info{color:#0c5460;background:#d1ecf1;padding:10px;border-radius:5px;margin:5px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:5px 0;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:20px;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
tr:nth-child(even){background:#f9f9f9;}
.btn{display:inline-block;padding:8px 16px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:5px;font-weight:bold;font-size:14px;}
.btn:hover{background:#ffc233;}
.btn-test{background:#27ae60;color:white;}
.password-preview{font-family:monospace;font-size:11px;color:#6c757d;max-width:200px;overflow:hidden;text-overflow:ellipsis;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>👥 All Users Database Check</h1>";

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Database connected</div>";
    
    // Get all users
    $stmt = $conn->query("SELECT id, username, email, password, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) === 0) {
        echo "<div class='error'>No users found in database</div>";
    } else {
        echo "<div class='info'>Found <strong>" . count($users) . "</strong> user(s) in database</div>";
        
        echo "<h2>All Users</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Preview</th><th>Password Type</th><th>Role</th><th>Test Login</th></tr>";
        
        foreach($users as $user) {
            $password = $user['password'];
            $passwordLength = strlen($password);
            $passwordPreview = substr($password, 0, 30) . ($passwordLength > 30 ? '...' : '');
            
            // Determine password type
            if ($passwordLength > 60) {
                $passwordType = '<span style="color:#27ae60;">Hashed (bcrypt)</span>';
            } elseif ($passwordLength > 20) {
                $passwordType = '<span style="color:#f39c12;">Possibly Hashed</span>';
            } elseif ($passwordLength > 0) {
                $passwordType = '<span style="color:#e74c3c;">Plain Text</span>';
            } else {
                $passwordType = '<span style="color:#95a5a6;">Empty</span>';
            }
            
            $role = $user['role'] ?? 'NULL';
            $isAdmin = (strtolower(trim($role)) === 'admin');
            $rowStyle = $isAdmin ? "background:#d4edda;" : "";
            
            echo "<tr style='$rowStyle'>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong>" . ($isAdmin ? ' 👑' : '') . "</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td class='password-preview' title='$passwordPreview'>$passwordPreview</td>";
            echo "<td>$passwordType</td>";
            echo "<td>" . ($role ?: 'NULL') . "</td>";
            echo "<td><a href='test_login.php?user_id={$user['id']}' class='btn btn-test'>Test Login</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show login test form
        if (isset($_GET['user_id'])) {
            $userId = intval($_GET['user_id']);
            $testStmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE id = ?");
            $testStmt->execute([$userId]);
            $testUser = $testStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($testUser) {
                echo "<div class='warning' style='margin-top:30px;'>";
                echo "<h2>Test Login for: {$testUser['username']}</h2>";
                
                if (isset($_POST['test_password'])) {
                    $testPassword = $_POST['test_password'];
                    $storedPass = $testUser['password'];
                    
                    // Test all methods
                    $result1 = password_verify($testPassword, $storedPass);
                    $result2 = (trim($testPassword) === trim($storedPass));
                    $result3 = (strtolower(trim($testPassword)) === strtolower(trim($storedPass)));
                    
                    echo "<div class='info'>";
                    echo "<h3>Password Test Results:</h3>";
                    echo "<p><strong>password_verify():</strong> " . ($result1 ? '✅ <span style="color:green;">MATCH</span>' : '❌ <span style="color:red;">NO MATCH</span>') . "</p>";
                    echo "<p><strong>Direct comparison:</strong> " . ($result2 ? '✅ <span style="color:green;">MATCH</span>' : '❌ <span style="color:red;">NO MATCH</span>') . "</p>";
                    echo "<p><strong>Case-insensitive:</strong> " . ($result3 ? '✅ <span style="color:green;">MATCH</span>' : '❌ <span style="color:red;">NO MATCH</span>') . "</p>";
                    
                    if ($result1 || $result2 || $result3) {
                        echo "<div class='success' style='margin-top:15px;'>";
                        echo "<h3>✅ Password is Valid!</h3>";
                        echo "<p>This user should be able to login.</p>";
                        if (strtolower(trim($testUser['role'] ?? '')) !== 'admin') {
                            echo "<p><strong>Note:</strong> User is not an admin. <a href='grant_admin.php?username=" . urlencode($testUser['username']) . "'>Grant Admin Access</a></p>";
                        } else {
                            echo "<p><strong>Status:</strong> User is an admin ✅</p>";
                        }
                        echo "</div>";
                    } else {
                        echo "<div class='error' style='margin-top:15px;'>";
                        echo "<h3>❌ Password Does Not Match</h3>";
                        echo "<p>The password you entered does not match the stored password.</p>";
                        echo "<p><strong>Stored password type:</strong> " . (strlen($storedPass) > 60 ? 'Hashed' : 'Plain Text') . "</p>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                
                echo "<form method='POST' style='margin-top:20px;'>";
                echo "<p><label><strong>Enter Password to Test:</strong><br>";
                echo "<input type='password' name='test_password' required style='padding:10px;width:300px;border:2px solid #ddd;border-radius:5px;margin-top:5px;'></label></p>";
                echo "<button type='submit' class='btn btn-test'>Test Password</button>";
                echo "</form>";
                echo "</div>";
            }
        }
        
        // Show quick actions
        echo "<div class='info' style='margin-top:30px;'>";
        echo "<h2>Quick Actions</h2>";
        echo "<p><a href='admin_login.php' class='btn'>Go to Admin Login</a> ";
        echo "<a href='fix_admin_now.php' class='btn'>Fix Admin Access</a> ";
        echo "<a href='grant_admin.php' class='btn'>Grant Admin to User</a></p>";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php'>Admin Login</a> | <a href='Index.php'>Home</a></p>";
echo "</div></body></html>";
?>

