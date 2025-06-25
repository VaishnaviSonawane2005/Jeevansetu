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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../components/admin_header.php'; ?>
    
    <main class="container">
        <h1>System Reports</h1>
        
        <div class="report-grid">
            <div class="report-card">
                <h2>Blood Group Distribution</h2>
                <canvas id="bloodGroupChart"></canvas>
            </div>
            
            <div class="report-card">
                <h2>Donations Last 12 Months</h2>
                <canvas id="donationsChart"></canvas>
            </div>
            
            <div class="report-card">
                <h2>Request Status</h2>
                <canvas id="requestsChart"></canvas>
            </div>
            
            <div class="report-card">
                <h2>Top Cities (Donors)</h2>
                <div class="city-list">
                    <?php foreach ($reports['top_cities'] as $city): ?>
                        <div class="city-item">
                            <span class="city-name"><?= htmlspecialchars($city['city']) ?></span>
                            <span class="city-count"><?= $city['count'] ?> donors</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="report-card">
                <h2>Donor Availability</h2>
                <canvas id="availabilityChart"></canvas>
            </div>
            
            <div class="report-card">
                <h2>Export Data</h2>
                <div class="export-options">
                    <a href="export.php?type=donors" class="btn">Export Donors (CSV)</a>
                    <a href="export.php?type=requests" class="btn">Export Requests (CSV)</a>
                    <a href="export.php?type=donations" class="btn">Export Donations (CSV)</a>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Blood Group Chart
        const bloodGroupCtx = document.getElementById('bloodGroupChart').getContext('2d');
        const bloodGroupChart = new Chart(bloodGroupCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_keys($reports['blood_groups'])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($reports['blood_groups'])) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#8AC249', '#3A3A3A'
                    ]
                }]
            }
        });
        
        // Donations Chart
        const donationsCtx = document.getElementById('donationsChart').getContext('2d');
        const donationsChart = new Chart(donationsCtx, {
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
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Requests Chart
        const requestsCtx = document.getElementById('requestsChart').getContext('2d');
        const requestsChart = new Chart(requestsCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($reports['requests_by_status'])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($reports['requests_by_status'])) ?>,
                    backgroundColor: [
                        '#FFCE56', '#36A2EB', '#4BC0C0', '#FF6384'
                    ]
                }]
            }
        });
        
        // Availability Chart
        const availabilityCtx = document.getElementById('availabilityChart').getContext('2d');
        const availabilityChart = new Chart(availabilityCtx, {
            type: 'bar',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    label: 'Donors',
                    data: [
                        <?= $reports['donor_availability'][1] ?? 0 ?>,
                        <?= $reports['donor_availability'][0] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#4BC0C0', '#FF6384'
                    ]
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    
    <?php include '../components/footer.php'; ?>
</body>
</html>