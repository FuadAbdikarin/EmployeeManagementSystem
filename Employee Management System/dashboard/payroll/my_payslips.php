<?php
/**
 * My Payslips
 * Employee page for viewing and downloading payslips
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$employee = get_employee_by_user_id($user_id);

if (!$employee) {
    die("Employee record not found.");
}

// Fetch all payslips for this employee
try {
    $stmt = $pdo->prepare("
        SELECT * FROM payroll 
        WHERE employee_id = ? 
        ORDER BY year DESC, month DESC
    ");
    $stmt->execute([$employee['id']]);
    $payslips = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch my payslips error: " . $e->getMessage());
    $payslips = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payslips - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/employee_dashboard.css">
</head>
<body class="dashboard-page employee-page">
    
    <?php include '../includes/employee_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/employee_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>💰 My Payslips</h1>
                <p>View and download your monthly salary statements</p>
            </div>
            
            <div class="card">
                <h2>Payslip History</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Basic Salary</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payslips)): ?>
                                <tr><td colspan="8" class="text-center">No payslips found</td></tr>
                            <?php else: ?>
                                <?php foreach($payslips as $payslip): ?>
                                <tr>
                                    <td><?php echo date('F', mktime(0, 0, 0, $payslip['month'], 1)); ?></td>
                                    <td><?php echo $payslip['year']; ?></td>
                                    <td>$<?php echo number_format($payslip['basic_salary'], 2); ?></td>
                                    <td>$<?php echo number_format($payslip['allowances'], 2); ?></td>
                                    <td>$<?php echo number_format($payslip['deductions'], 2); ?></td>
                                    <td><strong>$<?php echo number_format($payslip['net_salary'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $payslip['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                            <?php echo $payslip['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="generate_pdf.php?id=<?php echo $payslip['id']; ?>" class="btn btn-sm btn-primary">Download PDF</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
