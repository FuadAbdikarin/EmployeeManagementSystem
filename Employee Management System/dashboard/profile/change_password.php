<?php
/**
 * Change Password
 * User page for updating account password
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = 'All fields are required';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        } else {
            // Verify current password
            $user = get_user_info($user_id);
            if ($user && verify_password($current_password, $user['password'])) {
                try {
                    $hashed_password = hash_password($new_password);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    
                    log_activity($user_id, 'Changed account password');
                    $success = 'Password updated successfully!';
                } catch (PDOException $e) {
                    $errors[] = 'Failed to update password';
                    error_log("Change password error: " . $e->getMessage());
                }
            } else {
                $errors[] = 'Current password is incorrect';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <?php if(is_employee()): ?>
    <link rel="stylesheet" href="../../assets/css/employee_dashboard.css">
    <?php else: ?>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <?php endif; ?>
</head>
<body class="dashboard-page <?php echo is_employee() ? 'employee-page' : 'admin-page'; ?>">
    
    <?php 
    if (is_employee()) {
        include '../includes/employee_header.php';
    } else {
        include '../includes/admin_header.php';
    }
    ?>
    
    <div class="dashboard-container">
        <?php 
        if (is_employee()) {
            include '../includes/employee_sidebar.php';
        } else {
            include '../includes/admin_sidebar.php';
        }
        ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>🔑 Change Password</h1>
                <p>Update your account security credentials</p>
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
            
            <div class="card card-small mx-auto">
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">Update Password</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
