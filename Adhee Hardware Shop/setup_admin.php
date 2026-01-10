<?php
require_once 'db_config.php';

echo "<h2>Admin Setup</h2>";

try {
    // Check if users table exists
    $result = $conn->query('SHOW TABLES LIKE "users"');
    if ($result->rowCount() == 0) {
        echo "<p>Creating users table...</p>";
        $conn->exec('CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT "admin",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        echo "<p style='color: green;'>✅ Users table created successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Users table already exists.</p>";
    }
    
    // Check if there are any users
    $result = $conn->query('SELECT COUNT(*) as count FROM users');
    $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        echo "<p>Creating test admin user...</p>";
        $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['admin', 'admin@adhee.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
        echo "<p style='color: green;'>✅ Test admin user created!</p>";
        echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Found $count existing users:</p>";
        $users = $conn->query('SELECT username, email FROM users LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>" . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h3>Access Links:</h3>";
    echo "<p><a href='admin_login.php' style='color: #2563eb; text-decoration: none; font-weight: bold;'>🔐 Admin Login Page</a></p>";
    echo "<p><a href='admin_dashboard.php' style='color: #2563eb; text-decoration: none; font-weight: bold;'>📊 Admin Dashboard</a></p>";
    echo "<p><a href='Index.php' style='color: #2563eb; text-decoration: none; font-weight: bold;'>🏠 Home Page</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>