<?php require_once '../includes/session_check.php'; ?>
<?php
// Ensure user ID is accessible from session for fallback
$donorId = $donor['user_id'] ?? ($_SESSION['user_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Donor Dashboard | JeevanSetu</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  :root{
    --primary:#e63946;
    --primary-dark:#c92d3b;
    --bg-1:#ff9e7c;
    --bg-2:#ffc55e;
  }

  /* banner */
  .dashboard-banner{
    padding:2.5rem 1rem;
    background:linear-gradient(135deg,var(--primary),var(--bg-1));
    color:#fff;text-align:center;
  }
  .dashboard-banner h1{margin:0 0 .3rem;font-size:2rem}
  .dashboard-banner p{margin:0;font-size:1.1rem}

  /* grid layout */
  .dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:1.5rem;
    padding:2rem 1rem;
    max-width:960px;
    margin:auto;
  }
  .dashboard-card{
    background:#fff;border-radius:12px;
    box-shadow:0 8px 18px rgba(0,0,0,.12);
    padding:1.5rem 1.25rem;text-align:center;
    display:flex;flex-direction:column;justify-content:space-between;
  }
  .dashboard-card h2{margin:.2rem 0 .6rem;color:var(--primary)}
  .dashboard-card p{flex:1;color:#555;font-size:.95rem}
  .dashboard-card .btn{
    align-self:center;margin-top:1rem;padding:.55rem 1.2rem;
    background:var(--primary);color:#fff;border:none;border-radius:8px;
    font-weight:600;cursor:pointer;transition:background .25s ease;
    text-decoration:none;display:inline-block;
  }
  .dashboard-card .btn:hover{background:var(--primary-dark)}

  /* quick actions */
  .quick-actions{
    padding:2rem 1rem;text-align:center;
  }
  .quick-actions h2{margin-bottom:1rem;color:var(--primary)}
  .quick-actions .btn{
    margin:.4rem .3rem;padding:.65rem 1.2rem;border-radius:9px;
    font-weight:700;text-decoration:none;display:inline-block;
  }
  .btn-primary{background:var(--primary);color:#fff}
  .btn-primary:hover{background:var(--primary-dark)}
  .btn-secondary{background:#ccc}
  .btn-secondary:hover{background:#b5b5b5}
</style>
</head>
<body>

<?php include '../components/header.php'; ?>
<section class="dashboard-banner">
  <h1>Welcome, Donor!</h1>
  <p>Thank you for being someone’s lifeline.</p>
</section>

<main>
  <div class="dashboard-grid">
    <div class="dashboard-card">
      <h2>Your Profile</h2>
      <p>View and update your donor information</p>
      <a href="update_profile.php" class="btn">Manage Profile</a>
    </div>

    <div class="dashboard-card">
      <h2>Donation History</h2>
      <p>Track all your previous donations</p>
      <a href="donation_history.php" class="btn">View History</a>
    </div>

    <div class="dashboard-card">
      <h2>Your Donor Card</h2>
      <p>Access your digital donor ID card</p>
      <a href="../qr/donor_card.php?donor_id=<?= $donorId ?>" class="btn btn-primary">🎫 View Donor Card</a>
    </div>

    <div class="dashboard-card">
      <h2>Current Requests</h2>
      <p>See blood / organ requests nearby</p>
      <a href="../requester/search_donors.php" class="btn">View Requests</a>
    </div>

    <div class="dashboard-card" style="grid-column:1 / -1; max-width:360px; margin:auto;">
      <h2>My Notifications</h2>
      <p>View the Donation Requests you received</p>
      <a href="../donor/my_notifications.php" class="btn">My Notifications</a>
    </div>
  </div>

  <div class="quick-actions">
    <h2>Quick Actions</h2>
    <a href="register_donation.php" class="btn btn-primary">➕ Register New Donation</a>
    <a href="../auth/logout.php" class="btn btn-secondary">🚪 Logout</a>
  </div>
</main>

<?php include '../components/footer.php'; ?>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header ? header.offsetHeight : 0;
    document.body.style.paddingTop = `${headerHeight + 20}px`;
  });
</script>

</body>
</html>