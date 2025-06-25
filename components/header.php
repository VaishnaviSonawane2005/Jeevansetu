<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$BASE_PATH = '/jeevansetu/';
$current_page = basename($_SERVER['PHP_SELF']);
$is_user   = isset($_SESSION['user_id']);
$is_admin  = isset($_SESSION['admin_logged_in']);
$user_role = $_SESSION['role'] ?? '';
$dashboard_link = $user_role === 'donor'
    ? $BASE_PATH . 'donor/dashboard.php'
    : $BASE_PATH . 'requester/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= isset($page_title)
              ? htmlspecialchars($page_title)
              : 'JeevanSetu – National Blood & Organ Donation Network'; ?></title>

  <link rel="stylesheet" href="<?= $BASE_PATH ?>css/style.css">
  <link rel="stylesheet" href="<?= $BASE_PATH ?>css/loader.css">
  <link rel="icon" type="image/x-icon" href="<?= $BASE_PATH ?>assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <style>
    :root {
      --primary: #e63946;
      --gray-800: #333;
      --shadow-sm: 0 2px 4px rgba(0,0,0,.1);
      --shadow-md: 0 4px 8px rgba(0,0,0,.15);
      --transition: all 0.3s ease-in-out;
    }

    body {
      padding-top: 0;
      transition: padding-top 0.3s ease-in-out;
    }

    .header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: #fff;
      z-index: 1000;
      padding: 10px 20px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }

    .header.scrolled {
      box-shadow: var(--shadow-md);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      min-height: 80px;
    }

    .logo-link {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .logo-link img {
      height: 60px;
      width: auto;
    }

    .logo-text {
      font: 900 2rem 'Montserrat', sans-serif;
      color: var(--primary);
      white-space: nowrap;
    }

    .main-nav ul {
      list-style: none;
      display: flex;
      gap: 2rem;
      margin: 0;
      padding: 0;
    }

    .main-nav a {
      text-decoration: none;
      color: var(--gray-800);
      font-weight: 500;
      padding-bottom: 4px;
      position: relative;
    }

    .main-nav a.active,
    .main-nav a:hover {
      color: var(--primary);
    }

    .main-nav a.active::after,
    .main-nav a:hover::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background: var(--primary);
    }

    .btn {
      padding: 0.4rem 0.9rem;
      border: none;
      border-radius: 20px;
      background: var(--primary);
      color: #fff;
      text-decoration: none;
    }

    .btn-secondary {
      background: #555;
    }

    .btn-small {
      font-size: 0.9rem;
      padding: 0.3rem 0.75rem;
    }

    .mobile-menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.8rem;
      cursor: pointer;
    }

    .mobile-nav-container {
      display: none;
    }

    @media (max-width: 768px) {
      .main-nav {
        display: none;
      }

      .mobile-menu-toggle {
        display: block;
        align-self: flex-end;
        margin-top: 10px;
      }

      .mobile-nav-container.open {
        display: block;
      }

      .mobile-nav-container nav ul {
        list-style: none;
        margin: 0;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        background: #fff;
        box-shadow: var(--shadow-md);
      }
    }
  </style>
</head>
<body>

<!-- BLOOD THEME LOADER -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/jeevansetu/includes/loader.php'; ?>

<header class="header" id="siteHeader">
  <div class="container">
    <a href="<?= $BASE_PATH ?>index.php" class="logo-link">
      <img src="<?= $BASE_PATH ?>assets/logo.png" alt="JeevanSetu logo">
      <span class="logo-text">JeevanSetu</span>
    </a>

    <nav class="main-nav">
      <ul>
        <li><a href="<?= $BASE_PATH ?>index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= $BASE_PATH ?>about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About</a></li>
        <li><a href="<?= $BASE_PATH ?>faq.php" class="<?= $current_page === 'faq.php' ? 'active' : '' ?>">FAQ</a></li>
        <li><a href="<?= $BASE_PATH ?>contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>

        <?php if ($is_user): ?>
          <li><a href="<?= $dashboard_link ?>" class="btn btn-small">Dashboard</a></li>
          <li><a href="<?= $BASE_PATH ?>auth/logout.php" class="btn btn-small btn-secondary">Logout</a></li>
        <?php elseif ($is_admin): ?>
          <li><a href="<?= $BASE_PATH ?>admin/dashboard.php" class="btn btn-small">Admin Panel</a></li>
          <li><a href="<?= $BASE_PATH ?>admin/logout.php" class="btn btn-small btn-secondary">Logout</a></li>
        <?php else: ?>
          <li><a href="<?= $BASE_PATH ?>auth/login.php" class="btn btn-small">Login</a></li>
          <li><a href="<?= $BASE_PATH ?>auth/register.php" class="btn btn-small">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <button id="mobileToggle" class="mobile-menu-toggle">☰</button>
  </div>
</header>

<div id="mobileNav" class="mobile-nav-container">
  <nav class="mobile-nav">
    <ul>
      <li><a href="<?= $BASE_PATH ?>index.php">Home</a></li>
      <li><a href="<?= $BASE_PATH ?>about.php">About</a></li>
      <li><a href="<?= $BASE_PATH ?>faq.php">FAQ</a></li>
      <li><a href="<?= $BASE_PATH ?>contact.php">Contact</a></li>

      <?php if ($is_user): ?>
        <li><a href="<?= $dashboard_link ?>">Dashboard</a></li>
        <li><a href="<?= $BASE_PATH ?>auth/logout.php">Logout</a></li>
      <?php elseif ($is_admin): ?>
        <li><a href="<?= $BASE_PATH ?>admin/dashboard.php">Admin Panel</a></li>
        <li><a href="<?= $BASE_PATH ?>admin/logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="<?= $BASE_PATH ?>auth/login.php">Login</a></li>
        <li><a href="<?= $BASE_PATH ?>auth/register.php">Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<script src="<?= $BASE_PATH ?>js/loader.js" defer></script>
<script>
  document.getElementById('mobileToggle')
          .addEventListener('click', () => {
              document.getElementById('mobileNav').classList.toggle('open');
          });

  window.addEventListener('scroll', () => {
    document.getElementById('siteHeader')
            .classList.toggle('scrolled', window.scrollY > 10);
  });

  function adjustBodyPadding() {
    const header = document.querySelector('.header');
    if (header) {
      document.body.style.paddingTop = header.offsetHeight + 'px';
    }
  }

  window.addEventListener('load', adjustBodyPadding);
  window.addEventListener('resize', adjustBodyPadding);
</script>