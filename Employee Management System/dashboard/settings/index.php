<?php
/**
 * System Settings
 * Admin page for system configuration
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and admin access
require_login();
require_admin();

$errors = [];
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $settings = $_POST['settings'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value, $value]);
            }
            
            $pdo->commit();
            log_activity($_SESSION['user_id'], 'Updated system settings');
            $success = 'Settings updated successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to update settings';
            error_log("Update settings error: " . $e->getMessage());
        }
    }
}

// Fetch all settings
try {
    $stmt = $pdo->query("SELECT * FROM system_settings ORDER BY setting_key");
    $settings_data = $stmt->fetchAll();
    $settings = [];
    foreach ($settings_data as $row) {
        $settings[$row['setting_key']] = $row;
    }
} catch (PDOException $e) {
    error_log("Fetch settings error: " . $e->getMessage());
    $settings = [];
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
</head>
<body class="dashboard-page admin-page">
    
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>⚙️ System Settings</h1>
                <p>Configure company information and system policies</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="card">
                    <h2>Company Information</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="settings[company_name]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_name']['setting_value'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="company_email">Support Email</label>
                            <input type="email" id="company_email" name="settings[company_email]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_email']['setting_value'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="company_phone">Contact Phone</label>
                            <input type="text" id="company_phone" name="settings[company_phone]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_phone']['setting_value'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2>Leave Policies</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="leave_annual_days">Annual Leave (Days)</label>
                            <input type="number" id="leave_annual_days" name="settings[leave_annual_days]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['leave_annual_days']['setting_value'] ?? '20'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="leave_sick_days">Sick Leave (Days)</label>
                            <input type="number" id="leave_sick_days" name="settings[leave_sick_days]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['leave_sick_days']['setting_value'] ?? '10'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="leave_casual_days">Casual Leave (Days)</label>
                            <input type="number" id="leave_casual_days" name="settings[leave_casual_days]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['leave_casual_days']['setting_value'] ?? '5'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2>Payroll & Attendance</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="payroll_tax_rate">Default Tax Rate (%)</label>
                            <input type="number" step="0.01" id="payroll_tax_rate" name="settings[payroll_tax_rate]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['payroll_tax_rate']['setting_value'] ?? '15'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="attendance_grace_period">Late Grace Period (Minutes)</label>
                            <input type="number" id="attendance_grace_period" name="settings[attendance_grace_period]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['attendance_grace_period']['setting_value'] ?? '15'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (Seconds)</label>
                            <input type="number" id="session_timeout" name="settings[session_timeout]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['session_timeout']['setting_value'] ?? '1800'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
                    <button type="reset" class="btn btn-secondary btn-lg">Reset Changes</button>
                </div>
            </form>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
