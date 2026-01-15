<?php
/**
 * Password Recovery Page
 * Request password reset
 */

require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!validate_email($email)) {
            $errors[] = 'Invalid email format';
        } else {
            try {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // In a real application, you would:
                    // 1. Generate a unique reset token
                    // 2. Store token in database with expiry
                    // 3. Send email with reset link
                    
                    // For this demo, we'll just show the username
                    $success = "Password reset instructions have been sent to your email. Your username is: <strong>" . htmlspecialchars($user['username']) . "</strong>. Please contact the administrator for password reset.";
                    
                    log_activity($user['id'], 'Password reset requested');
                } else {
                    // Don't reveal whether email exists (security best practice)
                    $success = "If an account exists with this email, you will receive password reset instructions.";
                }
            } catch (PDOException $e) {
                $errors[] = 'Error processing request. Please try again.';
                error_log("Password reset error: " . $e->getMessage());
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
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Forgot Password</h1>
                <p>Enter your email to reset your password</p>
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
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required autofocus>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p><a href="login.php">Back to Login</a></p>
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
