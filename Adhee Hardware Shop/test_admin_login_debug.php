<?php
/**
 * Debug script to test admin login
 */
session_start();
require_once 'db_config.php';

$test_username = $_GET['username'] ?? '';
echo "<h2>Admin Login Debug</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;background:#d4edda;padding:10px;margin:5px 0;} .error{color:red;background:#f8d7da;padding:10px;margin:5px 0;} .info{color:blue;background:#d1ecf1;padding:10px;margin:5px 0;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;} th{background:#333;color:white;}</style>";

if ($test_username) {
    echo "<h3>Testing user: $test_username</h3>";
    
    // Check if role column exists
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    echo "<div class='info'>Role column exists: " . ($roleColumnExists ? 'YES' : 'NO') . "</div>";
    
    // Get user
    if ($roleColumnExists) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
    }
    $stmt->execute([trim($test_username), trim($test_username)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<div class='success'>✓ User found!</div>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
        echo "<tr><td>Username</td><td>{$user['username']}</td></tr>";
        if (isset($user['role'])) {
            echo "<tr><td>Role</td><td><strong>" . ($user['role'] ?? 'NULL') . "</strong></td></tr>";
            $userRole = isset($user['role']) && $user['role'] !== null ? strtolower(trim($user['role'])) : '';
            echo "<tr><td>Role (normalized)</td><td><strong>'$userRole'</strong></td></tr>";
            echo "<tr><td>Is Admin?</td><td><strong>" . ($userRole === 'admin' ? 'YES ✓' : 'NO ✗') . "</strong></td></tr>";
        } else {
            echo "<tr><td>Role</td><td><strong>NOT IN QUERY (column doesn't exist)</strong></td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ User not found!</div>";
    }
} else {
    // Show all users
    echo "<h3>All Users in Database</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    $roleColumnExists = count($columns) > 0;
    
    if ($roleColumnExists) {
        $stmt = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
    } else {
        $stmt = $conn->query("SELECT id, username, email FROM users ORDER BY id");
    }
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Role column exists: " . ($roleColumnExists ? 'YES' : 'NO') . "</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th>" . ($roleColumnExists ? "<th>Role</th><th>Is Admin?</th>" : "") . "<th>Test</th></tr>";
    foreach($users as $u) {
        $isAdmin = false;
        if ($roleColumnExists && isset($u['role'])) {
            $userRole = strtolower(trim($u['role'] ?? ''));
            $isAdmin = ($userRole === 'admin');
        }
        $rowStyle = $isAdmin ? "background:#d4edda;" : "";
        echo "<tr style='$rowStyle'>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['username']}</td>";
        echo "<td>{$u['email']}</td>";
        if ($roleColumnExists) {
            echo "<td><strong>" . ($u['role'] ?? 'NULL') . "</strong></td>";
            echo "<td>" . ($isAdmin ? 'YES ✓' : 'NO ✗') . "</td>";
        }
        echo "<td><a href='?username={$u['username']}'>Test</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='admin_login.php'>Go to Admin Login</a> | <a href='make_user_admin.php'>Make User Admin</a></p>";
?>

