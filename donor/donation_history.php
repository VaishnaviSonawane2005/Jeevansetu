<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

/* ---- fetch history with recipient name ---- */
$stmt = $pdo->prepare("
  SELECT d.*, u.name AS recipient_name
  FROM donations d
  LEFT JOIN requests r ON d.request_id = r.id
  LEFT JOIN users u ON r.user_id = u.id
  WHERE d.donor_id = ?
  ORDER BY d.donation_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Donation History | JeevanSetu</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    :root {
      --primary: #e63946;
      --primary-dark: #c92d3b;
      --bg-1: #ff9e7c;
      --bg-2: #ffc55e;
    }

    .history-banner {
      padding: 2.5rem 1rem;
      background: linear-gradient(135deg, var(--primary), var(--bg-1));
      color: #fff;
      text-align: center;
    }

    .history-banner h1 {
      margin: 0;
      font-size: 2rem;
    }

    .history-section {
      padding: 3rem 1rem;
    }

    .history-card {
      background: #fff;
      margin: auto;
      max-width: 900px;
      width: 100%;
      border-radius: 14px;
      box-shadow: 0 12px 28px rgba(0,0,0,.15);
      padding: 2rem 1.5rem;
      overflow: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: .95rem;
    }

    thead {
      background: #f5f5f5;
    }

    th, td {
      padding: .75rem .9rem;
      text-align: left;
    }

    th {
      font-weight: 700;
      color: #333;
    }

    tr:nth-child(even) {
      background: #fafafa;
    }

    .status-badge {
      padding: .3rem .7rem;
      border-radius: 20px;
      color: #fff;
      font-size: .8rem;
      text-transform: capitalize;
      font-weight: 600;
      display: inline-block;
    }

    .status-badge.completed {
      background: #28a745;
    }

    .status-badge.pending {
      background: #ffc107;
      color: #333;
    }

    .status-badge.cancelled {
      background: #dc3545;
    }

    .table-responsive {
      overflow-x: auto;
    }

    .actions {
      text-align: center;
      margin-top: 2rem;
    }

    .actions .btn {
      display: inline-block;
      margin: .35rem .25rem;
      padding: .65rem 1.2rem;
      border-radius: 9px;
      font-weight: 700;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: #fff;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
    }

    .btn-secondary {
      background: #ccc;
    }

    .btn-secondary:hover {
      background: #b5b5b5;
    }

    .alert-info {
      background: #eaf4ff;
      color: #004a99;
      padding: 1rem 1.1rem;
      border-radius: 9px;
      max-width: 500px;
      margin: 1.5rem auto;
      text-align: center;
      font-weight: 600;
    }
  </style>
</head>
<body>

<?php include '../components/header.php'; ?>

<section class="history-banner">
  <h1>Your Donation History</h1>
</section>

<main class="history-section">
  <?php if (!$donations): ?>
    <div class="alert-info">No donation records found.</div>
  <?php else: ?>
    <div class="history-card">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Item</th>
              <th>Details</th>
              <th>Recipient</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($donations as $d): ?>
              <tr>
                <td><?= date('d M Y', strtotime($d['donation_date'])) ?></td>
                <td><?= htmlspecialchars($d['donated_item'] ?? '—') ?></td>
                <td>
                  <?php if (strtolower($d['donated_item']) === 'blood'): ?>
                    <?= htmlspecialchars($d['blood_group'] ?? '') ?> (<?= intval($d['units']) ?> unit<?= intval($d['units']) > 1 ? 's' : '' ?>)
                  <?php else: ?>
                    <?= ucwords(str_replace(',', ', ', $d['organs'] ?? '')) ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($d['recipient_name'] ?? 'Anonymous') ?></td>
                <td>
                  <span class="status-badge <?= strtolower($d['status']) ?>">
                    <?= ucfirst($d['status']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <div class="actions">
    <a href="register_donation.php" class="btn btn-primary">Register New Donation</a>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>
</main>

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
