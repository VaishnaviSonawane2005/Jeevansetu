<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    strtolower(trim($_SESSION['role'])) !== 'admin'
) {
    header("Location: login.php");
    exit();
}
