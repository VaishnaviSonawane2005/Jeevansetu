<?php
require_once 'functions.php';

session_start();

if (!is_logged_in()) {
    header("Location: ../auth/login.php");
    exit();
}

// Additional role-based checks can be added here if needed
?>