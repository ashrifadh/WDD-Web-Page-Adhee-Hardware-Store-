<?php
/**
 * Test script to check login redirects
 */
session_start();
echo "<h2>Current Session Status:</h2>";
echo "<pre>";
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
echo "Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET') . "\n";
echo "Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "\n";
echo "Admin Login Flag: " . (isset($_SESSION['admin_login']) ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h2>Test Links:</h2>";
echo "<p><a href='login.php'>Regular Customer Login</a></p>";
echo "<p><a href='admin_login.php'>Admin Login</a></p>";
echo "<p><a href='Index.php'>Home Page</a></p>";
echo "<p><a href='admin_dashboard.php'>Admin Dashboard (should redirect if not admin login)</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
?>

