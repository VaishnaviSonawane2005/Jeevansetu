<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate reset token (valid for 1 hour)
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);
            
            // Send email with reset link (in a real app)
            $reset_link = "http://yourdomain.com/auth/reset_password.php?token=$token";
            // mail($email, "Password Reset", "Click here to reset: $reset_link");
            
            $message = "Password reset link has been sent to your email!";
        } else {
            $error = "Email not found!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | JeevanSetu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main class="container">
        <h1>Forgot Password</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="email">Enter your email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        
        <p>Remember your password? <a href="login.php">Login here</a></p>
    </main>
    
    <?php include '../components/footer.php'; ?>
</body>
</html>