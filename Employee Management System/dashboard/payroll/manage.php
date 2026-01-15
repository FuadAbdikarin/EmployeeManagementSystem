<?php
/**
 * Payroll Management
 * Admin page for managing employee payroll
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

// Get month/year filter (default current month)
$filter_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Fetch payroll records
try {
    $stmt = $pdo->prepare("
        SELECT p.*, e.employee_id, u.first_name, u.last_name, d.name as department_name
        FROM payroll p
        JOIN employees e ON p.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE p.month = ? AND p.year = ?
        ORDER BY u.last_name, u.first_name
    ");
    $stmt->execute([$filter_month, $filter_year]);
    $payroll_records = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch payroll error: " . $e->getMessage());
    $payroll_records = [];
}

// Calculate summary
$total_basic = 0;
$total_net = 0;
$total_paid = 0;
$total_pending = 0;

foreach ($payroll_records as $record) {
    $total_basic += $record['basic_salary'];
    $total_net += $record['net_salary'];
    if ($record['payment_status'] == 'Paid') {
        $total_paid += $record['net_salary'];
    } else {
        $total_pending += $record['net_salary'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - <?php echo SITE_NAME; ?></title>
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
                <h1>💰 Payroll Management</h1>
                <p>Manage employee salaries and payments</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <!-- Month/Year Filter -->
            <div class="card">
                <h2>Filter Payroll</h2>
                <form method="GET" class="form-inline">
                    <div class="form-group">
                        <label for="month">Month:</label>
                        <select id="month" name="month" class="form-control">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m == $filter_month ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select id="year" name="year" class="form-control">
                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $filter_year ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">View</button>
                </form>
            </div>
            
            <!-- Payroll Summary -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">💵</div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($total_basic, 2); ?></h3>
                        <p>Total Basic Salary</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($total_paid, 2); ?></h3>
                        <p>Total Paid</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($total_pending, 2); ?></h3>
                        <p>Total Pending</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($total_net, 2); ?></h3>
                        <p>Total Net Salary</p>
                    </div>
                </div>
            </div>
            
            <!-- Payroll Records -->
            <div class="card">
                <h2>Payroll Records - <?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?></h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Tax</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payroll_records)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No payroll records for this period</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payroll_records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['department_name'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($record['basic_salary'], 2); ?></td>
                                        <td>$<?php echo number_format($record['allowances'], 2); ?></td>
                                        <td>$<?php echo number_format($record['deductions'], 2); ?></td>
                                        <td>$<?php echo number_format($record['tax'], 2); ?></td>
                                        <td><strong>$<?php echo number_format($record['net_salary'], 2); ?></strong></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'Paid' => 'success',
                                                'Pending' => 'warning',
                                                'Failed' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge badge-<?php echo $status_class[$record['payment_status']] ?? 'secondary'; ?>">
                                                <?php echo $record['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $record['payment_date'] ? date('M d, Y', strtotime($record['payment_date'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
