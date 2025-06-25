<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            session_regenerate_id(true); // prevent session fixation

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['email']   = $email;

            $role = strtolower(trim($user['role'])); // case-insensitive check

            // Role-based redirection
            if ($role === 'admin') {
                $_SESSION['admin_logged_in'] = true; // used in admin_check.php
                $dest = '../admin/dashboard.php';
            } elseif ($role === 'donor') {
                $dest = '../donor/dashboard.php';
            } elseif ($role === 'requester') {
                $dest = '../requester/dashboard.php';
            } else {
                $dest = '../unknown-role.php'; // fallback if role is unexpected
            }

            header("Location: $dest");
            exit;
        }

        $error = "Invalid email or password!";
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | JeevanSetu</title>

  <link rel="stylesheet" href="../css/style.css" />
  <style>
    :root {
      --primary: #e63946;
      --primary-dark: #c92d3b;
      --bg-1: #ff9e7c;
      --bg-2: #ffc55e;
    }
    .login-section {
      width: 100%;
      padding: 3rem 1rem 4rem;
      background: linear-gradient(-45deg, var(--primary), var(--bg-1), var(--bg-2), var(--primary-dark));
      background-size: 400% 400%;
      animation: bgShift 12s ease infinite;
      display: flex;
      justify-content: center;
    }
    @keyframes bgShift {
      0% { background-position: 0 50% }
      50% { background-position: 100% 50% }
      100% { background-position: 0 50% }
    }
    .login-card {
      background: #fff;
      width: 100%;
      max-width: 480px;
      border-radius: 14px;
      box-shadow: 0 12px 28px rgba(0,0,0,0.15);
      padding: 2.5rem 2rem 2rem;
      animation: fadeSlide .5s ease both;
    }
    @keyframes fadeSlide {
      from { opacity: 0; transform: translateY(20px); }
    }
    .login-card h1 {
      margin: 0 0 1.8rem;
      font-size: 1.8rem;
      text-align: center;
      color: var(--primary);
    }
    .form-group { margin-bottom: 1.3rem; }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: .35rem;
    }
    .form-group input {
      width: 100%;
      padding: .65rem 1rem;
      border: 1px solid #ccc;
      border-radius: 9px;
      font-size: 1rem;
      transition: border-color .25s ease, box-shadow .25s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(230,57,70,.3);
    }
    .btn-primary {
      width: 100%;
      padding: .8rem 1rem;
      background: var(--primary);
      color: #fff;
      font-weight: 700;
      font-size: 1.05rem;
      border: none;
      border-radius: 9px;
      cursor: pointer;
      transition: background .25s ease, transform .15s ease;
    }
    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }
    .alert {
      padding: .9rem 1rem;
      border-radius: 9px;
      background: #ffe6e6;
      color: #b00020;
      font-weight: 600;
      margin-bottom: 1.4rem;
    }
    .extra-links {
      text-align: center;
      margin-top: 1rem;
      font-size: .95rem;
    }
    .extra-links a {
      color: var(--primary);
      text-decoration: none;
    }
    .extra-links a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<?php include '../components/header.php'; ?>

<section class="login-section">
  <div class="login-card">
    <h1>Login to JeevanSetu</h1>

    <?php if (isset($error)): ?>
      <div class="alert"><?= $error; ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" autocomplete="username" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn-primary">Login</button>
    </form>

    <div class="extra-links">
      <p>Don’t have an account? <a href="register.php">Register here</a></p>
      <p><a href="forgot_password.php">Forgot password?</a></p>
    </div>
  </div>
</section>

<?php include '../components/footer.php'; ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`;
  });
</script>
</body>
</html>
