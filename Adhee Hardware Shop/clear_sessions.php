<?php
/**
 * Session Cleanup Script
 * This script will clear all existing sessions to fix auto-login issues
 * Run this once to clear problematic sessions
 */

// Start session
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any remember cookies
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page with message
header('Location: Index.php?sessions_cleared=true');
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Clearing Sessions...</title>
</head>
<body>
    <p>Clearing all sessions and redirecting to home page...</p>
</body>
</html>
