<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store username for notification (if available)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Set logout notification before destroying session (if notification system uses session)
// Note: We'll use URL parameter instead since we're destroying the session

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear remember token cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page with logout message
header('Location: Index.php?logout=success');
exit();
?>

