<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

/* ---------- normalise & trim inputs ---------- */
function clean($v) { return trim($v ?? ''); }

$blood_group = strtoupper(clean($_GET['blood_group'] ?? ''));
$city        = strtolower(clean($_GET['city']        ?? ''));
$organ       = strtolower(clean($_GET['organ']       ?? ''));

/* ---------- build dynamic SQL ---------- */
$sql   = "SELECT d.*, u.name, u.email
          FROM   donors d
          JOIN   users  u ON d.user_id = u.id
          WHERE  d.status = 1";
$args  = [];

/* exact blood group match, case-insensitive */
if ($blood_group !== '') {
    $sql  .= " AND UPPER(d.blood_group) = ?";
    $args[] = $blood_group;
}

/* city substring match */
if ($city !== '') {
    $sql  .= " AND LOWER(d.city) LIKE ?";
    $args[] = "%$city%";
}

/* organ list match – surrounds list with commas to avoid partial hits */
if ($organ !== '') {
    $sql  .= " AND LOWER(CONCAT(',', REPLACE(d.organs,' ',''), ',')) LIKE ?";
    $args[] = "%,$organ,%";
}

$sql .= " ORDER BY d.last_donation_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Donors | JeevanSetu</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  /* -------- minimal inline tweaks for clarity -------- */
  body{font-family:'Segoe UI',sans-serif;background:#f4f6f8;margin:0}
  main{max-width:1200px;margin:auto;padding:2rem}
  .filter-form{display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;
               background:#fff;padding:1rem;border-radius:10px;
               box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:2rem}
  .filter-group{display:flex;flex-direction:column;min-width:180px}
  .filter-group label{font-weight:600;margin-bottom:4px}
  .filter-group select,.filter-group input{padding:8px;border:1px solid #ccc;border-radius:6px}
  .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:700;cursor:pointer}
  .btn-primary{background:#e74c3c;color:#fff}.btn-secondary{background:#95a5a6;color:#fff}
  .donor-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem}
  .donor-card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:1.5rem;
              transition:transform .2s}.donor-card:hover{transform:scale(1.02)}
  .donor-badge{display:flex;gap:8px;margin-bottom:8px}
  .blood-group{background:#c0392b;color:#fff;padding:5px 10px;border-radius:5px;font-weight:700}
  .organ-donor{background:#27ae60;color:#fff;padding:5px 10px;border-radius:5px;font-size:.85rem}
  h1,h2{text-align:center;color:#2c3e50;margin:0 0 1rem}
  .alert{background:#ecf0f1;padding:1rem;border-radius:10px;text-align:center;margin-top:1rem}
  .btn-small{font-size:.9rem;padding:6px 12px;margin-right:6px}
  @media (max-width:600px){.filter-form{flex-direction:column}}
</style>
</head>
<body>
<?php include '../components/header.php'; ?>

<main>
  <h1>Search Donors</h1>

  <!-- ---------- filters ---------- -->
  <form method="get" class="filter-form">
      <div class="filter-group">
          <label for="blood_group">Blood Group</label>
          <select id="blood_group" name="blood_group">
              <option value="">Any</option>
              <?php foreach (["A+","A-","B+","B-","AB+","AB-","O+","O-"] as $g): ?>
                  <option value="<?= $g ?>" <?= $blood_group===$g?'selected':'' ?>><?= $g ?></option>
              <?php endforeach; ?>
          </select>
      </div>

      <div class="filter-group">
          <label for="city">City</label>
          <input id="city" name="city" type="text" value="<?= htmlspecialchars($city) ?>" placeholder="Enter city">
      </div>

      <div class="filter-group">
          <label for="organ">Organ (optional)</label>
          <select id="organ" name="organ">
              <option value="">None</option>
              <?php foreach (["kidney","liver","pancreas","heart","lungs","eyes"] as $o): ?>
                  <option value="<?= $o ?>" <?= $organ===$o?'selected':'' ?>><?= ucfirst($o) ?></option>
              <?php endforeach; ?>
          </select>
      </div>

      <button class="btn btn-primary" type="submit">Search</button>
      <a href="search_donors.php" class="btn btn-secondary">Reset</a>
  </form>

  <!-- ---------- results ---------- -->
  <div class="search-results" data-count="<?= count($donors) ?>">
      <h2>Available Donors</h2>

      <?php if (!$donors): ?>
        <div class="alert">
            No donors match your criteria.<br>
            <a href="request_form.php" class="btn btn-primary" style="margin-top:10px">Create Request</a>
        </div>
      <?php else: ?>
        <div class="donor-grid">
          <?php foreach ($donors as $d): ?>
            <div class="donor-card">
              <div class="donor-badge">
                <span class="blood-group"><?= htmlspecialchars($d['blood_group']) ?></span>
                <?php if (!empty($d['organs'])): ?>
                  <span class="organ-donor">Organ Donor</span>
                <?php endif; ?>
              </div>
              <h3><?= htmlspecialchars($d['name']) ?></h3>
              <p><strong>City:</strong> <?= htmlspecialchars($d['city']) ?></p>
              <p><strong>Contact:</strong> <?= htmlspecialchars($d['contact_number']) ?></p>
              <p><strong>Email:</strong> <?= htmlspecialchars($d['email']) ?></p>
              <?php if ($d['organs']): ?>
                <p><strong>Organs:</strong> <?= ucwords(str_replace(',', ', ', $d['organs'])) ?></p>
              <?php endif; ?>
              <?php if ($d['last_donation_date']): ?>
                <p><small>Last Donation: <?= date('M Y', strtotime($d['last_donation_date'])) ?></small></p>
              <?php endif; ?>
              <div style="margin-top:10px">
                <a href="tel:<?= $d['contact_number'] ?>" class="btn btn-small btn-primary">Call</a>
                <a href="mailto:<?= $d['email'] ?>" class="btn btn-small btn-secondary">Email</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
  </div>
</main>

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
