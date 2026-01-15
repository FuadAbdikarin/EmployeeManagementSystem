<?php
/**
 * Admin Header Component
 * Header for administrators with notifications
 */

// Get unread notification count
$notification_count = get_unread_notification_count($_SESSION['user_id']);
$notifications = get_user_notifications($_SESSION['user_id'], 5);
?>
<!-- Admin Dashboard Header -->
<header class="dashboard-header admin-header">
    <div class="header-container">
        <div class="logo">
            <h2><?php echo SITE_NAME; ?></h2>
        </div>
        
        <div class="header-actions">
            <!-- Notifications -->
            <div class="notifications-dropdown">
                <button class="notification-btn" id="notificationBtn">
                    🔔
                    <?php if ($notification_count > 0): ?>
                        <span class="notification-badge"><?php echo $notification_count; ?></span>
                    <?php endif; ?>
                </button>
                
                <div class="notification-dropdown-content" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <?php if ($notification_count > 0): ?>
                            <span class="badge"><?php echo $notification_count; ?> unread</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notification-item">
                                <p>No notifications</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                    <div class="notif-icon notif-<?php echo strtolower($notif['type']); ?>">
                                        <?php 
                                        $icons = ['Info' => 'ℹ️', 'Success' => '✅', 'Warning' => '⚠️', 'Danger' => '🚨'];
                                        echo $icons[$notif['type']] ?? 'ℹ️';
                                        ?>
                                    </div>
                                    <div class="notif-content">
                                        <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <small><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-footer">
                        <a href="notifications/index.php">View All Notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="user-info">
                <img src="<?php echo '../uploads/profiles/' . ($_SESSION['profile_picture'] ?? 'default-avatar.png'); ?>" 
                     alt="Profile" class="user-avatar" onerror="this.src='../assets/images/default-avatar.png'">
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                    <span class="user-role badge badge-admin"><?php echo htmlspecialchars($_SESSION['user_type']); ?></span>
                </div>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline">Logout</a>
            </div>
        </div>
    </div>
</header>

<script>
// Notification dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    const notifBtn = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');
    
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                notifDropdown.classList.remove('show');
            }
        });
    }
});
</script>
