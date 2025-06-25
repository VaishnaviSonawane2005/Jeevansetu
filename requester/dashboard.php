<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// Recent 3 requests
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$_SESSION['user_id']]);
$recent_requests = $stmt->fetchAll();

// Count by status
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM requests WHERE user_id = ? GROUP BY status");
$stmt->execute([$_SESSION['user_id']]);
$request_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Requester Dashboard | JeevanSetu</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    :root {
      --primary: #2196f3;
      --primary-dark: #1976d2;
      --accent: #e91e63;
      --bg1: #bbdefb;
      --bg2: #e3f2fd;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background: #f8f9fa;
    }

    .dashboard-section {
      background: linear-gradient(-45deg, var(--primary), var(--bg1), var(--accent), var(--primary-dark));
      background-size: 400% 400%;
      animation: dashBg 12s ease infinite;
      padding: 3rem 1rem;
      color: #fff;
      text-align: center;
    }

    @keyframes dashBg {
      0% {background-position: 0 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0 50%;}
    }

    .dashboard-wrapper {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 2rem;
      border-radius: 14px;
      box-shadow: 0 12px 28px rgba(0,0,0,.12);
      color: #333;
    }

    .dashboard-wrapper h1 {
      margin-bottom: 1.5rem;
      color: var(--primary-dark);
      text-align: center;
    }

    .dashboard-stats {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      flex: 1 1 30%;
      background: #f1f8ff;
      padding: 1.2rem;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .stat-card h3 {
      margin-bottom: 0.5rem;
      font-size: 1rem;
      color: #333;
    }

    .stat-card p {
      font-size: 1.8rem;
      font-weight: bold;
      color: var(--primary);
    }

    .quick-actions {
      text-align: center;
      margin: 2rem 0 2.5rem;
    }

    .btn {
      display: inline-block;
      margin: 0.4rem;
      padding: 0.75rem 1.4rem;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.3s;
    }

    .btn:hover {
      background: var(--primary-dark);
    }

    .btn-secondary {
      background: #e0e0e0;
      color: #333;
    }

    .recent-requests h2 {
      font-size: 1.4rem;
      margin-bottom: 1rem;
      color: var(--primary-dark);
    }

    .request-list {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
    }

    .request-card {
      border: 1px solid #ddd;
      border-left: 5px solid var(--primary);
      padding: 1rem 1.2rem;
      border-radius: 10px;
      background: #fafafa;
    }

    .request-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.6rem;
    }

    .status-badge {
      padding: 0.3rem 0.7rem;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: capitalize;
      color: #fff;
    }

    .status-badge.pending { background: #fbc02d; color: #000; }
    .status-badge.matched { background: #1976d2; }
    .status-badge.completed { background: #43a047; }

    .request-details p {
      margin: 0.3rem 0;
      font-size: 0.95rem;
    }

    .request-actions {
      margin-top: 0.8rem;
    }

    .btn-small {
      padding: 0.4rem 0.9rem;
      font-size: 0.85rem;
      border-radius: 6px;
    }

    .view-all {
      text-align: right;
      margin-top: 1.5rem;
    }

    .alert-info {
      background: #e3f2fd;
      padding: 1rem;
      border-radius: 9px;
      color: #1565c0;
      font-weight: 600;
      text-align: center;
    }

    @media (max-width: 768px) {
      .dashboard-stats {
        flex-direction: column;
      }
      .stat-card {
        flex: 1 1 100%;
      }
    }
  </style>
</head>
<body>

<?php include '../components/header.php'; ?>

<section class="dashboard-section">
  <div class="dashboard-wrapper">
    <h1>Request Blood/Organ Help</h1>

    <div class="dashboard-stats">
      <div class="stat-card">
        <h3>Pending Requests</h3>
        <p><?= $request_counts['pending'] ?? 0 ?></p>
      </div>
      <div class="stat-card">
        <h3>Matched Requests</h3>
        <p><?= $request_counts['matched'] ?? 0 ?></p>
      </div>
      <div class="stat-card">
        <h3>Completed</h3>
        <p><?= $request_counts['completed'] ?? 0 ?></p>
      </div>
    </div>

    <div class="quick-actions">
      <a href="request_form.php" class="btn">Create New Request</a>
      <a href="search_donors.php" class="btn btn-secondary">Search Donors</a>
    </div>

    <section class="recent-requests">
      <h2>Your Recent Requests</h2>
      <?php if (empty($recent_requests)): ?>
        <div class="alert-info">No recent requests found.</div>
      <?php else: ?>
        <div class="request-list">
          <?php foreach ($recent_requests as $request): ?>
            <div class="request-card">
              <div class="request-header">
                <h3><?= ucfirst($request['type']) ?> Request</h3>
                <span class="status-badge <?= $request['status'] ?>">
                  <?= ucfirst($request['status']) ?>
                </span>
              </div>
              <div class="request-details">
                <p><strong>For:</strong> <?= $request['patient_name'] ?></p>
                <p><strong>Blood Group:</strong> <?= $request['blood_group'] ?></p>
                <p><strong>Location:</strong> <?= $request['hospital_city'] ?></p>
                <p><strong>Date:</strong> <?= date('d M Y', strtotime($request['required_date'])) ?></p>
              </div>
              <div class="request-actions">
                <a href="request_status.php?id=<?= $request['id'] ?>" class="btn btn-small">View Details</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="view-all">
          <a href="request_status.php" class="btn btn-secondary">View All</a>
        </div>
      <?php endif; ?>
    </section>
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
