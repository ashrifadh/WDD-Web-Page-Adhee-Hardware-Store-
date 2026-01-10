<?php
/**
 * Setup Admin Table - Creates separate admin table and migrates existing admin users
 */
require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Admin Table</title>";
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
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Setup Admin Table</h1>";

try {
    // Step 1: Create admin table
    echo "<h2>Step 1: Create Admin Table</h2>";
    
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `admin` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($createTableSQL);
    echo "<div class='success'>✅ Admin table created successfully!</div>";
    
    // Step 2: Check if admin table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'admin'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Admin table exists</div>";
    }
    
    // Step 3: Check existing admin users in users table
    echo "<h2>Step 2: Check Existing Admin Users</h2>";
    
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    
    $adminUsers = [];
    if ($roleColumnExists) {
        $stmt = $conn->query("SELECT id, username, email, password FROM users WHERE role = 'admin'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (count($adminUsers) > 0) {
        echo "<div class='info'>Found " . count($adminUsers) . " admin user(s) in users table:</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Action</th></tr>";
        foreach($adminUsers as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>{$admin['username']}</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td><a href='?migrate={$admin['id']}' class='btn'>Migrate to Admin Table</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (isset($_GET['migrate'])) {
            $userId = intval($_GET['migrate']);
            $stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ? AND role = 'admin'");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Check if admin already exists
                $checkStmt = $conn->prepare("SELECT id FROM admin WHERE username = ? OR email = ?");
                $checkStmt->execute([$user['username'], $user['email']]);
                
                if ($checkStmt->fetch()) {
                    echo "<div class='warning'>⚠️ Admin user '{$user['username']}' already exists in admin table</div>";
                } else {
                    // Insert into admin table
                    $insertStmt = $conn->prepare("INSERT INTO admin (username, email, password) VALUES (?, ?, ?)");
                    $insertStmt->execute([$user['username'], $user['email'], $user['password']]);
                    echo "<div class='success'>✅ Admin user '{$user['username']}' migrated successfully!</div>";
                    echo "<script>setTimeout(function(){ window.location.href='setup_admin_table.php'; }, 2000);</script>";
                }
            }
        }
    } else {
        echo "<div class='info'>No admin users found in users table (or role column doesn't exist)</div>";
    }
    
    // Step 4: Show all admins in admin table
    echo "<h2>Step 3: All Admins in Admin Table</h2>";
    $stmt = $conn->query("SELECT id, username, email, created_at FROM admin ORDER BY id");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created At</th></tr>";
        foreach($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td><strong>{$admin['username']}</strong> 👑</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>{$admin['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No admins in admin table yet. You can create one using the form below.</div>";
    }
    
    // Step 5: Create new admin form
    echo "<h2>Step 4: Create New Admin</h2>";
    
    if (isset($_POST['create_admin'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if ($username && $email && $password) {
            // Check if admin already exists
            $checkStmt = $conn->prepare("SELECT id FROM admin WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            
            if ($checkStmt->fetch()) {
                echo "<div class='error'>❌ Admin with this username or email already exists!</div>";
            } else {
                // Hash password and create admin
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $conn->prepare("INSERT INTO admin (username, email, password) VALUES (?, ?, ?)");
                $insertStmt->execute([$username, $email, $hashedPassword]);
                
                echo "<div class='success'>✅ Admin user '$username' created successfully!</div>";
                echo "<script>setTimeout(function(){ window.location.href='setup_admin_table.php'; }, 2000);</script>";
            }
        }
    }
    
    echo "<form method='POST' style='margin:20px 0;padding:20px;background:#f8f9fa;border-radius:5px;'>";
    echo "<p><strong>Username:</strong><br><input type='text' name='username' required style='padding:8px;width:300px;'></p>";
    echo "<p><strong>Email:</strong><br><input type='email' name='email' required style='padding:8px;width:300px;'></p>";
    echo "<p><strong>Password:</strong><br><input type='password' name='password' required style='padding:8px;width:300px;'></p>";
    echo "<button type='submit' name='create_admin' class='btn'>Create Admin</button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<div class='success'>";
    echo "<strong>✅ Setup Complete!</strong><br>";
    echo "Admin table is ready. Now you can login through admin_login.php using admin credentials from this table.<br>";
    echo "<a href='admin_login.php' style='color:#3498db;font-weight:bold;'>Go to Admin Login Page</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>

