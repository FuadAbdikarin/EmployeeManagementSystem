<?php
/**
 * System Reports
 * Admin page for generating and exporting reports
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

// Fetch some basic data for summary
try {
    // Total employees per department
    $stmt = $pdo->query("
        SELECT d.name, COUNT(e.id) as count 
        FROM departments d 
        LEFT JOIN employees e ON d.id = e.department_id 
        GROUP BY d.id
    ");
    $dept_stats = $stmt->fetchAll();
    
    // Recent leave requests summary
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM leave_requests 
        GROUP BY status
    ");
    $leave_stats = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Reports stats error: " . $e->getMessage());
    $dept_stats = $leave_stats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo SITE_NAME; ?></title>
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
                <h1>📂 System Reports</h1>
                <p>Generate and export system data in multiple formats</p>
            </div>
            
            <div class="reports-grid">
                <!-- Employee Reports -->
                <div class="card report-card">
                    <div class="report-icon">👥</div>
                    <h2>Employee Reports</h2>
                    <p>Export complete employee list with department and position details.</p>
                    <div class="report-actions">
                        <a href="export.php?type=employees&format=csv" class="btn btn-primary">CSV Export</a>
                        <a href="export.php?type=employees&format=pdf" class="btn btn-secondary">PDF Export</a>
                    </div>
                </div>
                
                <!-- Attendance Reports -->
                <div class="card report-card">
                    <div class="report-icon">🕒</div>
                    <h2>Attendance Reports</h2>
                    <p>Daily or monthly attendance summary for all employees.</p>
                    <form action="export.php" method="GET" class="report-form">
                        <input type="hidden" name="type" value="attendance">
                        <div class="form-group">
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="report-actions">
                            <button type="submit" name="format" value="csv" class="btn btn-primary">CSV</button>
                            <button type="submit" name="format" value="pdf" class="btn btn-secondary">PDF</button>
                        </div>
                    </form>
                </div>
                
                <!-- Payroll Reports -->
                <div class="card report-card">
                    <div class="report-icon">💰</div>
                    <h2>Payroll Reports</h2>
                    <p>Monthly payroll summary and disbursement reports.</p>
                    <form action="export.php" method="GET" class="report-form">
                        <input type="hidden" name="type" value="payroll">
                        <div class="form-row">
                            <select name="month" class="form-control">
                                <?php for($m=1; $m<=12; $m++) echo "<option value='$m' ".($m==date('m')?'selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
                            </select>
                            <select name="year" class="form-control">
                                <?php for($y=date('Y')-2; $y<=date('Y'); $y++) echo "<option value='$y' ".($y==date('Y')?'selected':'').">$y</option>"; ?>
                            </select>
                        </div>
                        <div class="report-actions">
                            <button type="submit" name="format" value="csv" class="btn btn-primary">CSV</button>
                            <button type="submit" name="format" value="pdf" class="btn btn-secondary">PDF</button>
                        </div>
                    </form>
                </div>
                
                <!-- Leave Reports -->
                <div class="card report-card">
                    <div class="report-icon">📝</div>
                    <h2>Leave Reports</h2>
                    <p>Leave request history and balances for all employees.</p>
                    <div class="report-actions">
                        <a href="export.php?type=leaves&format=csv" class="btn btn-primary">CSV Export</a>
                        <a href="export.php?type=leaves&format=pdf" class="btn btn-secondary">PDF Export</a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2>Quick Overview</h2>
                <div class="charts-container">
                    <div class="overview-section">
                        <h3>Employees by Department</h3>
                        <table class="data-table">
                            <thead>
                                <tr><th>Department</th><th>Count</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($dept_stats as $stat): ?>
                                <tr><td><?php echo htmlspecialchars($stat['name']); ?></td><td><?php echo $stat['count']; ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
