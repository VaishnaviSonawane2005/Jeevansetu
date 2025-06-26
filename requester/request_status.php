<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

$request_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id) {
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$request_id, $_SESSION['user_id']]);
    $request = $stmt->fetch();

    if (!$request) {
        header("Location: request_status.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT u.id AS donor_user_id, u.name, u.email, d.* FROM request_matches rm
                          JOIN users u ON rm.donor_id = u.id
                          JOIN donors d ON u.id = d.user_id
                          WHERE rm.request_id = ?");
    $stmt->execute([$request_id]);
    $matched_donors = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $all_requests = $stmt->fetchAll();
}

// Handle notification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_donor_id'])) {
    $donor_id = intval($_POST['notify_donor_id']);
    $stmt = $pdo->prepare("INSERT INTO donor_notifications (request_id, donor_user_id, sender_user_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$request_id, $donor_id, $_SESSION['user_id'], 'You have a new blood donation request.']);
    echo "<script>alert('Notification sent to donor!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status | JeevanSetu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background: #f4f7fc; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 960px; margin: auto; padding: 20px; }
        h1{}
        h2, h3 { color: #e74c3c; text-align: center; }
        .request-detail-card, .request-item { background: #fff; border-radius: 8px; padding: 20px; margin: 20px auto; box-shadow: 0 3px 6px rgba(0,0,0,0.1); animation: fadeIn 0.6s ease-in-out; max-width: 840px; }
        .request-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-weight: bold; text-transform: capitalize; }
        .status-badge.pending { background: #f1c40f; color: #fff; }
        .status-badge.matched { background: #27ae60; color: #fff; }
        .status-badge.closed { background: #7f8c8d; color: #fff; }
        .donor-item { display: flex; align-items: center; justify-content: space-between; margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; }
        .donor-info { flex: 1; margin-left: 10px; }
        .donor-actions a, .donor-actions form { display: inline-block; margin-right: 10px; }
        .btn { padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #c0392b; }
        .btn-small { font-size: 0.9em; padding: 4px 10px; }
        .today-badge { background: #ffd54f; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; color: #333; }
        .alert { background: #ecf0f1; padding: 10px; border-left: 4px solid #3498db; margin-bottom: 15px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
<?php include '../components/header.php'; ?>
<h1>📝 Request Details</h1>

<main class="container">
<?php if (isset($request)): ?>
    <div class="request-detail-card">
        <div class="request-header">
            <h2><?= ucfirst($request['type']) ?> - <?= htmlspecialchars($request['patient_name']) ?></h2>
            <span class="status-badge <?= $request['status'] ?>">Status: <?= ucfirst($request['status']) ?></span>
        </div>
        <p><strong>Blood Group:</strong> <?= $request['blood_group'] ?></p>
        <p><strong>Hospital:</strong> <?= htmlspecialchars($request['hospital_name']) ?>, <?= htmlspecialchars($request['hospital_city']) ?></p>
        <p><strong>Required Date:</strong> <?= date('d M Y', strtotime($request['required_date'])) ?></p>
        <p><strong>Urgency:</strong> <?= ucfirst($request['urgency']) ?></p>
        <p><strong>Contact Person:</strong> <?= htmlspecialchars($request['contact_person']) ?> (<?= htmlspecialchars($request['contact_number']) ?>)</p>
        <?php if (!empty($request['additional_info'])): ?><p><strong>Note:</strong> <?= htmlspecialchars($request['additional_info']) ?></p><?php endif; ?>
        <?php if (!empty($request['medical_proof'])): ?>
            <p><strong>Proof:</strong> <a href="../uploads/<?= htmlspecialchars($request['medical_proof']) ?>" target="_blank">View</a> | <a href="../uploads/<?= htmlspecialchars($request['medical_proof']) ?>" download>Download</a></p>
        <?php endif; ?>
        <hr>
        <?php if (!empty($matched_donors)): ?>
            <h3>Matched Donors</h3>
            <?php foreach ($matched_donors as $donor): ?>
                <div class="donor-item">
                    <div class="donor-info">
                        <strong><?= htmlspecialchars($donor['name']) ?></strong><br>
                        <?= htmlspecialchars($donor['blood_group']) ?> | <?= htmlspecialchars($donor['city']) ?><br>
                        📞 <?= htmlspecialchars($donor['contact_number']) ?><br>
                        ✉ <?= htmlspecialchars($donor['email']) ?>
                        <?php if (!empty($donor['organs'])): ?>
                            <br><small>Organs: <?= ucwords(str_replace(',', ', ', $donor['organs'])) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="donor-actions">
                        <a href="tel:<?= $donor['contact_number'] ?>" class="btn btn-small">📞 Call</a>
                        <a href="mailto:<?= $donor['email'] ?>?subject=Blood Donation Request&body=Dear <?= urlencode($donor['name']) ?>,%0AWe need your help for a <?= $request['blood_group'] ?> donation in <?= $request['hospital_city'] ?> on <?= $request['required_date'] ?>. Please consider helping." class="btn btn-small">✉ Email</a>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="notify_donor_id" value="<?= $donor['donor_user_id'] ?>">
                            <button type="submit" class="btn btn-small">📩 Notify</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert">No matched donors yet. Please check back later or <a href="search_donors.php?blood_group=<?= $request['blood_group'] ?>&city=<?= $request['hospital_city'] ?>">search manually</a>.</div>
        <?php endif; ?>
        <div class="request-footer">
            <small>Request ID: <?= $request['id'] ?> | Created on: <?= date('d M Y H:i', strtotime($request['created_at'])) ?></small>
        </div>
        <div style="margin-top: 15px;">
            <a href="request_form.php" class="btn">➕ New Request</a>
            <a href="request_status.php" class="btn btn-small">🔙 Back to All</a>
        </div>
    </div>
<?php else: ?>
    <h1>Your Requests</h1>
    <?php if (empty($all_requests)): ?>
        <div class="alert">You haven't made any requests yet.</div>
        <a href="request_form.php" class="btn">➕ Create Your First Request</a>
    <?php else: ?>
        <?php foreach ($all_requests as $req): ?>
            <div class="request-item">
                <div class="request-header">
                    <h3><?= ucfirst($req['type']) ?> for <?= htmlspecialchars($req['patient_name']) ?></h3>
                    <span class="status-badge <?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span>
                </div>
                <p><?= $req['blood_group'] ?> | <?= htmlspecialchars($req['hospital_city']) ?> | <?= date('d M Y', strtotime($req['required_date'])) ?>
                    <?php if (date('Y-m-d') === date('Y-m-d', strtotime($req['required_date']))): ?> <span class="today-badge">Today</span> <?php endif; ?>
                    <?php if ($req['urgency'] === 'emergency'): ?> <strong>🔥 Emergency</strong> <?php elseif ($req['urgency'] === 'urgent'): ?> <strong>⚠ Urgent</strong> <?php endif; ?>
                </p>
                <a href="request_status.php?id=<?= $req['id'] ?>" class="btn btn-small">🔎 View Details</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
</main>

<?php include '../components/footer.php'; ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`;
  });
</script>
</body>
</html>
