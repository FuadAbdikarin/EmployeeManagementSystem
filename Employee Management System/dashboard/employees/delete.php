<?php
/**
 * Delete Employee
 * Remove employee record from database
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role(['Admin', 'HR']);

$employee_id = intval($_GET['id'] ?? 0);

try {
    // Get employee info before deleting
    $stmt = $pdo->prepare("SELECT e.employee_id, e.user_id FROM employees e WHERE e.id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect_with_message('list.php', 'Employee not found.', 'danger');
    }
    
    // Delete employee (cascade will delete user)
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    
    log_activity($_SESSION['user_id'], 'Deleted employee', "Employee ID: {$employee['employee_id']}");
    
    redirect_with_message('list.php', 'Employee deleted successfully.', 'success');
    
} catch (PDOException $e) {
    error_log("Delete employee error: " . $e->getMessage());
    redirect_with_message('list.php', 'Failed to delete employee.', 'danger');
}
?>
