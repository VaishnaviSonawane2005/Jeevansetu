<?php
session_start();
require_once '../includes/db_connect.php';
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/* ── Check if donor_id is provided ───────────────────────────── */
$donor_id = isset($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;
if ($donor_id <= 0) {
    echo '<h2 style="color:#e74c3c;text-align:center;margin-top:3rem">❌ No donor ID provided.</h2>';
    exit;
}

/* ── 1  fetch donor row ───────────────────────────────────────── */
$stmt = $pdo->prepare("SELECT u.name, u.email, u.is_verified,
                             d.user_id AS donor_id,
                             d.profile_pic, d.donation_count,
                             d.blood_group, d.city, d.contact_number AS contact,
                             d.status
                      FROM users AS u
                      JOIN donors AS d ON u.id = d.user_id
                      WHERE u.id = ?");
$stmt->execute([$donor_id]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor || $donor['status'] != 1 || $donor['is_verified'] != 1) {
    echo '<h2 style="color:#e74c3c;text-align:center;margin-top:3rem">❌ Your donor profile is not approved or verified yet.</h2>';
    exit;
}

/* ── 2  generate QR code ─────────────────────────────────────── */
$qrText = "Name: {$donor['name']}\nID: {$donor['donor_id']}\nBlood Group: {$donor['blood_group']}\nCity: {$donor['city']}\nContact: {$donor['contact']}";
$qrCode = new QrCode($qrText);
$writer = new PngWriter();
$qrBase64 = 'data:image/png;base64,' . base64_encode($writer->write($qrCode)->getString());

/* ── 3  profile image fallback ───────────────────────────────── */
$profileFile = htmlspecialchars($donor['profile_pic'] ?? '');
$profilePic = (!empty($profileFile) && file_exists("../uploads/$profileFile"))
    ? "../uploads/$profileFile"
    : "../assets/default.jpg";

$starsHtml = ($cnt = min(5, ceil(($donor['donation_count'] ?? 0) / 2))) ? str_repeat('⭐', $cnt) : '—';

/* ── 4  HTML donor card ───────────────────────────────────────── */
ob_start();
include '../components/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Donor Card | JeevanSetu</title>
  <style>
    :root{--brand:#e74c3c;--dark:#333}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',sans-serif;background:#f2f4f8;padding:24px 8px;text-align:center}
    .card{width:360px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.15);margin:auto;margin-bottom:16px;position:relative}
    .strip{height:60px;background:var(--brand);display:flex;align-items:center;padding:0 16px;justify-content:flex-start;position:relative}
    .strip img {
      width: 40px;
      height: 40px;
      background: #fff;
      border-radius: 50%;
      padding: 0;
      object-fit: contain;
    }
    .verified-badge{position:absolute;top:14px;right:16px;background:#fff;color:var(--brand);padding:4px 8px;border-radius:18px;font:600 11px/1 'Segoe UI';box-shadow:0 0 5px rgba(0,0,0,.12)}
    .avatar{width:75px;height:75px;border-radius:50%;object-fit:cover;border:4px solid #fff;position:absolute;top:42px;left:50%;transform:translateX(-50%)}
    h1{text-align:center;color:var(--brand);margin-top:90px;font-size:18px}
    .info{padding:14px 22px 12px;color:var(--dark);font-size:14px;text-align:left}
    .info p{margin:6px 0}
    .qr{display:flex;justify-content:center;margin:12px 0 16px}
    .qr img{width:140px;border-radius:8px;border:4px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.12)}
    .stars{text-align:center;font-size:18px;margin-bottom:14px;color:#f5c518}
    .btn-bar{display:flex;gap:10px;justify-content:center;margin-top:12px}
    .btn{flex:1;max-width:160px;text-align:center;padding:10px 0;background:var(--brand);color:#fff;font-weight:600;font-size:14px;border:none;border-radius:6px;cursor:pointer;transition:background .2s}
    .btn:hover{background:#c0392b}
    @media print{.btn-bar{display:none}}
  </style>
</head>
<body>
  <div class="card">
    <div class="strip">
      <img src="../assets/logo.png" alt="Logo">
    </div>
    <div class="verified-badge">✔ Verified</div>
    <img class="avatar" src="<?= $profilePic ?>" alt="Profile Photo">
    <h1>JeevanSetu Donor Card</h1>
    <div class="info">
      <p><strong>Name:</strong> <?= $donor['name'] ?></p>
      <p><strong>ID:</strong> <?= $donor['donor_id'] ?></p>
      <p><strong>Blood Group:</strong> <?= $donor['blood_group'] ?></p>
      <p><strong>City:</strong> <?= $donor['city'] ?></p>
      <p><strong>Contact:</strong> <?= $donor['contact'] ?></p>
    </div>
    <div class="qr"><img src="<?= $qrBase64 ?>" alt="QR Code"></div>
    <div class="stars"><?= $starsHtml ?></div>
  </div>

  <div class="btn-bar">
    <form method="post"><button class="btn" name="download">📥 Download PDF</button></form>
    <button class="btn" onclick="window.print()">🖨 Print</button>
  </div>
</body>
</html>
<?php
$cardHtml = ob_get_clean();

/* ── 5  PDF download ───────────────────────────────────────────── */
if (isset($_POST['download'])) {
    $opts = new Options();
    $opts->set('isRemoteEnabled', true);
    $pdf = new Dompdf($opts);
    $pdf->loadHtml($cardHtml);
    $pdf->setPaper('A4');
    $pdf->render();
    $pdf->stream('donor_card.pdf', ['Attachment' => 1]);
    exit;
}

echo $cardHtml;
