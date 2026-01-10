<?php
session_start();

// Check if user is already logged in as admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Verify admin status in database
    try {
        require_once 'db_config.php';
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // User is confirmed admin, redirect to dashboard immediately
            header('Location: admin_dashboard.php');
            exit();
        }
    } catch(Exception $e) {
        // On any error, redirect to login
    }
}

// If not admin or not logged in, redirect to admin login immediately
header('Location: admin_login.php');
exit();
?>
