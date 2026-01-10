<?php
/**
 * Complete Admin Fix - This will fix everything automatically
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Admin Access Now</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #c3e6cb;}
.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #f5c6cb;}
.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #bee5eb;}
.warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border:1px solid #ffeaa7;}
h1{color:#2a3f54;border-bottom:3px solid #f8b739;padding-bottom:10px;}
h2{color:#3a5068;margin-top:30px;}
.btn{display:inline-block;padding:12px 24px;background:#f8b739;color:#2a3f54;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;border:none;cursor:pointer;font-size:16px;}
.btn:hover{background:#ffc233;}
.btn-success{background:#27ae60;color:white;}
.btn-success:hover{background:#229954;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
th,td{padding:12px;border:1px solid #ddd;text-align:left;}
th{background:#2a3f54;color:#f8b739;}
tr:nth-child(even){background:#f9f9f9;}
.step{background:#f8f9fa;padding:15px;margin:15px 0;border-left:4px solid #f8b739;border-radius:5px;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Complete Admin Access Fix</h1>";

$allFixed = false;
$issues = [];
$fixes = [];

try {
    require_once 'db_config.php';
    echo "<div class='success'>✓ Step 1: Database connection successful</div>";
    
    // Step 2: Check and add role column
    echo "<div class='step'>";
    echo "<h2>Step 2: Checking Role Column</h2>";
    try {
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
        if (count($columns) === 0) {
            echo "<div class='warning'>⚠ Role column does not exist. Adding it now...</div>";
            try {
                $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                echo "<div class='success'>✓ Role column added successfully!</div>";
                $fixes[] = "Added role column to users table";
            } catch(PDOException $e) {
                // Try alternative syntax
                try {
                    $conn->exec("ALTER TABLE users ADD COLUMN role ENUM('customer','admin') DEFAULT 'customer'");
                    echo "<div class='success'>✓ Role column added successfully!</div>";
                    $fixes[] = "Added role column to users table";
                } catch(PDOException $e2) {
                    echo "<div class='error'>✗ Could not add role column: " . $e2->getMessage() . "</div>";
                    $issues[] = "Role column could not be added";
                    echo "<div class='info'><strong>Manual Fix:</strong> Run this SQL in phpMyAdmin:<br><code>ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';</code></div>";
                }
            }
        } else {
            echo "<div class='success'>✓ Role column already exists</div>";
        }
    } catch(PDOException $e) {
        echo "<div class='error'>✗ Error checking role column: " . $e->getMessage() . "</div>";
        $issues[] = "Error checking role column";
    }
    echo "</div>";
    
    // Step 3: Find and fix user
    echo "<div class='step'>";
    echo "<h2>Step 3: Finding User 'Fathima Adheena'</h2>";
    
    $searchUsernames = ['Fathima Adheena', 'FathimaAdheena', 'fathima adheena', 'fathimaadheena'];
    $userFound = null;
    
    foreach($searchUsernames as $searchName) {
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$searchName, $searchName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userFound = $user;
            break;
        }
    }
    
    // If not found by exact match, list all users
    if (!$userFound) {
        echo "<div class='warning'>⚠ User 'Fathima Adheena' not found with exact match. Showing all users:</div>";
        $allUsers = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
        $users = $allUsers->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Current Role</th><th>Action</th></tr>";
            foreach($users as $u) {
                $role = $u['role'] ?? 'NULL';
                $isAdmin = ($role === 'admin');
                $rowStyle = $isAdmin ? "background:#d4edda;" : "";
                echo "<tr style='$rowStyle'>";
                echo "<td>{$u['id']}</td>";
                echo "<td><strong>{$u['username']}</strong></td>";
                echo "<td>{$u['email']}</td>";
                echo "<td>" . ($role ?: 'NULL') . ($isAdmin ? ' ✅' : '') . "</td>";
                if (!$isAdmin) {
                    echo "<td><a href='?make_admin={$u['id']}' style='color:#27ae60;font-weight:bold;'>Make Admin</a></td>";
                } else {
                    echo "<td>Already Admin</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Auto-fix first user if only one exists
            if (count($users) === 1) {
                $userFound = $users[0];
                echo "<div class='info'>Found 1 user. Will make this user admin automatically.</div>";
            }
        } else {
            echo "<div class='error'>✗ No users found in database!</div>";
            echo "<div class='info'>Please create a user account first by going to the signup page.</div>";
            $issues[] = "No users in database";
        }
    }
    
    // Handle make admin from URL
    if (isset($_GET['make_admin'])) {
        $userId = intval($_GET['make_admin']);
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userFound = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($userFound) {
        echo "<div class='success'>✓ Found user: <strong>{$userFound['username']}</strong> (ID: {$userFound['id']})</div>";
        echo "<div class='info'>Email: {$userFound['email']}</div>";
        echo "<div class='info'>Current Role: " . ($userFound['role'] ?? 'NULL') . "</div>";
        
        // Make user admin
        if (($userFound['role'] ?? '') !== 'admin') {
            echo "<div class='warning'>⚠ User is not an admin. Making user admin now...</div>";
            try {
                $updateStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                $updateStmt->execute([$userFound['id']]);
                echo "<div class='success'>✓ <strong>SUCCESS!</strong> User '{$userFound['username']}' is now an admin!</div>";
                $fixes[] = "Made user '{$userFound['username']}' an admin";
                $userFound['role'] = 'admin';
                $allFixed = true;
            } catch(PDOException $e) {
                echo "<div class='error'>✗ Error updating user role: " . $e->getMessage() . "</div>";
                $issues[] = "Could not update user role";
            }
        } else {
            echo "<div class='success'>✓ User is already an admin!</div>";
            $allFixed = true;
        }
    }
    echo "</div>";
    
    // Step 4: Verify fix
    echo "<div class='step'>";
    echo "<h2>Step 4: Verification</h2>";
    if ($userFound) {
        $verifyStmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $verifyStmt->execute([$userFound['id']]);
        $verified = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($verified && $verified['role'] === 'admin') {
            echo "<div class='success'>";
            echo "<h3>✅ VERIFICATION SUCCESSFUL!</h3>";
            echo "<p><strong>User '{$verified['username']}' is confirmed as admin.</strong></p>";
            echo "</div>";
            $allFixed = true;
        } else {
            echo "<div class='error'>✗ Verification failed. Role is: " . ($verified['role'] ?? 'NULL') . "</div>";
            $issues[] = "Verification failed";
        }
    }
    echo "</div>";
    
    // Summary
    echo "<div class='step'>";
    echo "<h2>Summary</h2>";
    if ($allFixed && count($issues) === 0) {
        echo "<div class='success'>";
        echo "<h3>🎉 Everything is Fixed!</h3>";
        echo "<p>You can now login to the admin dashboard:</p>";
        echo "<ol>";
        echo "<li>Go to <a href='admin_login.php' style='color:#f8b739;font-weight:bold;'>Admin Login Page</a></li>";
        if ($userFound) {
            echo "<li>Login with username: <strong>{$userFound['username']}</strong></li>";
        }
        echo "<li>Enter your password</li>";
        echo "<li>You will be redirected to the admin dashboard</li>";
        echo "</ol>";
        echo "</div>";
        echo "<p><a href='admin_login.php' class='btn btn-success'>Go to Admin Login Now</a></p>";
    } else {
        echo "<div class='warning'>";
        echo "<h3>⚠ Some Issues Found</h3>";
        if (count($fixes) > 0) {
            echo "<p><strong>Fixes Applied:</strong></p><ul>";
            foreach($fixes as $fix) {
                echo "<li>✓ $fix</li>";
            }
            echo "</ul>";
        }
        if (count($issues) > 0) {
            echo "<p><strong>Remaining Issues:</strong></p><ul>";
            foreach($issues as $issue) {
                echo "<li>✗ $issue</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Connection Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Please check:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database 'adhee_hardware' exists</li>";
    echo "<li>Database credentials in db_config.php are correct</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='admin_login.php' class='btn'>Admin Login</a> <a href='Index.php' class='btn'>Home Page</a></p>";
echo "</div></body></html>";
?>

