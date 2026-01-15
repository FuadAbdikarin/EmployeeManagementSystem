<?php
/**
 * Application Configuration
 * Employee Management System
 */

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ems_database');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty

// Site Configuration
define('SITE_NAME', 'Employee Management System');
define('SITE_URL', 'http://localhost:81/Employee Management System');
define('BASE_PATH', __DIR__ . '/../');

// Upload Configuration
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Session Configuration
define('SESSION_TIMEOUT', 300); // 5 minutes (300 seconds)
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 days

// Security Configuration
define('PASSWORD_MIN_LENGTH', 6);
define('PASSWORD_REQUIRE_SPECIAL', false);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination
define('ITEMS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('Africa/Nairobi');

?>
