<?php
/**
 * Admin Dashboard
 * Full control panel for administrators
 */

require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

// Fetch admin dashboard statistics
$stats = get_dashboard_stats_admin();

// Fetch recent activities
try {
    $stmt = $pdo->query("
        SELECT al.*, u.username, u.first_name, u.last_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 15
    ");
    $recent_activities = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin dashboard activities error: " . $e->getMessage());
    $recent_activities = [];
}

// Get pending leave requests
try {
    $stmt = $pdo->query("
        SELECT lr.*, e.employee_id, u.first_name, u.last_name
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        WHERE lr.status = 'Pending'
        ORDER BY lr.created_at DESC
        LIMIT 5
    ");
    $pending_leaves = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Pending leaves error: " . $e->getMessage());
    $pending_leaves = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body class="dashboard-page admin-page">
    
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>Admin Control Panel</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! You have full system access.</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <!-- System Statistics Cards -->
            <div class="stats-grid admin-stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                        <a href="users/manage.php" class="stat-link">Manage Users →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍💼</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['active_employees']; ?></h3>
                        <p>Active Employees</p>
                        <a href="employees/list.php" class="stat-link">View All →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-danger">
                    <div class="stat-icon">❌</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['inactive_users']; ?></h3>
                        <p>Inactive Users</p>
                        <a href="users/manage.php?filter=inactive" class="stat-link">Review →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_leaves']; ?></h3>
                        <p>Pending Leave Requests</p>
                        <a href="leaves/manage.php" class="stat-link">Review →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['payroll_due']; ?></h3>
                        <p>Payroll Pending</p>
                        <a href="payroll/manage.php" class="stat-link">Process →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">🕒</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['attendance_today']; ?></h3>
                        <p>Present Today</p>
                        <a href="attendance/manage.php" class="stat-link">View →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-primary">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_departments']; ?></h3>
                        <p>Departments</p>
                        <a href="departments/manage.php" class="stat-link">Manage →</a>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-details">
                        <h3><?php echo count($recent_activities); ?></h3>
                        <p>Recent Activities</p>
                        <a href="#activities" class="stat-link">View Below →</a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="employees/add.php" class="btn btn-primary">➕ Add Employee</a>
                    <a href="users/manage.php?action=create" class="btn btn-success">👤 Create User</a>
                    <a href="departments/manage.php?action=create" class="btn btn-info">🏢 New Department</a>
                    <a href="reports/index.php" class="btn btn-secondary">📂 Generate Report</a>
                    <a href="settings/index.php" class="btn btn-warning">⚙️ System Settings</a>
                </div>
            </div>
            
            <!-- Pending Leave Requests -->
            <?php if (!empty($pending_leaves)): ?>
            <div class="admin-section">
                <h2>Pending Leave Approvals</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_leaves as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($leave['leave_type']); ?></span></td>
                                    <td><?php echo date('M d', strtotime($leave['start_date'])) . ' - ' . date('M d, Y', strtotime($leave['end_date'])); ?></td>
                                    <td><?php echo $leave['days']; ?> days</td>
                                    <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50)) . (strlen($leave['reason']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <a href="leaves/manage.php?id=<?php echo $leave['id']; ?>" class="btn btn-sm btn-primary">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-footer">
                    <a href="leaves/manage.php" class="btn btn-secondary">View All Leave Requests →</a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Activity Log -->
            <div class="admin-section" id="activities">
                <h2>Recent System Activity</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_activities)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent activity</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></td>
                                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['details'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
