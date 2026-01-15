<?php
/**
 * Attendance Management
 * Admin page for viewing and managing attendance
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

// Get date filter (default today)
$filter_date = $_GET['date'] ?? date('Y-m-d');

// Fetch attendance records for the selected date
try {
    $stmt = $pdo->prepare("
        SELECT a.*, e.employee_id, u.first_name, u.last_name, d.name as department_name
        FROM attendance a
        JOIN employees e ON a.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE a.date = ?
        ORDER BY a.check_in ASC
    ");
    $stmt->execute([$filter_date]);
    $attendance_records = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch attendance error: " . $e->getMessage());
    $attendance_records = [];
}

// Get attendance summary
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as present,
            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as absent,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
            COUNT(CASE WHEN status = 'On Leave' THEN 1 END) as on_leave
        FROM attendance
        WHERE date = ?
    ");
    $stmt->execute([$filter_date]);
    $summary = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Attendance summary error: " . $e->getMessage());
    $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'on_leave' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - <?php echo SITE_NAME; ?></title>
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
                <h1>Attendance Management</h1>
                <p>View and manage employee attendance records</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <!-- Date Filter -->
            <div class="card">
                <h2>Filter Attendance</h2>
                <form method="GET" class="form-inline">
                    <div class="form-group">
                        <label for="date">Select Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">View</button>
                    <a href="?date=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary">Today</a>
                </form>
            </div>
            
            <!-- Attendance Summary -->
            <div class="stats-grid">
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $summary['present']; ?></h3>
                        <p>Present</p>
                    </div>
                </div>
                
                <div class="stat-card stat-danger">
                    <div class="stat-icon">❌</div>
                    <div class="stat-details">
                        <h3><?php echo $summary['absent']; ?></h3>
                        <p>Absent</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-details">
                        <h3><?php echo $summary['late']; ?></h3>
                        <p>Late</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <h3><?php echo $summary['on_leave']; ?></h3>
                        <p>On Leave</p>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Records -->
            <div class="card">
                <h2>Attendance Records - <?php echo date('F d, Y', strtotime($filter_date)); ?></h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance_records)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No attendance records for this date</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['department_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '-'; ?></td>
                                        <td><?php echo $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '-'; ?></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'Present' => 'success',
                                                'Absent' => 'danger',
                                                'Late' => 'warning',
                                                'Half Day' => 'info',
                                                'On Leave' => 'info'
                                            ];
                                            ?>
                                            <span class="badge badge-<?php echo $status_class[$record['status']] ?? 'secondary'; ?>">
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
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
