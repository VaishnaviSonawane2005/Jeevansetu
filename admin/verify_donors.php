<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

$just_verified_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_id'])) {
    $verify_id = (int) $_POST['verify_id'];

    // Set user as verified
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$verify_id]);
    $just_verified_id = $verify_id;
}

// Fetch unverified users (donors + requesters)
$stmt = $pdo->prepare("
    SELECT u.id AS user_id, u.name, u.email, u.role, u.is_verified,
           d.gender, d.age, d.blood_group, d.city, d.contact_number
    FROM users u
    LEFT JOIN donors d ON u.id = d.user_id
    WHERE u.is_verified = 0
    ORDER BY u.created_at DESC
");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Users | Admin</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f6fa;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      background: #fff;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    h1 {
      text-align: center;
      color: #c0392b;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 15px;
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

    .btn-verify {
      background-color: #27ae60;
      color: white;
      padding: 7px 14px;
      border-radius: 5px;
      font-size: 13px;
      border: none;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-verify:hover {
      background-color: #1e8449;
    }

    .btn-verified {
      background-color: #6c757d;
      color: #fff;
      padding: 7px 14px;
      border-radius: 5px;
      font-size: 13px;
      border: none;
      cursor: default;
      opacity: 0.85;
    }

    .no-records {
      text-align: center;
      color: #777;
      padding: 40px 0;
      font-size: 18px;
    }
  </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="container">
  <h1>Pending User Verifications</h1>

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
                <button class="btn-verified" disabled>✔ Verified</button>
              <?php else: ?>
                <form method="post" onsubmit="return confirm('Verify this user?');">
                  <input type="hidden" name="verify_id" value="<?= $user['user_id'] ?>">
                  <button type="submit" class="btn-verify">✔ Verify</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>

</body>
</html>
