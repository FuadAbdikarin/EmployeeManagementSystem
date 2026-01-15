<?php
/**
 * Utility Functions
 * Reusable functions for the application
 */

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hash password using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (basic validation)
 */
function validate_phone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if length is between 10-15 digits
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Upload profile picture
 */
function upload_profile_picture($file) {
    global $pdo;
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed (5MB)'];
    }
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($file_ext, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, JPEG, PNG, GIF'];
    }
    
    // Generate unique filename
    $new_filename = uniqid('profile_', true) . '.' . $file_ext;
    $upload_path = PROFILE_UPLOAD_PATH . $new_filename;
    
    // Create directory if it doesn't exist
    if (!is_dir(PROFILE_UPLOAD_PATH)) {
        mkdir(PROFILE_UPLOAD_PATH, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Failed to save file'];
    }
}

/**
 * Log user activity
 */
function log_activity($user_id, $action, $details = '') {
    global $pdo;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip_address]);
        return true;
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check session timeout
 */
function check_session_timeout() {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
        
        if ($elapsed_time > SESSION_TIMEOUT) {
            // Session expired
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();
    return true;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login (redirect if not logged in)
 */
function require_login($redirect_to = '../auth/login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit();
    }
    
    // Check session timeout
    if (!check_session_timeout()) {
        header("Location: $redirect_to?session_expired=1");
        exit();
    }
}

/**
 * Check if user has specific role
 */
function has_role($allowed_roles = []) {
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    return isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], $allowed_roles);
}

/**
 * Require specific role
 */
function require_role($allowed_roles, $redirect_to = '../dashboard/index.php') {
    if (!has_role($allowed_roles)) {
        header("Location: $redirect_to?access_denied=1");
        exit();
    }
}

/**
 * Get user info from database
 */
function get_user_info($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get user info error: " . $e->getMessage());
        return null;
    }
}

/**
 * Generate unique employee ID
 */
function generate_employee_id() {
    global $pdo;
    
    try {
        // Get last employee ID
        $stmt = $pdo->query("SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1");
        $last_employee = $stmt->fetch();
        
        if ($last_employee) {
            // Extract number from last ID (e.g., EMP001 -> 001)
            $last_number = intval(substr($last_employee['employee_id'], 3));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        // Format as EMP001, EMP002, etc.
        return 'EMP' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        error_log("Generate employee ID error: " . $e->getMessage());
        return 'EMP001';
    }
}

/**
 * Display alert message
 */
function show_alert($type, $message) {
    $alert_class = 'alert-' . $type; // alert-success, alert-danger, alert-warning, alert-info
    echo "<div class='alert $alert_class'>$message</div>";
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Display flash message
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        show_alert($type, $_SESSION['flash_message']);
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Get comprehensive dashboard statistics for Admin
 */
function get_dashboard_stats_admin() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Active employees
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees e 
                             JOIN users u ON e.user_id = u.id 
                             WHERE u.user_status = 'Active'");
        $stats['active_employees'] = $stmt->fetch()['count'];
        
        // Inactive users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_status = 'Inactive'");
        $stats['inactive_users'] = $stmt->fetch()['count'];
        
        // Pending leave requests
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Pending'");
        $stats['pending_leaves'] = $stmt->fetch()['count'];
        
        // Payroll due (pending payments this month)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM payroll 
                             WHERE payment_status = 'Pending' 
                             AND month = MONTH(CURDATE()) 
                             AND year = YEAR(CURDATE())");
        $stats['payroll_due'] = $stmt->fetch()['count'];
        
        // Today's attendance
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance 
                             WHERE date = CURDATE() AND status = 'Present'");
        $stats['attendance_today'] = $stmt->fetch()['count'];
        
        // Total departments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
        $stats['total_departments'] = $stmt->fetch()['count'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_employees' => 0,
            'inactive_users' => 0,
            'pending_leaves' => 0,
            'payroll_due' => 0,
            'attendance_today' => 0,
            'total_departments' => 0
        ];
    }
}

