<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

// Get current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM site_settings");

        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$key, $value]);
        }

        $pdo->commit();
        $_SESSION['success'] = "✅ Settings updated successfully!";
        header("Location: site_settings.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "❌ Error updating settings: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Site Settings | JeevanSetu Admin</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    :root {
      --sidebar-width: 260px;
      --header-height: 70px;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #f5f7fa;
    }

    .main-wrapper {
      padding: 2rem;
      margin-left: var(--sidebar-width);
      margin-top: var(--header-height);
      transition: margin 0.3s ease;
    }

    .sidebar.closed ~ .main-wrapper {
      margin-left: 0 !important;
    }

    h1 { margin-bottom: 1rem; color:rgba(1, 2, 65, 0.92); }


    .setting-group {
      background-color: #fff;
      border-radius: 8px;
      padding: 25px 20px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .setting-group h2 {
      margin-top: 0;
      font-size: 20px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 10px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
    }

    input[type="text"],
    input[type="email"],
    input[type="number"],
    select,
    textarea {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    input:focus, select:focus, textarea:focus {
      border-color: #007bff;
      outline: none;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .btn {
      padding: 10px 18px;
      font-size: 15px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-primary {
      background-color: #007bff;
      color: white;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .alert {
      padding: 12px 20px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

    @media (max-width: 768px) {
      .main-wrapper {
        margin-left: 0 !important;
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <?php include '../components/admin_header.php'; ?>

  <div class="main-wrapper">
    <h1>⚙️ Site Settings</h1>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="post">
      <!-- General Settings -->
      <div class="setting-group">
        <h2>General Settings</h2>

        <div class="form-group">
          <label for="site_name">Site Name</label>
          <input type="text" id="site_name" name="settings[site_name]" value="<?= htmlspecialchars($settings['site_name'] ?? 'JeevanSetu') ?>" required>
        </div>

        <div class="form-group">
          <label for="site_email">Admin Email</label>
          <input type="email" id="site_email" name="settings[site_email]" value="<?= htmlspecialchars($settings['site_email'] ?? 'admin@jeevansetu.org') ?>" required>
        </div>

        <div class="form-group">
          <label for="items_per_page">Items Per Page</label>
          <input type="number" id="items_per_page" name="settings[items_per_page]" min="5" max="100" value="<?= htmlspecialchars($settings['items_per_page'] ?? '20') ?>" required>
        </div>
      </div>

      <!-- Donation Settings -->
      <div class="setting-group">
        <h2>Donation Settings</h2>

        <div class="form-group">
          <label for="blood_donation_interval">Blood Donation Interval (days)</label>
          <input type="number" id="blood_donation_interval" name="settings[blood_donation_interval]" min="30" value="<?= htmlspecialchars($settings['blood_donation_interval'] ?? '90') ?>" required>
        </div>

        <div class="form-group">
          <label for="max_active_requests">Max Active Requests per User</label>
          <input type="number" id="max_active_requests" name="settings[max_active_requests]" min="1" value="<?= htmlspecialchars($settings['max_active_requests'] ?? '3') ?>" required>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="setting-group">
        <h2>Notification Settings</h2>

        <div class="form-group">
          <label for="notify_admin">Notify Admin on New Request</label>
          <select id="notify_admin" name="settings[notify_admin]">
            <option value="1" <?= ($settings['notify_admin'] ?? '1') == '1' ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= ($settings['notify_admin'] ?? '1') == '0' ? 'selected' : '' ?>>No</option>
          </select>
        </div>

        <div class="form-group">
          <label for="notify_donors">Notify Matching Donors</label>
          <select id="notify_donors" name="settings[notify_donors]">
            <option value="1" <?= ($settings['notify_donors'] ?? '1') == '1' ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= ($settings['notify_donors'] ?? '1') == '0' ? 'selected' : '' ?>>No</option>
          </select>
        </div>
      </div>

      <!-- Maintenance Settings -->
      <div class="setting-group">
        <h2>Maintenance Mode</h2>

        <div class="form-group">
          <label for="maintenance_mode">Maintenance Mode</label>
          <select id="maintenance_mode" name="settings[maintenance_mode]">
            <option value="0" <?= ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' ?>>Disabled</option>
            <option value="1" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : '' ?>>Enabled</option>
          </select>
        </div>

        <div class="form-group">
          <label for="maintenance_message">Maintenance Message</label>
          <textarea id="maintenance_message" name="settings[maintenance_message]" rows="3"><?= htmlspecialchars($settings['maintenance_message'] ?? 'The site is currently under maintenance. Please check back later.') ?></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Save Settings</button>
        <button type="reset" class="btn btn-secondary">🔄 Reset</button>
      </div>
    </form>
  </div>

  <?php include '../components/footer.php'; ?>
</body>
</html>
