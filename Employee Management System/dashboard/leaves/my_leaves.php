<?php
/**
 * My Leaves
 * Employee page for applying and viewing leave requests
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
$errors = [];
$success = '';

// Handle leave application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_leave') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $leave_type = sanitize_input($_POST['leave_type'] ?? '');
        $start_date = sanitize_input($_POST['start_date'] ?? '');
        $end_date = sanitize_input($_POST['end_date'] ?? '');
        $reason = sanitize_input($_POST['reason'] ?? '');
        
        if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
            $errors[] = 'All fields are required';
        } else {
            // Calculate days
            $ts_start = strtotime($start_date);
            $ts_end = strtotime($end_date);
            
            if ($ts_end < $ts_start) {
                $errors[] = 'End date cannot be before start date';
            } else {
                $days = floor(($ts_end - $ts_start) / (60 * 60 * 24)) + 1;
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                    $stmt->execute([$employee_id, $leave_type, $start_date, $end_date, $days, $reason]);
                    
                    log_activity($user_id, 'Applied for leave', "$leave_type: $start_date to $end_date");
                    $success = 'Leave request submitted successfully!';
                } catch (PDOException $e) {
                    $errors[] = 'Failed to submit leave request';
                    error_log("Leave application error: " . $e->getMessage());
                }
            }
        }
    }
}

// Fetch my leave history
try {
    $stmt = $pdo->prepare("
        SELECT * FROM leave_requests 
        WHERE employee_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$employee_id]);
    $leaves = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch my leaves error: " . $e->getMessage());
    $leaves = [];
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Requests - <?php echo SITE_NAME; ?></title>
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
                <h1>📝 My Leave Requests</h1>
                <p>Apply for leave and track your application status</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="leave-grid">
                <!-- Apply for Leave -->
                <div class="card">
                    <h2>Apply for Leave</h2>
                    <form method="POST" class="leave-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="apply_leave">
                        
                        <div class="form-group">
                            <label for="leave_type">Leave Type *</label>
                            <select id="leave_type" name="leave_type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Casual Leave">Casual Leave</option>
                                <option value="Annual Leave">Annual Leave</option>
                                <option value="Unpaid Leave">Unpaid Leave</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reason">Reason for Leave *</label>
                            <textarea id="reason" name="reason" class="form-control" rows="3" required placeholder="Describe why you need leave..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
                
                <!-- Leave History -->
                <div class="card">
                    <h2>Leave History</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Days</th>
                                    <th>Start Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leaves)): ?>
                                    <tr><td colspan="5" class="text-center">No leave record found</td></tr>
                                <?php else: ?>
                                    <?php foreach($leaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                        <td><?php echo $leave['days']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $leave['status'] == 'Approved' ? 'success' : 
                                                    ($leave['status'] == 'Pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo $leave['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline" onclick="alert('Reason: <?php echo addslashes($leave['reason']); ?>')">Details</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
