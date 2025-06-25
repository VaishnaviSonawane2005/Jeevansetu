<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php'; // Use shared sanitize_input

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_type = sanitize_input($_POST['request_type']);
    $patient_name = sanitize_input($_POST['patient_name']);
    $blood_group = sanitize_input($_POST['blood_group']);
    $hospital_name = sanitize_input($_POST['hospital_name']);
    $hospital_city = sanitize_input($_POST['hospital_city']);
    $required_date = sanitize_input($_POST['required_date']);
    $urgency = sanitize_input($_POST['urgency']);
    $contact_person = sanitize_input($_POST['contact_person']);
    $contact_number = sanitize_input($_POST['contact_number']);
    $additional_info = sanitize_input($_POST['additional_info']);

    $medical_proof = '';
    if (isset($_FILES['medical_proof']) && $_FILES['medical_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['medical_proof']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['medical_proof']['tmp_name'], $target_path)) {
            $medical_proof = $file_name;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO requests (
            user_id, type, patient_name, blood_group, hospital_name, hospital_city,
            required_date, urgency, contact_person, contact_number, 
            additional_info, medical_proof, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        $stmt->execute([
            $_SESSION['user_id'], $request_type, $patient_name, $blood_group,
            $hospital_name, $hospital_city, $required_date, $urgency,
            $contact_person, $contact_number, $additional_info, $medical_proof
        ]);

        $request_id = $pdo->lastInsertId();

        // Attempt match
        $stmt = $pdo->prepare("SELECT d.*, u.name FROM donors d 
                               JOIN users u ON d.user_id = u.id
                               WHERE d.status = 1 AND d.city = ? AND d.blood_group = ?");
        $stmt->execute([$hospital_city, $blood_group]);
        $potential_donors = $stmt->fetchAll();

        if (!empty($potential_donors)) {
            $pdo->prepare("UPDATE requests SET status = 'matched' WHERE id = ?")->execute([$request_id]);
            foreach ($potential_donors as $donor) {
                $pdo->prepare("INSERT INTO request_matches (request_id, donor_id) VALUES (?, ?)")
                    ->execute([$request_id, $donor['user_id']]);
            }
        }

        header("Location: request_status.php?id=$request_id");
        exit();
    } catch (PDOException $e) {
        $error = "Error creating request: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Request | JeevanSetu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <style>
 body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background: #f7f9fc;
    }
    .hero {
      background: linear-gradient(-45deg, #1976d2, #42a5f5, #64b5f6, #1976d2);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding: 2rem 1rem;
      text-align: center;
      color: white;
    }
    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    main.container {
      max-width: 900px;
      margin: auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      margin-top: -40px;
      position: relative;
      z-index: 10;
    }
    h1 {
      color: #333;
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }
    .form-section {
      margin-bottom: 2rem;
    }
    .form-section h2 {
      font-size: 1.3rem;
      margin-bottom: 1rem;
      color: #1976d2;
      border-bottom: 2px solid #bbdefb;
      padding-bottom: 4px;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.4rem;
    }
    input[type="text"],
    input[type="date"],
    input[type="tel"],
    select,
    textarea,
    input[type="file"] {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
    }
    .radio-group label {
      margin-right: 1.5rem;
      font-weight: 500;
    }
    .radio-group input {
      margin-right: 0.3rem;
    }
    .help-text {
      font-size: 0.85rem;
      color: #555;
    }
    .form-actions {
      text-align: center;
      margin-top: 2rem;
    }
    .btn {
      background: #1976d2;
      color: #fff;
      padding: 0.8rem 1.5rem;
      font-size: 1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      margin: 0 0.5rem;
      display: inline-block;
    }
    .btn:hover {
      background: #1565c0;
    }
    .btn-secondary {
      background: #ccc;
      color: #000;
    }
    .alert-danger {
      background: #ffcdd2;
      color: #b71c1c;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      text-align: center;
    }
    @media (max-width: 600px) {
      .btn {
        width: 100%;
        margin: 0.5rem 0;
      }
    }

    </style>
</head>
<body>
<?php include '../components/header.php'; ?>

<main class="container">
    <h1>Create New Blood/Organ Request</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-card">
        <section class="form-section">
            <h2>Request Type</h2>
            <label><input type="radio" name="request_type" value="blood" checked> Blood Donation</label>
            <label><input type="radio" name="request_type" value="organ"> Organ Donation</label>
        </section>

        <section class="form-section">
            <h2>Patient Information</h2>
            <label>Patient Name
                <input type="text" name="patient_name" required>
            </label>
            <label>Blood Group
                <select name="blood_group" required>
                    <option value="">Select</option>
                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?>
                        <option value="<?= $group ?>"><?= $group ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Medical Proof (Optional)
                <input type="file" name="medical_proof" accept="image/*,.pdf">
                <small>Upload prescription or doctor's note</small>
            </label>
        </section>

        <section class="form-section">
            <h2>Hospital Details</h2>
            <label>Hospital Name
                <input type="text" name="hospital_name" required>
            </label>
            <label>City
                <input type="text" name="hospital_city" required>
            </label>
            <label>Required Date
                <input type="date" name="required_date" required>
            </label>
            <label>Urgency Level
                <select name="urgency" required>
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent (within 24 hours)</option>
                    <option value="emergency">Emergency</option>
                </select>
            </label>
        </section>

        <section class="form-section">
            <h2>Contact Details</h2>
            <label>Contact Person
                <input type="text" name="contact_person" required>
            </label>
            <label>Contact Number
                <input type="tel" name="contact_number" required>
            </label>
            <label>Additional Info
                <textarea name="additional_info" rows="3"></textarea>
            </label>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
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
