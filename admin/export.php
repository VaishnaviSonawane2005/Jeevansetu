<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

// Get export type from query string
$type = $_GET['type'] ?? '';

$filename = '';
$data = [];
$headers = [];

switch ($type) {
    case 'donors':
        $stmt = $pdo->query("SELECT users.name, users.email, donors.contact_number, donors.age, donors.organs, donors.blood_group, donors.city, donors.status, donors.last_donation_date
                             FROM donors 
                             JOIN users ON donors.user_id = users.id");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'donors_export_' . date('Ymd_His') . '.csv';
        $headers = ['Name', 'Email', 'Contact Number', 'Age', 'Organ Donation', 'Blood Group', 'City', 'Status','Last Donation Date'];
        break;

    case 'requests':
        $stmt = $pdo->query("SELECT patient_name, contact_number, blood_group, hospital_name, hospital_city, status, organs, created_at, required_date FROM requests");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'requests_export_' . date('Ymd_His') . '.csv';
        $headers = ['Name', 'Phone', 'Blood Group', 'City', 'Status', 'Requested On'];
        break;

    case 'donations':
        $stmt = $pdo->query("SELECT donors.user_id, donations.donation_date, donations.donated_item
                             FROM donations 
                             JOIN donors ON donations.donor_id = donors.user_id");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = 'donations_export_' . date('Ymd_His') . '.csv';
        $headers = ['Donor Name', 'Donation Date', 'Location'];
        break;

    default:
        http_response_code(400);
        echo 'Invalid export type.';
        exit;
}

// Output headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, $headers);
foreach ($data as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
