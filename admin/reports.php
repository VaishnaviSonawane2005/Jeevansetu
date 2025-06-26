<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

// Get report data
$reports = [];

// 1. Blood group distribution
$stmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM donors WHERE blood_group IS NOT NULL GROUP BY blood_group");
$reports['blood_groups'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 2. Donations by month
$stmt = $pdo->query("SELECT DATE_FORMAT(donation_date, '%Y-%m') as month, COUNT(*) as count 
                     FROM donations 
                     WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                     GROUP BY month ORDER BY month");
$reports['donations_by_month'] = $stmt->fetchAll();

// 3. Requests by status
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
$reports['requests_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 4. Top cities
$stmt = $pdo->query("SELECT city, COUNT(*) as count FROM donors WHERE city IS NOT NULL GROUP BY city ORDER BY count DESC LIMIT 10");
$reports['top_cities'] = $stmt->fetchAll();

// 5. Donor availability
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM donors GROUP BY status");
$reports['donor_availability'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports | JeevanSetu Admin</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
    }
    .main {
      margin-left: 260px;
      padding: 2rem;
      margin-top: 70px;
      transition: all 0.3s ease;
    }
    
    h1 { margin-bottom: 1rem; color:rgba(1, 2, 65, 0.92); }

    .main.full {
      margin-left: 0;
    }
    .report-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 1.5rem;
      margin-top: 20px;
    }
    .report-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .report-card h2 {
      font-size: 18px;
      margin-bottom: 15px;
      color: #c0392b;
    }
    .city-list {
      max-height: 240px;
      overflow-y: auto;
    }
    .city-item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px dashed #ddd;
    }
    .btn {
      display: inline-block;
      padding: 8px 16px;
      background: #2980b9;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      margin-right: 10px;
      transition: 0.3s;
    }
    .btn:hover {
      background: #1c5980;
    }
    
    .export-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  padding: 12px 20px;
  margin-bottom: 10px;
  font-weight: bold;
  font-size: 15px;
  color: #fff;
  background-color: #2c3e50; /* Dark blue */
  border: none;
  border-radius: 6px;
  text-decoration: none;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s, transform 0.2s;
}
.export-btn:hover {
  background-color: #1a252f;
  transform: translateY(-2px);
}


  </style>
</head>
<body>
  <?php include '../components/admin_header.php'; ?>

  <main class="main" id="main">
    <h1>System Reports</h1>
    <div class="report-grid">
      <div class="report-card">
        <h2><i class="fas fa-tint"></i> Blood Group Distribution</h2>
        <canvas id="bloodGroupChart"></canvas>
      </div>

      <div class="report-card">
        <h2><i class="fas fa-calendar-alt"></i> Donations Last 12 Months</h2>
        <canvas id="donationsChart"></canvas>
      </div>

      <div class="report-card">
        <h2><i class="fas fa-tasks"></i> Request Status</h2>
        <canvas id="requestsChart"></canvas>
      </div>

      <div class="report-card">
        <h2><i class="fas fa-city"></i> Top Cities</h2>
        <div class="city-list">
          <?php foreach ($reports['top_cities'] as $city): ?>
            <div class="city-item">
              <span><?= htmlspecialchars($city['city']) ?></span>
              <span><?= $city['count'] ?> donors</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="report-card">
        <h2><i class="fas fa-users"></i> Donor Availability</h2>
        <canvas id="availabilityChart"></canvas>
      </div>

      <div class="report-card">
  <h2><i class="fas fa-download"></i> Export Data</h2>
  <a href="export.php?type=donors" class="btn export-btn"><i class="fas fa-user-plus"></i> Donors</a>
  <a href="export.php?type=requests" class="btn export-btn"><i class="fas fa-file-alt"></i> Requests</a>
  <a href="export.php?type=donations" class="btn export-btn"><i class="fas fa-hand-holding-heart"></i> Donations</a>
</div>


    </div>
  </main>

  <script>
    const bloodGroupChart = new Chart(document.getElementById('bloodGroupChart'), {
      type: 'pie',
      data: {
        labels: <?= json_encode(array_keys($reports['blood_groups'])) ?>,
        datasets: [{
          data: <?= json_encode(array_values($reports['blood_groups'])) ?>,
          backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8AC249', '#3A3A3A']
        }]
      }
    });

    const donationsChart = new Chart(document.getElementById('donationsChart'), {
      type: 'line',
      data: {
        labels: <?= json_encode(array_column($reports['donations_by_month'], 'month')) ?>,
        datasets: [{
          label: 'Donations',
          data: <?= json_encode(array_column($reports['donations_by_month'], 'count')) ?>,
          borderColor: '#d43f3a',
          backgroundColor: 'rgba(212, 63, 58, 0.1)',
          fill: true
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });

    const requestsChart = new Chart(document.getElementById('requestsChart'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_keys($reports['requests_by_status'])) ?>,
        datasets: [{
          data: <?= json_encode(array_values($reports['requests_by_status'])) ?>,
          backgroundColor: ['#FFCE56', '#36A2EB', '#4BC0C0', '#FF6384']
        }]
      }
    });

    const availabilityChart = new Chart(document.getElementById('availabilityChart'), {
      type: 'bar',
      data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
          label: 'Donors',
          data: [
            <?= $reports['donor_availability'][1] ?? 0 ?>,
            <?= $reports['donor_availability'][0] ?? 0 ?>
          ],
          backgroundColor: ['#4BC0C0', '#FF6384']
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });
  </script>

  <?php include '../components/footer.php'; ?>
</body>
</html>
