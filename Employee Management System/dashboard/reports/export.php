<?php
/**
 * Data Export Handler
 * Handles CSV exports for various system modules
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if ($format === 'pdf') {
    // Simplified: For this demo, PDF format redirects to a printable view
    echo "<script>alert('PDF Generation requires server-side libraries. Displaying printable view instead.'); window.print();</script>";
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    try {
        switch ($type) {
            case 'employees':
                fputcsv($output, ['ID', 'Employee ID', 'Name', 'Email', 'Department', 'Position', 'Salary', 'Hire Date']);
                $stmt = $pdo->query("SELECT e.id, e.employee_id, u.first_name, u.last_name, u.email, d.name as dept, e.position, e.salary, e.hire_date 
                                     FROM employees e JOIN users u ON e.user_id = u.id LEFT JOIN departments d ON e.department_id = d.id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($output, [$row['id'], $row['employee_id'], $row['first_name'].' '.$row['last_name'], $row['email'], $row['dept'], $row['position'], $row['salary'], $row['hire_date']]);
                }
                break;
                
            case 'attendance':
                $date = $_GET['date'] ?? date('Y-m-d');
                fputcsv($output, ['Employee ID', 'Name', 'Date', 'Check In', 'Check Out', 'Status']);
                $stmt = $pdo->prepare("SELECT e.employee_id, u.first_name, u.last_name, a.date, a.check_in, a.check_out, a.status 
                                       FROM attendance a JOIN employees e ON a.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE a.date = ?");
                $stmt->execute([$date]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($output, [$row['employee_id'], $row['first_name'].' '.$row['last_name'], $row['date'], $row['check_in'], $row['check_out'], $row['status']]);
                }
                break;
                
            case 'payroll':
                $month = $_GET['month'] ?? date('m');
                $year = $_GET['year'] ?? date('Y');
                fputcsv($output, ['Employee ID', 'Name', 'Month', 'Year', 'Basic', 'Allowances', 'Deductions', 'Net', 'Status']);
                $stmt = $pdo->prepare("SELECT e.employee_id, u.first_name, u.last_name, p.month, p.year, p.basic_salary, p.allowances, p.deductions, p.net_salary, p.payment_status 
                                       FROM payroll p JOIN employees e ON p.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE p.month = ? AND p.year = ?");
                $stmt->execute([$month, $year]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($output, [$row['employee_id'], $row['first_name'].' '.$row['last_name'], $row['month'], $row['year'], $row['basic_salary'], $row['allowances'], $row['deductions'], $row['net_salary'], $row['payment_status']]);
                }
                break;
                
            case 'leaves':
                fputcsv($output, ['Employee ID', 'Name', 'Type', 'Start', 'End', 'Days', 'Status']);
                $stmt = $pdo->query("SELECT e.employee_id, u.first_name, u.last_name, lr.leave_type, lr.start_date, lr.end_date, lr.days, lr.status 
                                     FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id JOIN users u ON e.user_id = u.id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($output, [$row['employee_id'], $row['first_name'].' '.$row['last_name'], $row['leave_type'], $row['start_date'], $row['end_date'], $row['days'], $row['status']]);
                }
                break;
        }
    } catch (PDOException $e) {
        error_log("Export error: " . $e->getMessage());
    }
    
    fclose($output);
    exit();
}
