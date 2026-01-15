<?php
/**
 * User Management
 * Admin-only page to manage all system users
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role('Admin'); // Only admins can access

// Handle user status toggle
if (isset($_GET['toggle_status']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    
    try {
        // Get current status
        $stmt = $pdo->prepare("SELECT user_status, username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $new_status = $user['user_status'] === 'Active' ? 'Inactive' : 'Active';
            
            $stmt = $pdo->prepare("UPDATE users SET user_status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            
            log_activity($_SESSION['user_id'], 'Changed user status', "User: {$user['username']}, Status: $new_status");
            
            redirect_with_message('manage.php', "User status updated to $new_status.", 'success');
        }
    } catch (PDOException $e) {
        redirect_with_message('manage.php', 'Failed to update user status.', 'danger');
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM employees e WHERE e.user_id = u.id) as is_employee
        FROM users u
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
</head>
<body class="dashboard-page admin-page">
    
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>👥 User Management</h1>
                <p>Manage system users, roles, and account status</p>
                <div class="header-actions">
                    <a href="add.php" class="btn btn-primary">➕ Add New User</a>
                </div>
            </div>
            
            <?php display_flash_message(); ?>
            
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Employee</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php 
                                            $role_class = [
                                                'Admin' => 'danger',
                                                'HR' => 'warning',
                                                'Manager' => 'info',
                                                'Employee' => 'secondary'
                                            ];
                                            ?>
                                            <span class="badge badge-<?php echo $role_class[$user['user_type']] ?? 'secondary'; ?>">
                                                <?php echo htmlspecialchars($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['user_status'] === 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($user['user_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['is_employee'] > 0 ? '✅' : '❌'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" title="Edit User">✏️</a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="?toggle_status=1&user_id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm <?php echo $user['user_status'] === 'Active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>"
                                                       title="<?php echo $user['user_status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>"
                                                       onclick="return confirm('Are you sure you want to change this user status?')">
                                                        <?php echo $user['user_status'] === 'Active' ? '🔒' : '🔓'; ?>
                                                    </a>
                                                    <?php if(is_admin()): ?>
                                                    <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete User" onclick="return confirm('EXTREME CAUTION: This will delete the user account. Permanent action!')">🗑️</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card bg-light mt-4">
                <h3><i class="fas fa-info-circle"></i> Management Tips</h3>
                <ul class="mb-0">
                    <li><strong>Roles:</strong> Users see different dashboards based on their assigned role.</li>
                    <li><strong>Inactivation:</strong> Inactive users are immediately barred from system access.</li>
                    <li><strong>Employees:</strong> Most system features require a linked employee record for the user.</li>
                    <li><strong>Security:</strong> Modifying your own status or role is disabled for safety.</li>
                </ul>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
