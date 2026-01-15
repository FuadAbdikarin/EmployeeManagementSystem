<?php
/**
 * Leave Requests Management
 * Admin page for approving/rejecting leave requests
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

$errors = [];

// Handle leave approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $leave_id = intval($_POST['leave_id'] ?? 0);
        $action = $_POST['action']; // 'approve' or 'reject'
        $review_notes = sanitize_input($_POST['review_notes'] ?? '');
        
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        try {
            $stmt = $pdo->prepare("UPDATE leave_requests 
                                   SET status = ?, reviewed_by = ?, review_date = NOW(), review_notes = ? 
                                   WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $review_notes, $leave_id]);
            
            // Get employee user_id for notification
            $stmt = $pdo->prepare("SELECT e.user_id FROM leave_requests lr 
                                   JOIN employees e ON lr.employee_id = e.id WHERE lr.id = ?");
            $stmt->execute([$leave_id]);
            $employee = $stmt->fetch();
            
            if ($employee) {
                create_notification(
                    $employee['user_id'],
                    'Leave Request ' . $status,
                    "Your leave request has been {$status}.",
                    $status === 'Approved' ? 'Success' : 'Warning',
                    '../dashboard/leaves/my_leaves.php'
                );
            }
            
            log_activity($_SESSION['user_id'], ucfirst($action) . ' leave request', "Leave ID: $leave_id");
            redirect_with_message('manage.php', "Leave request {$status} successfully!", 'success');
        } catch (PDOException $e) {
            $errors[] = 'Failed to process leave request';
            error_log("Leave approval error: " . $e->getMessage());
        }
    }
}

// Fetch leave requests
$status_filter = $_GET['status'] ?? 'Pending';
try {
    $stmt = $pdo->prepare("
        SELECT lr.*, e.employee_id, u.first_name, u.last_name,
               r.username as reviewer_name
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN users r ON lr.reviewed_by = r.id
        WHERE lr.status = ?
        ORDER BY lr.created_at DESC
    ");
    $stmt->execute([$status_filter]);
    $leave_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch leave requests error: " . $e->getMessage());
    $leave_requests = [];
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - <?php echo SITE_NAME; ?></title>
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
                <h1>📝 Leave Requests Management</h1>
                <p>Approve or reject employee leave requests</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Status Filter -->
            <div class="card">
                <div class="filter-tabs">
                    <a href="?status=Pending" class="filter-tab <?php echo $status_filter === 'Pending' ? 'active' : ''; ?>">
                        Pending
                    </a>
                    <a href="?status=Approved" class="filter-tab <?php echo $status_filter === 'Approved' ? 'active' : ''; ?>">
                        Approved
                    </a>
                    <a href="?status=Rejected" class="filter-tab <?php echo $status_filter === 'Rejected' ? 'active' : ''; ?>">
                        Rejected
                    </a>
                </div>
            </div>
            
            <!-- Leave Requests -->
            <div class="card">
                <h2><?php echo $status_filter; ?> Leave Requests</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <?php if ($status_filter === 'Pending'): ?>
                                    <th>Actions</th>
                                <?php else: ?>
                                    <th>Reviewed By</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leave_requests)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No <?php echo strtolower($status_filter); ?> leave requests</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leave_requests as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($leave['employee_id']); ?></small>
                                        </td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($leave['leave_type']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($leave['end_date'])); ?></td>
                                        <td><?php echo $leave['days']; ?> days</td>
                                        <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge badge-<?php echo $status_class[$leave['status']]; ?>">
                                                <?php echo $leave['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                                        <?php if ($status_filter === 'Pending'): ?>
                                            <td>
                                                <button onclick="reviewLeave(<?php echo $leave['id']; ?>, 'approve')" class="btn btn-sm btn-success">Approve</button>
                                                <button onclick="reviewLeave(<?php echo $leave['id']; ?>, 'reject')" class="btn btn-sm btn-danger">Reject</button>
                                            </td>
                                        <?php else: ?>
                                            <td><?php echo htmlspecialchars($leave['reviewer_name'] ?? 'N/A'); ?><br>
                                                <small><?php echo $leave['review_date'] ? date('M d, Y', strtotime($leave['review_date'])) : ''; ?></small>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeReviewModal()">&times;</span>
            <h2 id="modalTitle">Review Leave Request</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" id="review_leave_id" name="leave_id">
                <input type="hidden" id="review_action" name="action">
                
                <div class="form-group">
                    <label for="review_notes">Review Notes (Optional)</label>
                    <textarea id="review_notes" name="review_notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Confirm</button>
                    <button type="button" onclick="closeReviewModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
    <script>
        function reviewLeave(id, action) {
            document.getElementById('review_leave_id').value = id;
            document.getElementById('review_action').value = action;
            document.getElementById('modalTitle').textContent = action === 'approve' ? 'Approve Leave Request' : 'Reject Leave Request';
            document.getElementById('submitBtn').className = action === 'approve' ? 'btn btn-success' : 'btn btn-danger';
            document.getElementById('submitBtn').textContent = action === 'approve' ? 'Approve' : 'Reject';
            document.getElementById('reviewModal').style.display = 'block';
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeReviewModal();
            }
        }
    </script>
</body>
</html>
