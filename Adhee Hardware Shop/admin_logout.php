<?php
session_start();
// Clear only admin session variables
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_login']);
// Keep customer session if exists (they're separate)
header('Location: admin_login.php');
exit();
?>