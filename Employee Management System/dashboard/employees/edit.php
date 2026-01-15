<?php
/**
 * Edit Employee
 * Update employee record
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role(['Admin', 'HR']);

$employee_id = intval($_GET['id'] ?? 0);
$errors = [];

// Fetch employee data
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.first_name, u.last_name, u.gender, u.username, u.email, u.phone, u.user_type, u.profile_picture
        FROM employees e
        INNER JOIN users u ON e.user_id = u.id
        WHERE e.id = ?
    ");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect_with_message('list.php', 'Employee not found.', 'danger');
    }
} catch (PDOException $e) {
    redirect_with_message('list.php', 'Error loading employee.', 'danger');
}

// Fetch departments
try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $gender = sanitize_input($_POST['gender'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $position = sanitize_input($_POST['position'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $salary = floatval($_POST['salary'] ?? 0);
        $hire_date = sanitize_input($_POST['hire_date'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');
        $emergency_phone = sanitize_input($_POST['emergency_phone'] ?? '');
        $user_type = sanitize_input($_POST['user_type'] ?? 'Employee');
        
        // Validation
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($email) || !validate_email($email)) $errors[] = 'Valid email is required';
        if (empty($position)) $errors[] = 'Position is required';
        if (empty($hire_date)) $errors[] = 'Hire date is required';
        
        // Check duplicate email (excluding current user)
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $employee['user_id']]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email already exists';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error';
            }
        }
        
        // Update employee
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                
                // Update user
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, gender = ?, email = ?, phone = ?, user_type = ?
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $gender, $email, $phone, $user_type, $employee['user_id']]);
                
                // Update employee
                $stmt = $pdo->prepare("
                    UPDATE employees 
                    SET department_id = ?, position = ?, salary = ?, hire_date = ?, address = ?, 
                        emergency_contact = ?, emergency_phone = ?
                    WHERE id = ?
                ");
                $stmt->execute([$department_id ?: null, $position, $salary, $hire_date, $address, 
                               $emergency_contact, $emergency_phone, $employee_id]);
                
                // Handle profile picture upload
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = upload_profile_picture($_FILES['profile_picture']);
                    if ($upload_result['success']) {
                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                        $stmt->execute([$upload_result['filename'], $employee['user_id']]);
                    }
                }
                
                $pdo->commit();
                
                log_activity($_SESSION['user_id'], 'Updated employee', "Employee ID: {$employee['employee_id']}");
                
                redirect_with_message('list.php', 'Employee updated successfully!', 'success');
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to update employee.';
                error_log("Update employee error: " . $e->getMessage());
            }
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST = $employee;
}

$csrf_token = generate_csrf_token();
?><?php echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - ' . SITE_NAME . '</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="dashboard-page">'; ?>
    
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>Edit Employee</h1>
                <p>Update employee information</p>
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
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data" class="employee-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <h3>Personal Information</h3>
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
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <h3>Account Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username (Read-only)</label>
                            <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($employee['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="user_type">User Type *</label>
                            <select id="user_type" name="user_type" class="form-control" required>
                                <option value="Employee" <?php echo ($_POST['user_type'] ?? '') === 'Employee' ? 'selected' : ''; ?>>Employee</option>
                                <option value="Manager" <?php echo ($_POST['user_type'] ?? '') === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="HR" <?php echo ($_POST['user_type'] ?? '') === 'HR' ? 'selected' : ''; ?>>HR</option>
                                <?php if (has_role('Admin')): ?>
                                <option value="Admin" <?php echo ($_POST['user_type'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_picture">Update Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                        <small>Current: <?php echo htmlspecialchars($employee['profile_picture']); ?></small>
                    </div>
                    
                    <h3>Employment Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">Position *</label>
                            <input type="text" id="position" name="position" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" 
                                            <?php echo ($employee['department_id'] ?? 0) == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary">Salary</label>
                            <input type="number" id="salary" name="salary" class="form-control" step="0.01" 
                                   value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="hire_date">Hire Date *</label>
                            <input type="date" id="hire_date" name="hire_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['hire_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <h3>Emergency Contact</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_contact">Contact Name</label>
                            <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['emergency_contact'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="emergency_phone">Contact Phone</label>
                            <input type="tel" id="emergency_phone" name="emergency_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['emergency_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/validation.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
