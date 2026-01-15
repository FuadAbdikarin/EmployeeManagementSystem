<?php
/**
 * My Attendance
 * Employee page for viewing personal attendance records
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

$employee_id = $employee['id'];

// Get month/year filter
$filter_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Fetch attendance records
try {
    $stmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE employee_id = ? 
        AND MONTH(date) = ? AND YEAR(date) = ?
        ORDER BY date DESC
    ");
    $stmt->execute([$employee_id, $filter_month, $filter_year]);
    $attendance_records = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch my attendance error: " . $e->getMessage());
    $attendance_records = [];
}

// Summary stats
$present_count = 0;
$absent_count = 0;
$late_count = 0;
foreach ($attendance_records as $record) {
    if ($record['status'] == 'Present') $present_count++;
    if ($record['status'] == 'Absent') $absent_count++;
    if ($record['status'] == 'Late') $late_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - <?php echo SITE_NAME; ?></title>
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
                <h1>🕒 My Attendance</h1>
                <p>View your attendance records and statistics</p>
            </div>
            
            <div class="card">
                <h2>Filter Records</h2>
                <form method="GET" class="form-inline">
                    <div class="form-group">
                        <select name="month" class="form-control">
                            <?php for($m=1; $m<=12; $m++) echo "<option value='$m' ".($m==$filter_month?'selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="year" class="form-control">
                            <?php for($y=date('Y')-2; $y<=date('Y'); $y++) echo "<option value='$y' ".($y==$filter_year?'selected':'').">$y</option>"; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $present_count; ?></h3>
                        <p>Total Present</p>
                    </div>
                </div>
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-details">
                        <h3><?php echo $late_count; ?></h3>
                        <p>Times Late</p>
                    </div>
                </div>
                <div class="stat-card stat-danger">
                    <div class="stat-icon">❌</div>
                    <div class="stat-details">
                        <h3><?php echo $absent_count; ?></h3>
                        <p>Total Absent</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2>Attendance Logs</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance_records)): ?>
                                <tr><td colspan="5" class="text-center">No records found for this period</td></tr>
                            <?php else: ?>
                                <?php foreach($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                    <td><?php echo $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '-'; ?></td>
                                    <td><?php echo $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $record['status'] == 'Present' ? 'success' : 
                                                ($record['status'] == 'Late' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo $record['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
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
