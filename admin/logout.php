<?php
require_once '../includes/functions.php';

session_start();

// Unset all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_logged_in']);

// Destroy the session
session_destroy();

// Redirect to admin login page
header("Location: login.php");
exit();
?>