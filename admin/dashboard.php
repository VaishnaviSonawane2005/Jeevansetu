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

// Get data for charts
$blood_groups = $pdo->query("SELECT blood_group, COUNT(*) as count FROM donors GROUP BY blood_group")->fetchAll();
$request_trends = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at)")->fetchAll();

// Include unified admin header (contains sidebar and topbar)
require_once '../components/admin_header.php';
?>

<link rel="stylesheet" href="../css/admin_dashboard.css">

<main class="main">
  <!-- Dashboard Title -->
  <div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 1rem; color:rgba(1, 2, 65, 0.92);">Dashboard Overview</h1>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-title"><i class="fas fa-users mr-2"></i> Total Users</div>
      <div class="stat-value"><?= number_format($counts['users']) ?></div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i> 12% from last month
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fas fa-hand-holding-water mr-2"></i> Active Donors</div>
      <div class="stat-value"><?= number_format($counts['active_donors']) ?> <span class="text-muted" style="font-size:1rem;">/ <?= number_format($counts['donors']) ?></span></div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i> 8% from last month
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fas fa-clock mr-2"></i> Pending Requests</div>
      <div class="stat-value"><?= number_format($counts['pending_requests']) ?></div>
      <div class="stat-change negative">
        <i class="fas fa-arrow-down"></i> 5% from last week
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fas fa-check-circle mr-2"></i> Completed Requests</div>
      <div class="stat-value"><?= number_format($counts['completed_requests']) ?></div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i> 15% from last week
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="charts-row">
    <div class="chart-container">
      <div class="chart-header">
        <h3><i class="fas fa-chart-line mr-2"></i> Requests Trend (Last 30 Days)</h3>
      </div>
      <div id="requestsChart" style="height: 300px;"></div>
    </div>
    <div class="chart-container">
      <div class="chart-header">
        <h3><i class="fas fa-tint mr-2"></i> Blood Group Distribution</h3>
      </div>
      <div id="bloodGroupChart" style="height: 300px;"></div>
    </div>
  </div>

  <!-- Recent Requests Table -->
  <div class="table-container">
    <div class="table-header">
      <h3><i class="fas fa-list mr-2"></i> Recent Requests</h3>
      <a href="manage_requests.php" class="btn">View All</a>
    </div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Requester</th>
          <th>Type</th>
          <th>Status</th>
          <th>Blood Group</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_requests as $r): ?>
        <tr>
          <td>#<?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['requester_name']) ?></td>
          <td><?= ucfirst($r['type']) ?></td>
          <td><span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= $r['blood_group'] ?></td>
          <td>
            <button class="action-btn view" title="View"><i class="fas fa-eye"></i></button>
            <button class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Donors Table -->
  <div class="table-container">
    <div class="table-header">
      <h3><i class="fas fa-user-friends mr-2"></i> Recent Donors</h3>
      <a href="verify_donors.php" class="btn">View All</a>
    </div>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Blood Group</th>
          <th>City</th>
          <th>Status</th>
          <th>Actions</th>
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
            <span class="status-badge <?= $d['status'] ? 'status-active' : 'status-pending' ?>">
              <?= $d['status'] ? 'Active' : 'Pending' ?>
            </span>
          </td>
          <td>
            <button class="action-btn view" title="View"><i class="fas fa-eye"></i></button>
            <button class="action-btn verify" title="Verify"><i class="fas fa-check"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  // Requests Trend Chart
  var requestTrendChart = new ApexCharts(document.querySelector("#requestsChart"), {
    series: [{
      name: 'Requests',
      data: [<?= implode(', ', array_column($request_trends, 'count')) ?>]
    }],
    chart: { height: 350, type: 'area', toolbar: { show: false } },
    colors: ['#e63946'],
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    xaxis: {
      categories: [<?= "'" . implode("', '", array_column($request_trends, 'date')) . "'" ?>],
      labels: { style: { colors: '#6c757d' } }
    },
    yaxis: { labels: { style: { colors: '#6c757d' } } },
    tooltip: { y: { formatter: val => val + " requests" } },
    grid: { borderColor: '#f1f1f1' }
  });
  requestTrendChart.render();

  // Blood Group Distribution Chart
  var bloodGroupChart = new ApexCharts(document.querySelector("#bloodGroupChart"), {
    series: <?= json_encode(array_column($blood_groups, 'count')) ?>,
    labels: <?= json_encode(array_column($blood_groups, 'blood_group')) ?>,
    chart: { type: 'donut', height: 350 },
    legend: { position: 'bottom' },
    colors: ['#e63946', '#457b9d', '#1d3557', '#a8dadc', '#ff686b', '#ffd166', '#06d6a0', '#118ab2'],
    plotOptions: {
      pie: {
        donut: {
          labels: {
            show: true,
            total: { show: true, label: 'Total Donors', color: '#6c757d' }
          }
        }
      }
    },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: val => val + " donors" } }
  });
  bloodGroupChart.render();
</script>

<?php require_once '../components/footer.php'; ?>
