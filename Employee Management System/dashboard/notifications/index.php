<?php
/**
 * Notifications Center
 * View and manage all user notifications
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        redirect_with_message('index.php', 'All notifications marked as read', 'success');
    } catch (PDOException $e) {
        error_log("Mark all read error: " . $e->getMessage());
    }
}

// Mark single as read
if (isset($_GET['read'])) {
    mark_notification_read(intval($_GET['read']));
    header("Location: index.php");
    exit();
}

// Fetch all notifications
try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch notifications error: " . $e->getMessage());
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <?php if(is_employee()): ?>
    <link rel="stylesheet" href="../../assets/css/employee_dashboard.css">
    <?php else: ?>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <?php endif; ?>
</head>
<body class="dashboard-page <?php echo is_employee() ? 'employee-page' : 'admin-page'; ?>">
    
    <?php 
    if (is_employee()) {
        include '../includes/employee_header.php';
    } else {
        include '../includes/admin_header.php';
    }
    ?>
    
    <div class="dashboard-container">
        <?php 
        if (is_employee()) {
            include '../includes/employee_sidebar.php';
        } else {
            include '../includes/admin_sidebar.php';
        }
        ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>🔔 Notification Center</h1>
                <p>Stay updated with system activities and requests</p>
                <div class="header-actions">
                    <a href="?mark_all_read=1" class="btn btn-sm btn-secondary">Mark All as Read</a>
                </div>
            </div>
            
            <?php display_flash_message(); ?>
            
            <div class="card">
                <div class="notification-full-list">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No notifications found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notifications as $notif): ?>
                        <div class="notification-row <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                            <div class="notif-type-icon notif-<?php echo strtolower($notif['type']); ?>">
                                <?php 
                                $icons = ['Info' => 'ℹ️', 'Success' => '✅', 'Warning' => '⚠️', 'Danger' => '🚨'];
                                echo $icons[$notif['type']] ?? 'ℹ️';
                                ?>
                            </div>
                            <div class="notif-body">
                                <div class="notif-header">
                                    <h3><?php echo htmlspecialchars($notif['title']); ?></h3>
                                    <span class="notif-time"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                <?php if($notif['link']): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn btn-sm btn-link">View Details →</a>
                                <?php endif; ?>
                            </div>
                            <div class="notif-actions">
                                <?php if(!$notif['is_read']): ?>
                                <a href="?read=<?php echo $notif['id']; ?>" class="btn btn-sm btn-outline" title="Mark as read">Mark Read</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
