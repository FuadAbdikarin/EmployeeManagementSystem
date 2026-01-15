<?php
/**
 * Dashboard Index Redirector
 * Routes users to their specific dashboard based on role
 */

require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Redirect based on user role
if (is_employee()) {
    header('Location: employee_dashboard.php');
} else {
    // Admin, HR, Manager go to admin dashboard
    header('Location: admin_dashboard.php');
}
exit();
?>
