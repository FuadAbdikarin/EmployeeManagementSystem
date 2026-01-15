<?php
/**
 * Login Page
 * User authentication with sessions and cookies
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
$session_expired = isset($_GET['session_expired']);

// Check for "Remember Me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $cookie_value = $_COOKIE['remember_me'];
    
    try {
        // Verify cookie format: user_id:hash
        list($user_id, $cookie_hash) = explode(':', $cookie_value);
        
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_status = 'Active'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Verify cookie hash
            $expected_hash = hash('sha256', $user_id . $user['username'] . $user['password']);
            
            if (hash_equals($expected_hash, $cookie_hash)) {
                // Auto-login user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['LAST_ACTIVITY'] = time();
                
                log_activity($user['id'], 'Auto-login via Remember Me cookie');
                
                header('Location: ../dashboard/index.php');
                exit();
            }
        }
    } catch (Exception $e) {
        // Invalid cookie, delete it
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        // Validation
        if (empty($username)) $errors[] = 'Username is required';
        if (empty($password)) $errors[] = 'Password is required';
        
        // Authenticate user
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && verify_password($password, $user['password'])) {
                    // Check if user is active
                    if ($user['user_status'] !== 'Active') {
                        $errors[] = 'Your account is inactive. Please contact administrator.';
                    } else {
                        // Login successful
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['profile_picture'] = $user['profile_picture'];
                        $_SESSION['LAST_ACTIVITY'] = time();
                        
                        // Handle "Remember Me"
                        if ($remember_me) {
                            $cookie_hash = hash('sha256', $user['id'] . $user['username'] . $user['password']);
                            $cookie_value = $user['id'] . ':' . $cookie_hash;
                            setcookie('remember_me', $cookie_value, time() + REMEMBER_ME_DURATION, '/', '', false, true);
                        }
                        
                        // Log activity
                        log_activity($user['id'], 'User logged in');
                        
                        // Redirect based on user role
                        if ($user['user_type'] === 'Employee') {
                            header('Location: ../dashboard/employee_dashboard.php');
                        } else {
                            // Admin, HR, Manager go to admin dashboard
                            header('Location: ../dashboard/admin_dashboard.php');
                        }
                        exit();
                    }
                } else {
                    $errors[] = 'Invalid username or password';
                }
            } catch (PDOException $e) {
                $errors[] = 'Login error. Please try again.';
                error_log("Login error: " . $e->getMessage());
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
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Login to your account</p>
            </div>
            
            <?php if ($session_expired): ?>
                <div class="alert alert-warning">
                    Your session has expired. Please login again.
                </div>
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
            
            <?php display_flash_message(); ?>
            
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="remember_me" value="1">
                        Remember me (30 days)
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p><a href="forgot_password.php">Forgot Password?</a></p>
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                <p><a href="../index.php">Back to Home</a></p>
            </div>
            
            <!-- <div class="auth-info">
                <p><strong>Demo Credentials:</strong></p>
                <p>Username: <code>admin</code> | Password: <code>admin123</code></p>
            </div> -->
        </div>
    </div>
</body>
</html>
