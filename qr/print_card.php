<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// Get donor information
$stmt = $pdo->prepare("SELECT u.name, d.* FROM users u 
                      JOIN donors d ON u.id = d.user_id 
                      WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$donor = $stmt->fetch();

if (!$donor || empty($donor['qr_code'])) {
    die("Donor card not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Donor Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .donor-card-print {
            width: 85mm;
            height: 54mm;
            border: 2px solid #d43f3a;
            border-radius: 5px;
            display: flex;
            position: relative;
            margin: 10px auto;
        }
        .donor-info-print {
            flex: 2;
            padding: 5px;
            font-size: 10px;
        }
        .donor-qr-print {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }
        .donor-qr-print img {
            max-width: 100%;
            max-height: 100%;
        }
        .donor-header-print {
            color: #d43f3a;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
        }
        .donor-detail-print {
            margin-bottom: 3px;
        }
        .donor-detail-print label {
            font-weight: bold;
            font-size: 8px;
        }
        @page {
            size: auto;
            margin: 0;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.location.href = 'donor_card.php';
            }, 1000);
        };
    </script>
</head>
<body>
    <div class="donor-card-print">
        <div class="donor-info-print">
            <div class="donor-header-print">JeevanSetu Donor Card</div>
            
            <div class="donor-detail-print">
                <label>Name:</label> <?= substr(htmlspecialchars($donor['name']), 0, 20) ?>
            </div>
            
            <div class="donor-detail-print">
                <label>Blood Group:</label> <?= $donor['blood_group'] ?>
            </div>
            
            <div class="donor-detail-print">
                <label>Contact:</label> <?= htmlspecialchars($donor['contact_number']) ?>
            </div>
            
            <div class="donor-detail-print">
                <label>ID:</label> DONOR-<?= $_SESSION['user_id'] ?>
            </div>
            
            <?php if (!empty($donor['organs'])): ?>
                <div class="donor-detail-print">
                    <label>Organs:</label> <?= substr(ucwords(str_replace(',', ', ', $donor['organs'])), 0, 20) ?>
                </div>
            <?php endif; ?>
            
            <div class="donor-detail-print">
                <label>Status:</label> <?= $donor['status'] ? 'Active' : 'Inactive' ?>
            </div>
        </div>
        
        <div class="donor-qr-print">
            <img src="../assets/qrcodes/<?= htmlspecialchars($donor['qr_code']) ?>" alt="Donor QR Code">
        </div>
    </div>
</body>
</html>