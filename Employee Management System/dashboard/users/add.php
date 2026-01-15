<?php
/**
 * Add User
 * Create a new system user
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role('Admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $gender = sanitize_input($_POST['gender'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = sanitize_input($_POST['phone'] ?? '');
        $user_type = sanitize_input($_POST['user_type'] ?? 'Employee');
        $user_status = sanitize_input($_POST['user_status'] ?? 'Active');
        
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($username)) $errors[] = 'Username is required';
        if (empty($email) || !validate_email($email)) $errors[] = 'Valid email is required';
        if (empty($password) || strlen($password) < PASSWORD_MIN_LENGTH) 
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
            
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error';
            }
        }
        
        if (empty($errors)) {
            try {
                $hashed_password = hash_password($password);
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, gender, username, email, password, phone, user_type, user_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$first_name, $last_name, $gender, $username, $email, $hashed_password, $phone, $user_type, $user_status]);
                
                log_activity($_SESSION['user_id'], 'Created new user', "Username: $username");
                
                redirect_with_message('manage.php', 'User created successfully!', 'success');
            } catch (PDOException $e) {
                $errors[] = 'Failed to create user. Please try again.';
                error_log("Add user error: " . $e->getMessage());
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
    <title>Add User - <?php echo SITE_NAME; ?></title>
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
                <h1>➕ Add New User</h1>
                <p>Create a new system account</p>
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
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="user_type">User Role *</label>
                                <select id="user_type" name="user_type" class="form-control" required>
                                    <option value="Employee" <?php echo ($_POST['user_type'] ?? '') === 'Employee' ? 'selected' : ''; ?>>Employee</option>
                                    <option value="Manager" <?php echo ($_POST['user_type'] ?? '') === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="HR" <?php echo ($_POST['user_type'] ?? '') === 'HR' ? 'selected' : ''; ?>>HR</option>
                                    <option value="Admin" <?php echo ($_POST['user_type'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="user_status">Status *</label>
                            <select id="user_status" name="user_status" class="form-control" required>
                                <option value="Active" <?php echo ($_POST['user_status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($_POST['user_status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Create User Account</button>
                            <a href="manage.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
