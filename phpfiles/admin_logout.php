<?php
// Start session
session_start();

// Unset admin session variables
unset($_SESSION['admin_logged_in']);

// Redirect to admin login page
header("Location: admin_login.php");
exit;
?>