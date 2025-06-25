<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

// Get current settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Delete all existing settings
        $pdo->exec("DELETE FROM site_settings");
        
        // Insert new settings
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Settings updated successfully!";
        header("Location: site_settings.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error updating settings: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings | JeevanSetu Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../components/admin_header.php'; ?>
    
    <main class="container">
        <h1>Site Settings</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="settings-form">
                <div class="setting-group">
                    <h2>General Settings</h2>
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="settings[site_name]" value="<?= htmlspecialchars($settings['site_name'] ?? 'JeevanSetu') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_email">Admin Email</label>
                        <input type="email" id="site_email" name="settings[site_email]" value="<?= htmlspecialchars($settings['site_email'] ?? 'admin@jeevansetu.org') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="items_per_page">Items Per Page</label>
                        <input type="number" id="items_per_page" name="settings[items_per_page]" min="5" max="100" value="<?= htmlspecialchars($settings['items_per_page'] ?? '20') ?>">
                    </div>
                </div>
                
                <div class="setting-group">
                    <h2>Donation Settings</h2>
                    
                    <div class="form-group">
                        <label for="blood_donation_interval">Blood Donation Interval (days)</label>
                        <input type="number" id="blood_donation_interval" name="settings[blood_donation_interval]" min="30" value="<?= htmlspecialchars($settings['blood_donation_interval'] ?? '90') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_active_requests">Max Active Requests per User</label>
                        <input type="number" id="max_active_requests" name="settings[max_active_requests]" min="1" value="<?= htmlspecialchars($settings['max_active_requests'] ?? '3') ?>">
                    </div>
                </div>
                
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
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <button type="reset" class="btn btn-secondary">Reset Changes</button>
            </div>
        </form>
    </main>
    
    <?php include '../components/footer.php'; ?>
</body>
</html>