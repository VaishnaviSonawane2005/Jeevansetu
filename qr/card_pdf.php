<?php
session_start();
require_once '../includes/db_connect.php';
require __DIR__.'/../vendor/autoload.php';

use Dompdf\Dompdf;

/* Re-use card HTML by including donor_card.php into a buffer */
ob_start();
require 'donor_card.php';
$html = ob_get_clean();

/* Remove buttons (they’re inside .btn-bar) */
$html = preg_replace('/<div class="btn-bar">.*?<\/div>/s', '', $html);

/* Create PDF */
$pdf = new Dompdf(['defaultFont' => 'Helvetica']);
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();

/* Stream to browser */
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="donor_card.pdf"');
echo $pdf->output();
