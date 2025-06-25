<!-- admin_header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: ../admin/login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary: #e63946;
      --dark-bg: #1e1e2f;
      --light-bg: #f7f7f7;
      --sidebar-width: 240px;
      --header-height: 60px;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--light-bg);
    }

    .header {
      height: var(--header-height);
      background: var(--primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1rem;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
    }

    .header .title {
      font-size: 1.4rem;
      font-weight: bold;
    }

    .sidebar {
      width: var(--sidebar-width);
      background: var(--dark-bg);
      color: white;
      position: fixed;
      top: var(--header-height);
      bottom: 0;
      left: 0;
      overflow-y: auto;
      transition: transform 0.3s ease-in-out;
    }

    .sidebar.closed {
      transform: translateX(-100%);
    }

    .sidebar a {
      display: block;
      padding: 1rem;
      color: white;
      text-decoration: none;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: var(--primary);
    }

    .main {
      margin-left: var(--sidebar-width);
      padding: 1rem;
      margin-top: var(--header-height);
      transition: margin-left 0.3s ease-in-out;
    }

    .main.full {
      margin-left: 0;
    }

    .toggle-btn {
      background: transparent;
      border: none;
      color: white;
      font-size: 1.4rem;
      cursor: pointer;
    }

    .logout-btn {
      background: white;
      color: var(--primary);
      padding: 5px 12px;
      border-radius: 6px;
      border: none;
      font-weight: bold;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .main {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<header class="header">
  <button class="toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>
  <div class="title">JeevanSetu Admin</div>
  <form action="../admin/logout.php" method="post">
    <button class="logout-btn">Logout</button>
  </form>
</header>

<nav class="sidebar" id="sidebar">
  <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-line"></i> Dashboard
  </a>
  <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : '' ?>">
    <i class="fas fa-users"></i> Manage Users
  </a>
  <a href="verify_donors.php" class="<?= basename($_SERVER['PHP_SELF']) === 'verify_donors.php' ? 'active' : '' ?>">
    <i class="fas fa-check-circle"></i> Verify Donors
  </a>
  <a href="manage_requests.php" class="<?= basename($_SERVER['PHP_SELF']) === 'manage_requests.php' ? 'active' : '' ?>">
    <i class="fas fa-hand-holding-medical"></i> Manage Requests
  </a>
  <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>">
    <i class="fas fa-file-alt"></i> Reports
  </a>
  <a href="site_settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'site_settings.php' ? 'active' : '' ?>">
    <i class="fas fa-cog"></i> Site Settings
  </a>
</nav>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const main = document.querySelector('.main');
  sidebar.classList.toggle('closed');
  main.classList.toggle('full');
}
</script>
