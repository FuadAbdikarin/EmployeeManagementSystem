<?php
/**
 * Edit User
 * Update existing system user
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role('Admin');

$user_id = intval($_GET['id'] ?? 0);
$errors = [];

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect_with_message('manage.php', 'User not found.', 'danger');
    }
} catch (PDOException $e) {
    redirect_with_message('manage.php', 'Error loading user data.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $gender = sanitize_input($_POST['gender'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $user_type = sanitize_input($_POST['user_type'] ?? 'Employee');
        $user_status = sanitize_input($_POST['user_status'] ?? 'Active');
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($email) || !validate_email($email)) $errors[] = 'Valid email is required';
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email already exists';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error';
            }
        }
        
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                
                // Update basic info
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, gender = ?, email = ?, phone = ?, user_type = ?, user_status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $gender, $email, $phone, $user_type, $user_status, $user_id]);
                
                // Update password if provided
                if (!empty($new_password)) {
                    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                        $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
                        $pdo->rollBack();
                    } else {
                        $hashed_password = hash_password($new_password);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                    }
                }
                
                if (empty($errors)) {
                    $pdo->commit();
                    log_activity($_SESSION['user_id'], 'Updated user', "Username: {$user['username']}");
                    redirect_with_message('manage.php', 'User updated successfully!', 'success');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $errors[] = 'Failed to update user. Please try again.';
                error_log("Edit user error: " . $e->getMessage());
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
    <title>Edit User - <?php echo SITE_NAME; ?></title>
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
                <h1>✏️ Edit User</h1>
                <p>Update account details for <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                <div class="header-actions">
                    <a href="manage.php" class="btn btn-outline">Back to List</a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="form-container">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? $user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? $user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="Male" <?php echo ($user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username (Read-only)</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_type">User Role *</label>
                                <select id="user_type" name="user_type" class="form-control" required>
                                    <option value="Employee" <?php echo ($user['user_type'] === 'Employee') ? 'selected' : ''; ?>>Employee</option>
                                    <option value="Manager" <?php echo ($user['user_type'] === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                    <option value="HR" <?php echo ($user['user_type'] === 'HR') ? 'selected' : ''; ?>>HR</option>
                                    <option value="Admin" <?php echo ($user['user_type'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="user_status">Status *</label>
                                <select id="user_status" name="user_status" class="form-control" required>
                                    <option value="Active" <?php echo ($user['user_status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($user['user_status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="new_password">New Password (leave blank to keep current)</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Update User Details</button>
                            <a href="manage.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
