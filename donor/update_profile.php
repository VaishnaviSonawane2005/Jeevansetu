<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

/* --- fetch user --- */
$stmt = $pdo->prepare(
  "SELECT u.name, u.email, d.* 
   FROM users u 
   LEFT JOIN donors d ON u.id = d.user_id 
   WHERE u.id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

/* --- update ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = sanitize_input($_POST['name']);
    $email           = sanitize_input($_POST['email']);
    $age             = sanitize_input($_POST['age']);
    $gender          = sanitize_input($_POST['gender']);
    $medical_history = sanitize_input($_POST['medical_history']);

    try {
        $pdo->prepare(
          "UPDATE users SET name = ?, email = ? WHERE id = ?"
        )->execute([$name, $email, $_SESSION['user_id']]);

        if ($user && isset($user['user_id'])) {
            $pdo->prepare(
              "UPDATE donors SET age = ?, gender = ?, medical_history = ? 
               WHERE user_id = ?"
            )->execute([$age, $gender, $medical_history, $_SESSION['user_id']]);
        } else {
            $pdo->prepare(
              "INSERT INTO donors (user_id, age, gender, medical_history) 
               VALUES (?, ?, ?, ?)"
            )->execute([$_SESSION['user_id'], $age, $gender, $medical_history]);
        }

        $success = "Profile updated successfully!";
        // refresh
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Update Profile | JeevanSetu</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    :root{
      --primary:#e63946;
      --primary-dark:#c92d3b;
      --bg-1:#ff9e7c;
      --bg-2:#ffc55e;
    }

    /* gradient behind this section only */
    .profile-section{
      padding:3rem 1rem 4rem;
      background:linear-gradient(-45deg,
         var(--primary),var(--bg-1),var(--bg-2),var(--primary-dark));
      background-size:400% 400%;
      animation:bgShift 12s ease infinite;
      display:flex;justify-content:center;
    }
    @keyframes bgShift{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}

    .profile-card{
      background:#fff;
      width:100%;max-width:480px;
      border-radius:14px;
      box-shadow:0 12px 28px rgba(0,0,0,.15);
      padding:2.5rem 2rem 2rem;
      animation:fadeSlide .5s ease both;
    }
    @keyframes fadeSlide{from{opacity:0;transform:translateY(20px)}}

    .profile-card h1{
      text-align:center;font-size:1.8rem;margin-bottom:1.8rem;color:var(--primary);
    }

    /* form controls */
    .form-group{margin-bottom:1.2rem}
    .form-group label{display:block;font-weight:600;margin-bottom:.35rem}
    .form-group input,
    .form-group select,
    .form-group textarea{
      width:100%;padding:.65rem 1rem;border:1px solid #ccc;
      border-radius:9px;font-size:1rem;
      transition:border-color .25s ease,box-shadow .25s ease;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus{
      outline:none;border-color:var(--primary);
      box-shadow:0 0 0 3px rgba(230,57,70,.3);
    }
    textarea{resize:vertical}

    .btn-primary,.btn-secondary{
      display:inline-block;margin-top:1rem;padding:.8rem 1rem;
      border-radius:9px;font-weight:700;border:none;cursor:pointer;
      transition:background .25s ease,transform .15s ease;
    }
    .btn-primary{background:var(--primary);color:#fff;width:100%}
    .btn-primary:hover{background:var(--primary-dark);transform:translateY(-2px)}
    .btn-secondary{background:#ccc;margin-left:.5rem}
    .btn-secondary:hover{background:#b5b5b5}

    .alert{padding:.9rem 1rem;border-radius:9px;margin-bottom:1.4rem;font-weight:600}
    .alert-danger{background:#ffe6e6;color:#b00020}
    .alert-success{background:#e6ffe8;color:#006d1b}
  </style>
</head>
<body>
<?php include '../components/header.php'; ?>
<section class="profile-section">
  <div class="profile-card">
    <h1>Update Your Profile</h1>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?= $error; ?></div>
    <?php elseif(isset($success)): ?>
      <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="name">Full&nbsp;Name</label>
        <input id="name" name="name" type="text" required value="<?= $user['name']??'' ?>">
      </div>

      <div class="form-group">
        <label for="email">Email&nbsp;Address</label>
        <input id="email" name="email" type="email" required value="<?= $user['email']??'' ?>">
      </div>

      <div class="form-group">
        <label for="age">Age</label>
        <input id="age" name="age" type="number" min="18" max="65" required value="<?= $user['age']??'' ?>">
      </div>

      <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
          <option value="">Select</option>
          <option value="male"   <?= ($user['gender']??'')==='male'?'selected':'' ?>>Male</option>
          <option value="female" <?= ($user['gender']??'')==='female'?'selected':'' ?>>Female</option>
          <option value="other"  <?= ($user['gender']??'')==='other'?'selected':'' ?>>Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="medical_history">Medical&nbsp;History</label>
        <textarea id="medical_history" name="medical_history" rows="4"><?= $user['medical_history']??'' ?></textarea>
        <p class="help-text" style="font-size:.85rem;color:#666;margin-top:.2rem">
          Mention chronic illnesses, surgeries, etc.
        </p>
      </div>

      <button type="submit" class="btn-primary">Update Profile</button>
      <a href="dashboard.php" class="btn-secondary">Cancel</a>
    </form>
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
