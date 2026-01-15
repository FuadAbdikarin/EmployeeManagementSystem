<?php
/**
 * Payslip Generator (Printable View)
 * Generates a clean, professional printable payslip
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login
require_login();

$payslip_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

try {
    // Fetch payslip details
    $stmt = $pdo->prepare("
        SELECT p.*, e.employee_id, u.first_name, u.last_name, u.email, d.name as department_name, e.position
        FROM payroll p
        JOIN employees e ON p.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payslip_id]);
    $payslip = $stmt->fetch();

    if (!$payslip) {
        die("Payslip not found.");
    }

    // Security check: Employees can only view their own payslips
    if (is_employee() && $payslip['employee_id'] != get_employee_by_user_id($user_id)['id']) {
        die("Access denied. You can only view your own payslips.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$month_name = date('F', mktime(0, 0, 0, $payslip['month'], 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - <?php echo $month_name . ' ' . $payslip['year']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 0; padding: 40px; }
        .payslip-container { max-width: 800px; margin: auto; border: 1px solid #ddd; padding: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #4361ee; padding-bottom: 20px; margin-bottom: 30px; }
        .company-info h1 { margin: 0; color: #4361ee; }
        .payslip-title { text-align: right; }
        .payslip-title h2 { margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .detail-item { margin-bottom: 10px; }
        .detail-item label { font-weight: bold; color: #666; display: block; font-size: 0.8rem; text-transform: uppercase; }
        .earnings-deductions { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #666; font-size: 0.8rem; }
        .total-row { font-weight: bold; background: #fdfcfe; }
        .net-salary-box { background: #4361ee; color: #fff; padding: 20px; text-align: center; border-radius: 10px; margin-top: 40px; }
        .net-salary-box h3 { margin: 0; font-size: 1rem; opacity: 0.8; }
        .net-salary-box p { margin: 10px 0 0; font-size: 2rem; font-weight: bold; }
        .footer { margin-top: 50px; font-size: 0.8rem; color: #999; text-align: center; }
        @media print {
            body { padding: 0; }
            .payslip-container { border: none; box-shadow: none; }
            .no-print { display: none; }
        }
        .no-print { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #4361ee; color: #fff; border: none; border-radius: 5px;">Print Payslip</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer; background: #666; color: #fff; border: none; border-radius: 5px;">Back</button>
    </div>

    <div class="payslip-container">
        <div class="header">
            <div class="company-info">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Employee Management System</p>
            </div>
            <div class="payslip-title">
                <h2>Payslip</h2>
                <p>#PAY-<?php echo str_pad($payslip['id'], 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="employee-details">
                <div class="detail-item">
                    <label>Employee Name</label>
                    <span><?php echo htmlspecialchars($payslip['first_name'] . ' ' . $payslip['last_name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Employee ID</label>
                    <span><?php echo htmlspecialchars($payslip['employee_id']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Department</label>
                    <span><?php echo htmlspecialchars($payslip['department_name'] ?: 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <label>Designation</label>
                    <span><?php echo htmlspecialchars($payslip['position'] ?: 'N/A'); ?></span>
                </div>
            </div>
            <div class="payment-details">
                <div class="detail-item">
                    <label>Pay Period</label>
                    <span><?php echo $month_name . ' ' . $payslip['year']; ?></span>
                </div>
                <div class="detail-item">
                    <label>Payment Status</label>
                    <span><strong><?php echo htmlspecialchars($payslip['payment_status']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <label>Payment Date</label>
                    <span><?php echo $payslip['payment_date'] ? date('M d, Y', strtotime($payslip['payment_date'])) : 'Pending'; ?></span>
                </div>
            </div>
        </div>

        <div class="earnings-deductions">
            <div class="earnings">
                <h3>Earnings</h3>
                <table>
                    <thead><tr><th>Description</th><th>Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td>$<?php echo number_format($payslip['basic_salary'], 2); ?></td></tr>
                        <tr><td>Allowances</td><td>$<?php echo number_format($payslip['allowances'], 2); ?></td></tr>
                        <tr class="total-row"><td>Total Earnings</td><td>$<?php echo number_format($payslip['basic_salary'] + $payslip['allowances'], 2); ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="deductions">
                <h3>Deductions</h3>
                <table>
                    <thead><tr><th>Description</th><th>Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Tax</td><td>$<?php echo number_format($payslip['tax'], 2); ?></td></tr>
                        <tr><td>Other Deductions</td><td>$<?php echo number_format($payslip['deductions'], 2); ?></td></tr>
                        <tr class="total-row"><td>Total Deductions</td><td>$<?php echo number_format($payslip['tax'] + $payslip['deductions'], 2); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="net-salary-box">
            <h3>Net Salary</h3>
            <p>$<?php echo number_format($payslip['net_salary'], 2); ?></p>
        </div>

        <div class="footer">
            <p>This is a computer-generated payslip and does not require a physical signature.</p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
