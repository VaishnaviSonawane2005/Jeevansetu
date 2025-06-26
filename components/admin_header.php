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
  <title>Admin Panel | JeevanSetu</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #e63946;
      --primary-light: #ff6b7d;
      --primary-dark: #c1121f;
      --secondary: #2b2d42;
      --secondary-light: #40445a;
      --light: #f8f9fa;
      --light-gray: #e9ecef;
      --dark: #212529;
      --success: #28a745;
      --warning: #ffc107;
      --danger: #dc3545;
      --info: #17a2b8;
      --sidebar-width: 260px;
      --header-height: 70px;
      --transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f5f7fa;
      color: var(--dark);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Header Styles */
    .header {
      height: var(--header-height);
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 2rem;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .header .title {
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    .header .title i {
      font-size: 1.8rem;
    }

    /* Sidebar Styles */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--secondary);
      color: white;
      position: fixed;
      top: var(--header-height);
      bottom: 0;
      left: 0;
      overflow-y: auto;
      transition: var(--transition);
      z-index: 999;
      box-shadow: 2px 0 15px rgba(0,0,0,0.1);
    }

    .sidebar.closed {
      transform: translateX(-100%);
    }

    .sidebar-menu {
      padding: 1rem 0;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      padding: 1rem 2rem;
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      transition: var(--transition);
      font-size: 0.95rem;
      font-weight: 500;
      position: relative;
    }

    .sidebar-menu a i {
      margin-right: 1rem;
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    .sidebar-menu a:hover {
      background: var(--secondary-light);
      color: white;
      padding-left: 2.2rem;
    }

    .sidebar-menu a.active {
      background: linear-gradient(90deg, var(--primary), rgba(230, 57, 70, 0.2));
      color: white;
      border-left: 4px solid white;
    }

    .sidebar-menu a.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 4px;
      background: white;
    }

    /* Main Content */
    .main {
      margin-left: var(--sidebar-width);
      padding: 2rem;
      margin-top: var(--header-height);
      transition: var(--transition);
      min-height: calc(100vh - var(--header-height));
    }

    .main.full {
      margin-left: 0;
    }

    /* Header Buttons */
    .header-actions {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .toggle-btn {
      background: transparent;
      border: none;
      color: white;
      font-size: 1.4rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      transition: var(--transition);
    }

    .toggle-btn:hover {
      background: rgba(255,255,255,0.1);
    }

    .logout-btn {
      background: white;
      color: var(--primary);
      padding: 0.5rem 1.2rem;
      border-radius: 6px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* User Profile */
    .user-profile {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
    }

    .user-name {
      font-weight: 500;
      font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.closed {
        transform: translateX(-100%);
      }
      
      .sidebar.open {
        transform: translateX(0);
      }
      
      .main {
        margin-left: 0;
      }
    }

    @media (max-width: 576px) {
      .header {
        padding: 0 1rem;
      }
      
      .header .title {
        font-size: 1.2rem;
      }
      
      .user-name {
        display: none;
      }
    }
  </style>
</head>
<body>

<header class="header">
  <div class="header-left">
    <button class="toggle-btn" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    <div class="title">
      <i class="fas fa-heartbeat"></i>
      <span>JeevanSetu Admin</span>
    </div>
  </div>
  
  <div class="header-actions">
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin') ?>&background=random" alt="Admin" class="user-avatar">
      <span class="user-name"><?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
    </div>
    <form action="../admin/logout.php" method="post">
      <button type="submit" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</header>

<nav class="sidebar" id="sidebar">
  <div class="sidebar-menu">
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : '' ?>">
      <i class="fas fa-users-cog"></i> Manage Users
    </a>
    <a href="verify_donors.php" class="<?= basename($_SERVER['PHP_SELF']) === 'verify_donors.php' ? 'active' : '' ?>">
      <i class="fas fa-user-check"></i> Verify Donors
    </a>
    <a href="manage_requests.php" class="<?= basename($_SERVER['PHP_SELF']) === 'manage_requests.php' ? 'active' : '' ?>">
      <i class="fas fa-hand-holding-heart"></i> Manage Requests
    </a>
    <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>">
      <i class="fas fa-chart-pie"></i> Reports
    </a>
    <a href="site_settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'site_settings.php' ? 'active' : '' ?>">
      <i class="fas fa-cogs"></i> Settings
    </a>
  </div>
</nav>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const main = document.querySelector('.main');
  sidebar.classList.toggle('open');
  sidebar.classList.toggle('closed');
  main.classList.toggle('full');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.querySelector('.toggle-btn');
  
  if (window.innerWidth <= 992 && !sidebar.contains(event.target) && event.target !== toggleBtn && !toggleBtn.contains(event.target)) {
    sidebar.classList.remove('open');
    sidebar.classList.add('closed');
    document.querySelector('.main').classList.add('full');
  }
});
</script>