<?php
/**
 * Logout Page
 * Destroy session and cookies
 */

require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'User logged out');
}

// Delete Remember Me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
    unset($_COOKIE['remember_me']);
}

// Destroy session
session_unset();
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['flash_message'] = 'You have been logged out successfully.';
$_SESSION['flash_type'] = 'success';

// Redirect to login
header('Location: login.php');
exit();
?>
