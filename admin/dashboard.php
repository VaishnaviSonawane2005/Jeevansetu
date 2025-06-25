<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

// Summary stats
$counts = [];
$queries = [
    'users' => "SELECT COUNT(*) FROM users",
    'donors' => "SELECT COUNT(*) FROM donors",
    'active_donors' => "SELECT COUNT(*) FROM donors WHERE status = 1",
    'pending_requests' => "SELECT COUNT(*) FROM requests WHERE status = 'pending'",
    'completed_requests' => "SELECT COUNT(*) FROM requests WHERE status = 'completed'",
    'cities' => "SELECT COUNT(DISTINCT city) FROM donors WHERE city IS NOT NULL"
];
foreach ($queries as $key => $query) {
    $counts[$key] = $pdo->query($query)->fetchColumn();
}

// Get recent 5 donors
$recent_donors = $pdo->query("
    SELECT d.*, u.name, u.email 
    FROM donors d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY d.created_at DESC LIMIT 5
")->fetchAll();

// Get recent 5 requests
$recent_requests = $pdo->query("
    SELECT r.*, u.name as requester_name 
    FROM requests r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | JeevanSetu</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --brand: #e63946;
      --light: #f1f1f1;
      --dark: #333;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background: #fafafa;
    }

    header {
      background: var(--brand);
      color: white;
      padding: 1rem 2rem;
    }

    nav {
      background: #444;
      padding: 0.8rem 2rem;
    }

    nav a {
      color: white;
      margin-right: 1.2rem;
      text-decoration: none;
    }

    .container {
      padding: 2rem;
      max-width: 1200px;
      margin: auto;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.2rem;
    }

    .card {
      background: white;
      border-left: 6px solid var(--brand);
      padding: 1.2rem;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }

    .card h3 {
      margin: 0 0 0.4rem;
      font-size: 1.4rem;
    }

    .card p {
      font-size: 1.1rem;
      color: #666;
    }

    .dashboard-charts {
      margin-top: 2rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
    }

    canvas {
      background: white;
      border-radius: 8px;
      padding: 1rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2rem;
      background: white;
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 0.8rem 1rem;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background: #f8f8f8;
      font-weight: 600;
    }

    .status-badge {
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: bold;
      color: white;
    }

    .pending {
      background: #f39c12;
    }

    .completed {
      background: #27ae60;
    }

    .btn {
      display: inline-block;
      background: var(--brand);
      color: white;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .btn:hover {
      background: #c92d3b;
    }
  </style>
</head>
<body>

<header>
  <h1>👨‍⚕️ JeevanSetu Admin Dashboard</h1>
</header>

<nav>
  <a href="dashboard.php">Dashboard</a>
  <a href="manage_users.php">Manage Users</a>
  <a href="verify_donors.php">Verify Donors</a>
  <a href="manage_requests.php">Manage Requests</a>
  <a href="reports.php">Reports</a>
  <a href="site_settings.php">Settings</a>
  <a href="logout.php" style="float: right;">Logout</a>
</nav>

<main class="container">

  <!-- Stat Cards -->
  <div class="cards">
    <div class="card">
      <h3>Total Users</h3>
      <p><?= $counts['users'] ?></p>
    </div>
    <div class="card">
      <h3>Active Donors</h3>
      <p><?= $counts['active_donors'] ?> / <?= $counts['donors'] ?></p>
    </div>
    <div class="card">
      <h3>Pending Requests</h3>
      <p><?= $counts['pending_requests'] ?></p>
    </div>
    <div class="card">
      <h3>Completed Requests</h3>
      <p><?= $counts['completed_requests'] ?></p>
    </div>
    <div class="card">
      <h3>Areas Covered</h3>
      <p><?= $counts['cities'] ?> Cities</p>
    </div>
  </div>

  <!-- Charts -->
  <div class="dashboard-charts">
    <canvas id="requestChart"></canvas>
    <canvas id="donorChart"></canvas>
  </div>

  <!-- Recent Requests Table -->
  <h2 style="margin-top:3rem;">📌 Recent Requests</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Requester</th>
        <th>Type</th>
        <th>Status</th>
        <th>Blood Group</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recent_requests as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['requester_name']) ?></td>
        <td><?= ucfirst($r['type']) ?></td>
        <td><span class="status-badge <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
        <td><?= $r['blood_group'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Recent Donors Table -->
  <h2 style="margin-top:3rem;">🩸 Recent Donors</h2>
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Blood Group</th>
        <th>City</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recent_donors as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d['name']) ?></td>
        <td><?= htmlspecialchars($d['email']) ?></td>
        <td><?= $d['blood_group'] ?></td>
        <td><?= $d['city'] ?></td>
        <td>
          <span class="status-badge <?= $d['status'] ? 'completed' : 'pending' ?>">
            <?= $d['status'] ? 'Active' : 'Pending' ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<script>
const ctx1 = document.getElementById('requestChart');
const requestChart = new Chart(ctx1, {
  type: 'doughnut',
  data: {
    labels: ['Pending', 'Completed'],
    datasets: [{
      data: [<?= $counts['pending_requests'] ?>, <?= $counts['completed_requests'] ?>],
      backgroundColor: ['#f39c12', '#27ae60'],
    }]
  },
  options: {
    plugins: { legend: { position: 'bottom' } },
    responsive: true
  }
});

const ctx2 = document.getElementById('donorChart');
const donorChart = new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: ['Active Donors', 'Total Donors'],
    datasets: [{
      label: 'Donors',
      data: [<?= $counts['active_donors'] ?>, <?= $counts['donors'] ?>],
      backgroundColor: ['#3498db', '#95a5a6']
    }]
  },
  options: {
    scales: {
      y: { beginAtZero: true }
    },
    responsive: true
  }
});
</script>

</body>
</html>
