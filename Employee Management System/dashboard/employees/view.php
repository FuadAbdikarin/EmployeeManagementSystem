<?php
/**
 * View Employee
 * Display detailed employee information
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

require_login();
require_role(['Admin', 'HR', 'Manager', 'Employee']);

$employee_id = intval($_GET['id'] ?? 0);

// Fetch employee details
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.first_name, u.last_name, u.gender, u.username, u.email, u.phone, 
               u.user_type, u.user_status, u.profile_picture, u.created_at, d.name as department_name
        FROM employees e
        INNER JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE e.id = ?
    ");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect_with_message('list.php', 'Employee not found.', 'danger');
    }
} catch (PDOException $e) {
    redirect_with_message('list.php', 'Error loading employee.', 'danger');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial width=1.0">
    <title>View Employee - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>Employee Details</h1>
                <div class="header-actions">
                    <?php if (has_role(['Admin', 'HR'])): ?>
                    <a href="edit.php?id=<?php echo $employee_id; ?>" class="btn btn-warning">✏️ Edit</a>
                    <?php endif; ?>
                    <a href="list.php" class="btn btn-secondary">← Back to List</a>
                </div>
            </div>
            
            <div class="employee-profile">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="../../uploads/profiles/<?php echo htmlspecialchars($employee['profile_picture']); ?>" 
                             alt="Profile Picture"
                             onerror="this.src='../../assets/images/default-avatar.png'">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
                        <p class="employee-position"><?php echo htmlspecialchars($employee['position']); ?></p>
                        <p class="employee-id">Employee ID: <?php echo htmlspecialchars($employee['employee_id']); ?></p>
                        <span class="badge badge-<?php echo $employee['user_status'] === 'Active' ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($employee['user_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="detail-section">
                        <h3>Personal Information</h3>
                        <table class="info-table">
                            <tr>
                                <th>Full Name:</th>
                                <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Gender:</th>
                                <td><?php echo htmlspecialchars($employee['gender']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td><?php echo htmlspecialchars($employee['address'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Employment Information</h3>
                        <table class="info-table">
                            <tr>
                                <th>Employee ID:</th>
                                <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                            </tr>
                            <tr>
                                <th>Position:</th>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Salary:</th>
                                <td><?php echo $employee['salary'] ? '$' . number_format($employee['salary'], 2) : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Hire Date:</th>
                                <td><?php echo date('F d, Y', strtotime($employee['hire_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>User Type:</th>
                                <td><?php echo htmlspecialchars($employee['user_type']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Emergency Contact</h3>
                        <table class="info-table">
                            <tr>
                                <th>Contact Name:</th>
                                <td><?php echo htmlspecialchars($employee['emergency_contact'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Contact Phone:</th>
                                <td><?php echo htmlspecialchars($employee['emergency_phone'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Account Information</h3>
                        <table class="info-table">
                            <tr>
                                <th>Username:</th>
                                <td><?php echo htmlspecialchars($employee['username']); ?></td>
                            </tr>
                            <tr>
                                <th>Account Status:</th>
                                <td>
                                    <span class="badge badge-<?php echo $employee['user_status'] === 'Active' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($employee['user_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Account Created:</th>
                                <td><?php echo date('F d, Y', strtotime($employee['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
