<?php
/**
 * User Registration Page
 * Sign up new users to the system
 */

require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../dashboard/index.php');
    exit();
}

$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate inputs
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $gender = sanitize_input($_POST['gender'] ?? '');
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = sanitize_input($_POST['phone'] ?? '');
        
        // Validation
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($gender)) $errors[] = 'Gender is required';
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!validate_email($email)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($phone) && !validate_phone($phone)) {
            $errors[] = 'Invalid phone number format';
        }
        
        // Check for duplicate username or email
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error. Please try again.';
                error_log("Registration check error: " . $e->getMessage());
            }
        }
        
        // Handle profile picture upload
        $profile_picture = 'default-avatar.png';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_profile_picture($_FILES['profile_picture']);
            if ($upload_result['success']) {
                $profile_picture = $upload_result['filename'];
            } else {
                $errors[] = $upload_result['message'];
            }
        }
        
        // Insert new user
        if (empty($errors)) {
            try {
                $hashed_password = hash_password($password);
                $user_type = 'Employee'; // Default role
                $user_status = 'Active';
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, gender, username, email, password, phone, profile_picture, user_type, user_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $first_name, $last_name, $gender, $username, $email,
                    $hashed_password, $phone, $profile_picture, $user_type, $user_status
                ]);
                
                $success = 'Registration successful! You can now login.';
                
                // Clear form data
                $_POST = [];
                
            } catch (PDOException $e) {
                $errors[] = 'Registration failed. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Sign up to get started</p>
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
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <p class="text-center">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </p>
            <?php else: ?>
            
            <form method="POST" action="" enctype="multipart/form-data" id="registerForm" class="auth-form">
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
                
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <small>At least 3 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small>At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                           placeholder="+1234567890">
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                    <small>Max size: 5MB (JPG, PNG, GIF)</small>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="../index.php">Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/validation.js"></script>
</body>
</html>
