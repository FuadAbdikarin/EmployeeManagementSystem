<?php
/**
 * Employee Profile
 * View and edit personal profile
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $errors[] = 'Required fields are missing';
        }
        
        // Handle profile picture upload
        $profile_picture = $_SESSION['profile_picture'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_profile_picture($_FILES['profile_pic']);
            if ($upload['success']) {
                $profile_picture = $upload['filename'];
                $_SESSION['profile_picture'] = $profile_picture;
            } else {
                $errors[] = $upload['message'];
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, profile_picture = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $phone, $profile_picture, $user_id]);
                
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                log_activity($user_id, 'Updated own profile');
                $success = 'Profile updated successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Failed to update profile. Email might be in use.';
                error_log("Update profile error: " . $e->getMessage());
            }
        }
    }
}

// Fetch user and employee details
$user = get_user_info($user_id);
$employee = get_employee_by_user_id($user_id);

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
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
                <h1>👤 My Profile</h1>
                <p>Manage your personal information and account settings</p>
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
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="card text-center">
                        <div class="profile-pic-container">
                            <img src="<?php echo '../../uploads/profiles/' . ($user['profile_picture'] ?? 'default-avatar.png'); ?>" 
                                 alt="Profile" class="profile-view-img" id="profilePreview"
                                 onerror="this.src='../../assets/images/default-avatar.png'">
                        </div>
                        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                        <span class="badge badge-info"><?php echo htmlspecialchars($user['user_type']); ?></span>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <hr>
                        <div class="profile-quick-stats">
                            <p><strong>Member Since:</strong> <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                            <?php if($employee): ?>
                            <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></p>
                            <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-main">
                    <div class="card">
                        <h2>Personal Information</h2>
                        <form method="POST" enctype="multipart/form-data" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label for="profile_pic">Update Profile Picture</label>
                                    <input type="file" id="profile_pic" name="profile_pic" class="form-control" accept="image/*" onchange="previewImage(this)">
                                    <small class="text-muted">Allowed types: JPG, PNG. Max size: 5MB</small>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                                <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                            </div>
                        </form>
                    </div>
                    
                    <?php if($employee): ?>
                    <div class="card">
                        <h2>Employment Details</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>Position</label>
                                <p><?php echo htmlspecialchars($employee['position']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Salary</label>
                                <p>$<?php echo number_format($employee['salary'], 2); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Hire Date</label>
                                <p><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Emergency Contact</label>
                                <p><?php echo htmlspecialchars($employee['emergency_contact'] ?: 'Not set'); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
