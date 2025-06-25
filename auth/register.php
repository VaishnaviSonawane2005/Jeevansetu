<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize_input($_POST['name']);
    $email    = sanitize_input($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = sanitize_input($_POST['role']); // donor or requester

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $password, $role]);

            $user_id = $pdo->lastInsertId();

            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role']    = $role;
            $_SESSION['email']   = $email;

            $dest = $role === 'donor'
                  ? '../donor/register_donation.php'
                  : '../requester/dashboard.php';
            header("Location: $dest");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Register | JeevanSetu</title>

  <!-- global styles -->
  <link rel="stylesheet" href="../css/style.css">

  <!-- page‑specific styles -->
  <style>
    :root{
      --primary:#e63946;
      --primary-dark:#c92d3b;
      --bg-1:#ff9e7c;
      --bg-2:#ffc55e;
    }

    /* ===== section with medical‑themed animated gradient ===== */
    .register-section{
      width:100%;
      padding:3rem 1rem 4rem;
      background:linear-gradient(-45deg,
                 var(--primary),var(--bg-1),var(--bg-2),var(--primary-dark));
      background-size:400% 400%;
      animation:bgShift 12s ease infinite;
      display:flex;
      justify-content:center;
    }
    @keyframes bgShift{
      0%{background-position:0 50%}
      50%{background-position:100% 50%}
      100%{background-position:0 50%}
    }

    /* ===== card ===== */
    .register-card{
      background:#fff;
      width:100%;
      max-width:480px;
      border-radius:14px;
      box-shadow:0 12px 28px rgba(0,0,0,0.15);
      padding:2.5rem 2rem 2rem;
      animation:fadeSlide .5s ease both;
    }
    @keyframes fadeSlide{from{opacity:0;transform:translateY(20px)}}

    .register-card h1{
      margin:0 0 1.8rem;
      font-size:1.8rem;
      text-align:center;
      color:var(--primary);
    }

    /* ===== form ===== */
    .form-group{margin-bottom:1.2rem}
    .form-group label{
      display:block;
      font-weight:600;
      margin-bottom:.35rem;
    }
    .form-group input,
    .form-group select{
      width:100%;
      padding:.65rem 1rem;
      border:1px solid #ccc;
      border-radius:9px;
      font-size:1rem;
      transition:border-color .25s ease,box-shadow .25s ease;
    }
    .form-group input:focus,
    .form-group select:focus{
      outline:none;
      border-color:var(--primary);
      box-shadow:0 0 0 3px rgba(230,57,70,.3);
    }

    .btn-primary{
      width:100%;
      padding:.8rem 1rem;
      background:var(--primary);
      color:#fff;
      font-weight:700;
      font-size:1.05rem;
      border:none;
      border-radius:9px;
      cursor:pointer;
      transition:background .25s ease,transform .15s ease;
    }
    .btn-primary:hover{
      background:var(--primary-dark);
      transform:translateY(-2px);
    }

    /* ===== alert ===== */
    .alert{
      padding:.9rem 1rem;
      border-radius:9px;
      background:#ffe6e6;
      color:#b00020;
      font-weight:600;
      margin-bottom:1.4rem;
    }

    /* ===== extras ===== */
    .extra-links{
      text-align:center;
      margin-top:1rem;
      font-size:.95rem;
    }
    .extra-links a{
      color:var(--primary);
      text-decoration:none;
    }
    .extra-links a:hover{text-decoration:underline}
  </style>
</head>
<body>

<?php include '../components/header.php'; ?>

<section class="register-section">
  <div class="register-card">
    <h1>Create Your Account</h1>

    <?php if(isset($error)): ?>
      <div class="alert"><?= $error; ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="name">Full&nbsp;Name</label>
        <input type="text" id="name" name="name" required>
      </div>

      <div class="form-group">
        <label for="email">Email&nbsp;Address</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Password (min&nbsp;8&nbsp;chars)</label>
        <input type="password" id="password" name="password" minlength="8" required>
      </div>

      <div class="form-group">
        <label for="role">I&nbsp;want&nbsp;to&nbsp;…</label>
        <select id="role" name="role" required>
          <option value="">Select option</option>
          <option value="donor">Register as a Donor</option>
          <option value="requester">Request Blood / Organ</option>
        </select>
      </div>

      <button type="submit" class="btn-primary">Register</button>
    </form>

    <div class="extra-links">
      <p>Already have an account?
         <a href="login.php">Login here</a>
      </p>
    </div>
  </div>
</section>

<?php include '../components/footer.php'; ?>
<script>
     window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`; // 20px extra buffer
  });
</script>
</body>
</html>