/**
 * Get dashboard statistics for Employee
 */
function get_dashboard_stats_employee($user_id) {
    global $pdo;
    
    try {
        $stats = [];
        
        // Get employee record
        $stmt = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            return ['error' => 'Employee record not found'];
        }
        
        $employee_id = $employee['id'];
        
        // Leave balance (simplified - total available leaves)
        $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'leave_annual_days'");
        $annual_leaves = $stmt->fetch()['setting_value'] ?? 20;
        
        // Used leaves this year
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(days), 0) as used FROM leave_requests 
                               WHERE employee_id = ? 
                               AND status = 'Approved' 
                               AND YEAR(start_date) = YEAR(CURDATE())");
        $stmt->execute([$employee_id]);
        $used_leaves = $stmt->fetch()['used'];
        
        $stats['leave_balance'] = max(0, $annual_leaves - $used_leaves);
        $stats['leave_used'] = $used_leaves;
        
        // Attendance rate this month
        $stmt = $pdo->prepare("SELECT COUNT(*) as present FROM attendance 
                               WHERE employee_id = ? 
                               AND MONTH(date) = MONTH(CURDATE()) 
                               AND YEAR(date) = YEAR(CURDATE())
                               AND status = 'Present'");
        $stmt->execute([$employee_id]);
        $present_days = $stmt->fetch()['present'];
        
        $working_days = date('d'); // Simplified - actual days in current month so far
        $stats['attendance_rate'] = $working_days > 0 ? round(($present_days / $working_days) * 100, 1) : 0;
        
        // Pending leave requests
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM leave_requests 
                               WHERE employee_id = ? AND status = 'Pending'");
        $stmt->execute([$employee_id]);
        $stats['pending_requests'] = $stmt->fetch()['count'];
        
        // Unread notifications
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications 
                               WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $stats['unread_notifications'] = $stmt->fetch()['count'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Employee dashboard stats error: " . $e->getMessage());
        return [
            'leave_balance' => 0,
            'leave_used' => 0,
            'attendance_rate' => 0,
            'pending_requests' => 0,
            'unread_notifications' => 0
        ];
    }
}

/**
 * Create notification for user
 */
function create_notification($user_id, $title, $message, $type = 'Info', $link = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $title, $message, $link]);
        return true;
    } catch (PDOException $e) {
        error_log("Create notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user notifications
 */
function get_user_notifications($user_id, $limit = 10, $unread_only = false) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get notifications error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get unread notification count
 */
function get_unread_notification_count($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications 
                               WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log("Get unread count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mark notification as read
 */
function mark_notification_read($notification_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notification_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Mark notification read error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get employee record by user ID
 */
function get_employee_by_user_id($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT e.*, d.name as department_name, 
                               u.first_name, u.last_name, u.email, u.phone, u.profile_picture
                               FROM employees e
                               LEFT JOIN departments d ON e.department_id = d.id
                               JOIN users u ON e.user_id = u.id
                               WHERE e.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get employee error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';
}

/**
 * Check if user is employee
 */
function is_employee() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Employee';
}

/**
 * Require admin access
 */
function require_admin($redirect_to = '../dashboard/index.php') {
    if (!is_admin()) {
        header("Location: $redirect_to?access_denied=1");
        exit();
    }
}

/**
 * Require admin or HR access
 */
function require_admin_or_hr($redirect_to = '../dashboard/index.php') {
    if (!has_role(['Admin', 'HR'])) {
        header("Location: $redirect_to?access_denied=1");
        exit();
    }
}

/**
 * Prevent employee access (admin/HR/Manager only)
 */
function require_not_employee($redirect_to = '../dashboard/employee_dashboard.php') {
    if (is_employee()) {
        header("Location: $redirect_to?access_denied=1");
        exit();
    }
}

?>
