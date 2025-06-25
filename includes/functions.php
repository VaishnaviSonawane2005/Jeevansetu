<?php
// Utility function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data); // Optional: remove backslashes
    $data = htmlspecialchars($data); // Encode HTML chars
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect if user not logged in
function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Redirect user based on role
function redirect_based_on_role() {
    if (isset($_SESSION['role'])) {
        $role = strtolower($_SESSION['role']); // normalize case
        switch ($role) {
            case 'admin':
                header("Location: ../admin/dashboard.php");
                break;
            case 'donor':
                header("Location: ../donor/dashboard.php");
                break;
            default:
                header("Location: ../requester/dashboard.php");
        }
        exit();
    }
}


?>
