<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// ✅ Auto-update approved donations if required_date has passed
$today = date('Y-m-d');

$updateStmt = $pdo->prepare("UPDATE donations
    SET status = 'completed'
    WHERE status = 'pending'
      AND donation_date < ?
      AND donor_id = ?");
$updateStmt->execute([$today, $_SESSION['user_id']]);


// Handle approval submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request_id'])) {
    $requestId = intval($_POST['approve_request_id']);
    $donorId = $_SESSION['user_id'];

    // Check if already approved
    $check = $pdo->prepare("SELECT id FROM donations WHERE donor_id = ? AND request_id = ?");
    $check->execute([$donorId, $requestId]);
    $exists = $check->fetch();

    if (!$exists) {
        // Fetch request details
        $stmt = $pdo->prepare("SELECT required_date, blood_group, organs FROM requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();

        if ($request) {
            $expectedDate = $request['required_date'];
            $donatedItem = $request['blood_group'] ? 'Blood' : ($request['organs'] ?? '');
            $units = $donatedItem === 'Blood' ? 1 : null;

            $insert = $pdo->prepare("INSERT INTO donations (donor_id, request_id, donation_date, status, donated_item, units) VALUES (?, ?, ?, 'pending', ?, ?)");
            $insert->execute([$donorId, $requestId, $expectedDate, $donatedItem, $units]);
        }
    }

    header("Location: my_notifications.php");
    exit();
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT n.*, u.name AS sender_name, r.blood_group, r.hospital_city, r.hospital_name, r.contact_number, r.required_date, r.organs, r.id AS request_id
                      FROM donor_notifications n
                      JOIN users u ON n.sender_user_id = u.id
                      JOIN requests r ON r.id = n.request_id
                      WHERE donor_user_id = ?
                      ORDER BY n.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Notifications | JeevanSetu</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #fff5f5, #ffecec);
      padding: 2rem;
    }
    h1 {
      text-align: center;
      color: #e63946;
      margin-bottom: 2rem;
      font-size: 2.2rem;
    }
    .notification-card {
      background: #ffffff;
      border-radius: 14px;
      padding: 1.5rem 1.75rem;
      margin-bottom: 2rem;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
      transition: transform 0.3s ease;
    }
    .notification-card:hover {
      transform: scale(1.02);
    }
    .notification-card p {
      margin: 0.5rem 0;
      font-size: 1rem;
    }
    .notification-card small {
      color: #777;
      display: block;
      margin-top: 0.6rem;
    }
    .approve-form {
      margin-top: 1.2rem;
      text-align: right;
    }
    .approve-form button {
      background-color: #28a745;
      color: #fff;
      padding: 0.55rem 1.3rem;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.95rem;
    }
    .approve-form button:hover {
      background-color: #218838;
    }
    .approved-label {
      color: green;
      font-weight: bold;
      text-align: right;
      margin-top: 1rem;
    }
  </style>
</head>
<body>

<?php include '../components/header.php'; ?>

<h1>🔔 My Notifications</h1>

<?php if (empty($notifications)): ?>
  <p style="text-align:center; font-size: 1.1rem; color: #666;">You have no notifications at the moment.</p>
<?php else: ?>
  <?php foreach ($notifications as $note): ?>
    <div class="notification-card">
      <p><strong>Message:</strong> <?= htmlspecialchars($note['message']) ?></p>
      <p><strong>From:</strong> <?= htmlspecialchars($note['sender_name']) ?></p>
      <p><strong>Required Donation:</strong>
        <?= $note['blood_group'] ? 'Blood Group: ' . htmlspecialchars($note['blood_group']) : ucwords(htmlspecialchars($note['organs'])) ?>
      </p>
      <p><strong>Hospital:</strong> <?= htmlspecialchars($note['hospital_name']) ?> (<?= htmlspecialchars($note['hospital_city']) ?>)</p>
      <p><strong>Required Date:</strong> <?= date('d M Y', strtotime($note['required_date'])) ?></p>
      <p><strong>Contact:</strong> <?= htmlspecialchars($note['contact_number']) ?></p>
      <small>Notified on: <?= date('d M Y H:i', strtotime($note['created_at'])) ?></small>

      <?php
        $check = $pdo->prepare("SELECT status FROM donations WHERE donor_id = ? AND request_id = ?");
        $check->execute([$_SESSION['user_id'], $note['request_id']]);
        $alreadyApproved = $check->fetch();
      ?>

      <?php if ($alreadyApproved): ?>
        <div class="approved-label">✅ You have approved to donate</div>
      <?php else: ?>
        <form method="post" class="approve-form">
          <input type="hidden" name="approve_request_id" value="<?= $note['request_id'] ?>">
          <button type="submit">✅ Approve to Donate</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php include '../components/footer.php'; ?>
</body>
</html>
