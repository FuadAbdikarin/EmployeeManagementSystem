<?php
/**
 * Employee Dashboard
 * Restricted dashboard for regular employees
 */

require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Require login and employee role
require_login();

// Redirect non-employees to admin dashboard
if (!is_employee()) {
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch employee dashboard statistics
$stats = get_dashboard_stats_employee($_SESSION['user_id']);

// Get employee details
$employee = get_employee_by_user_id($_SESSION['user_id']);

if (!$employee) {
    die("Employee record not found. Please contact administrator.");
}

// Get recent payslips
try {
    $stmt = $pdo->prepare("
        SELECT * FROM payroll 
        WHERE employee_id = ? 
        ORDER BY year DESC, month DESC 
        LIMIT 3
    ");
    $stmt->execute([$employee['id']]);
    $recent_payslips = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Payslips error: " . $e->getMessage());
    $recent_payslips = [];
}

// Get recent leave requests
try {
    $stmt = $pdo->prepare("
        SELECT * FROM leave_requests 
        WHERE employee_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$employee['id']]);
    $leave_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Leave requests error: " . $e->getMessage());
    $leave_requests = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/employee_dashboard.css">
</head>
<body class="dashboard-page employee-page">
    
    <?php include 'includes/employee_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/employee_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>👤 My Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! Here's your employee summary.</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <!-- Employee Statistics Cards -->
            <div class="stats-grid employee-stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['leave_balance']; ?> days</h3>
                        <p>Leave Balance</p>
                        <small><?php echo $stats['leave_used']; ?> days used this year</small>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['attendance_rate']; ?>%</h3>
                        <p>Attendance Rate</p>
                        <small>This month</small>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_requests']; ?></h3>
                        <p>Pending Requests</p>
                        <small>Awaiting approval</small>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['unread_notifications']; ?></h3>
                        <p>Notifications</p>
                        <small>Unread messages</small>
                    </div>
                </div>
            </div>
            
            <!-- Employee Information Card -->
            <div class="employee-info-card">
                <h2>My Employment Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Employee ID</label>
                        <p><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Position</label>
                        <p><?php echo htmlspecialchars($employee['position']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Department</label>
                        <p><?php echo htmlspecialchars($employee['department_name'] ?? 'Not Assigned'); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Hire Date</label>
                        <p><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($employee['email']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <p><?php echo htmlspecialchars($employee['phone'] ?? 'Not Provided'); ?></p>
                    </div>
                </div>
                <div class="info-actions">
                    <a href="profile/view.php" class="btn btn-primary">View Full Profile</a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="leaves/my_leaves.php?action=apply" class="btn btn-primary">📝 Apply for Leave</a>
                    <a href="attendance/my_attendance.php" class="btn btn-info">🕒 View Attendance</a>
                    <a href="payroll/my_payslips.php" class="btn btn-success">💰 View Payslips</a>
                    <a href="profile/view.php" class="btn btn-secondary">👤 Edit Profile</a>
                </div>
            </div>
            
            <!-- Recent Payslips -->
            <div class="employee-section">
                <h2>Recent Payslips</h2>
                <?php if (empty($recent_payslips)): ?>
                    <p class="text-muted">No payslips available yet.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Basic Salary</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_payslips as $payslip): ?>
                                    <tr>
                                        <td><?php echo date('F Y', mktime(0, 0, 0, $payslip['month'], 1, $payslip['year'])); ?></td>
                                        <td>$<?php echo number_format($payslip['basic_salary'], 2); ?></td>
                                        <td><strong>$<?php echo number_format($payslip['net_salary'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $payslip['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                                <?php echo $payslip['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="payroll/my_payslips.php?id=<?php echo $payslip['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="section-footer">
                    <a href="payroll/my_payslips.php" class="btn btn-secondary">View All Payslips →</a>
                </div>
            </div>
            
            <!-- My Leave Requests -->
            <div class="employee-section">
                <h2>My Leave Requests</h2>
                <?php if (empty($leave_requests)): ?>
                    <p class="text-muted">No leave requests yet.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leave_requests as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                        <td><?php echo date('M d', strtotime($leave['start_date'])) . ' - ' . date('M d, Y', strtotime($leave['end_date'])); ?></td>
                                        <td><?php echo $leave['days']; ?> days</td>
                                        <td>
                                            <?php
                                            $badge_class = [
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class[$leave['status']]; ?>">
                                                <?php echo $leave['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="section-footer">
                    <a href="leaves/my_leaves.php" class="btn btn-secondary">View All My Leaves →</a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
