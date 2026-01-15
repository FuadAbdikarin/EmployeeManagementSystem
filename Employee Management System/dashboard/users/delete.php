<?php
/**
 * Delete User
 * Remove user account from database
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role('Admin'); // Only admins can delete users

$user_id = intval($_GET['id'] ?? 0);

// Prevent self-deletion
if ($user_id === $_SESSION['user_id']) {
    redirect_with_message('manage.php', 'You cannot delete your own account.', 'danger');
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect_with_message('manage.php', 'User not found.', 'danger');
    }
    
    // Delete user (cascade will handle employees and activity_logs if set)
    // Based on schema, employees has CASCADE, activity_logs has SET NULL
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    log_activity($_SESSION['user_id'], 'Deleted user', "Username: {$user['username']}, ID: $user_id");
    
    redirect_with_message('manage.php', 'User deleted successfully.', 'success');
    
} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    redirect_with_message('manage.php', 'Failed to delete user. It may be linked to other records.', 'danger');
}
?>
