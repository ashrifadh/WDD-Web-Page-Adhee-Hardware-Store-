<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check remember me token if session expired
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once 'db_config.php'; // You should create this file with your DB config
    
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="mk.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p>You have successfully logged in!</p>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="logout.php" class="login-button" style="display: inline-block; width: auto; padding: 10px 20px;">Logout</a>
        </div>
    </div>
</body>
</html>