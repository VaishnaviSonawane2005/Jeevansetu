<?php
// ─────────────────────────────────────────────────────────
//  JeevanSetu – QR generator (endroid/qr-code v6.x, no setters)
// ─────────────────────────────────────────────────────────
require_once '../includes/db_connect.php';
require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

/* 1. Fetch donor row */
$stmt = $pdo->prepare("
    SELECT  u.name,
            d.user_id        AS donor_id,
            d.blood_group,
            d.city,
            d.contact_number AS contact,
            d.status
    FROM users  AS u
    JOIN donors AS d ON u.id = d.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor || $donor['status'] != 1) { http_response_code(403); exit; }

/* 2. Compose text for QR  */
$qrText = sprintf(
    "Name: %s\nID: %s\nBlood Group: %s\nCity: %s\nContact: %s",
    $donor['name'],
    $donor['donor_id'],
    $donor['blood_group'],
    $donor['city'],
    $donor['contact']
);

/* 3. Create QR code (constructor is enough) */
$qrCode = new QrCode($qrText);          // defaults: UTF-8, H-level ECC, 300 px
$writer = new PngWriter();
$image  = $writer->write($qrCode)->getString();

/* 4. Echo as base-64 for inline <img>  */
echo 'data:image/png;base64,'.base64_encode($image);
