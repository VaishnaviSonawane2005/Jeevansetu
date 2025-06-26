<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

$just_verified_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_id'])) {
        $verify_id = (int) $_POST['verify_id'];
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $stmt->execute([$verify_id]);
        $just_verified_id = $verify_id;
        $toast = "User ID $verify_id verified successfully.";
    } elseif (isset($_POST['verify_all'])) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE is_verified = 0");
        $stmt->execute();
        $toast = "All users verified successfully.";
    }
}

$stmt = $pdo->prepare("SELECT u.id AS user_id, u.name, u.email, u.role, u.is_verified,
           d.gender, d.age, d.blood_group, d.city, d.contact_number
    FROM users u
    LEFT JOIN donors d ON u.id = d.user_id
    WHERE u.is_verified = 0
    ORDER BY u.created_at DESC");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Users | Admin</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-yJ3XrAknzQkE7PPe3RBNw4UfY2LZzqYJ20W2sx9bH3kCtbDfUIxFFdX+G7u2bJMSRKZsKfEv5X0gAhzImWZbZw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f6fa;
      margin: 0;
      padding: 0;
    }

    .wrapper {
      display: flex;
    }

    .main {
      margin-left: 260px;
      padding: 2rem;
      margin-top: 70px;
      width: 100%;
      transition: all 0.3s ease;
      min-height: calc(100vh - 70px);
      overflow-x: auto;
    }

    .main.full {
      margin-left: 0;
    }

    @media (max-width: 992px) {
      .main {
        margin-left: 0;
      }
    }

    .container {
      background: #fff;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      overflow-x: auto;
    }

    h1 { margin-bottom: 1rem; color:rgba(1, 2, 65, 0.92); }


    .btn-verify, .btn-verified {
      padding: 6px 12px;
      border-radius: 5px;
      font-size: 13px;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .btn-verify {
      background-color: #27ae60;
      color: white;
    }

    .btn-verify:hover {
      background-color: #1e8449;
    }

    .btn-verified {
      background-color: #6c757d;
      color: #fff;
      cursor: default;
      opacity: 0.85;
    }

    .no-records {
      text-align: center;
      color: #777;
      padding: 40px 0;
      font-size: 18px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 15px;
      min-width: 900px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #f1f1f1;
      color: #333;
    }

    .top-bar {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-bottom: 15px;
    }

    .toast {
      position: fixed;
      top: 90px;
      right: 30px;
      background: #27ae60;
      color: #fff;
      padding: 12px 20px;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
      z-index: 9999;
      animation: fadeInOut 4s forwards;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-10px); }
      10% { opacity: 1; transform: translateY(0); }
      90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-10px); }
    }
  </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="wrapper">
  <main class="main" id="main">
    <div class="container">
      <h1>Pending User Verifications</h1>

      <?php if (!empty($pending_users)): ?>
        <div class="top-bar">
          <form method="post" onsubmit="return confirm('Verify all users?');">
            <input type="hidden" name="verify_all" value="1">
            <button type="submit" class="btn-verify"><i class="fas fa-check-double"></i> Verify All</button>
          </form>
        </div>
      <?php endif; ?>

      <?php if (!empty($toast)): ?>
        <div class="toast"><?= $toast ?></div>
      <?php endif; ?>

      <?php if (empty($pending_users)): ?>
        <div class="no-records">✅ All users are verified.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Role</th>
              <th>Name</th>
              <th>Email</th>
              <th>Gender</th>
              <th>Age</th>
              <th>Blood Group</th>
              <th>City</th>
              <th>Contact</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pending_users as $user): ?>
              <tr>
                <td><?= $user['user_id'] ?></td>
                <td><?= ucfirst($user['role']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['gender'] ?? '—' ?></td>
                <td><?= $user['age'] ?? '—' ?></td>
                <td><?= $user['blood_group'] ?? '—' ?></td>
                <td><?= htmlspecialchars($user['city'] ?? '—') ?></td>
                <td><?= htmlspecialchars($user['contact_number'] ?? '—') ?></td>
                <td>
                  <?php if ($just_verified_id == $user['user_id']): ?>
                    <button class="btn-verified" disabled><i class="fas fa-check-circle"></i> Verified</button>
                  <?php else: ?>
                    <form method="post" onsubmit="return confirm('Verify this user?');">
                      <input type="hidden" name="verify_id" value="<?= $user['user_id'] ?>">
                      <button type="submit" class="btn-verify"><i class="fas fa-user-check"></i> Verify</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php include '../components/footer.php'; ?>

<script>
  const sidebarToggle = document.getElementById('sidebarToggle');
  const mainContent = document.getElementById('main');
  const sidebar = document.querySelector('.sidebar');

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('full');
    });
  }
</script>

</body>
</html>