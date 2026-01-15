<!-- Employee Sidebar Navigation -->
<aside class="dashboard-sidebar employee-sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/employee_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'employee_dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏠</span> Dashboard
                </a>
            </li>
            
            <!-- My Profile -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/profile/view.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span> My Profile
                </a>
            </li>
            
            <!-- My Attendance -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/attendance/my_attendance.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'my_attendance') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">🕒</span> My Attendance
                </a>
            </li>
            
            <!-- My Payslips -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/payroll/my_payslips.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'my_payslips') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">💰</span> My Payslips
                </a>
            </li>
            
            <!-- My Leave Requests -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/leaves/my_leaves.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'my_leaves') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span> Leave Requests
                </a>
            </li>
            
            <!-- Notifications -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/notifications/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'notifications') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">🔔</span> Notifications
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <!-- Change Password -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/profile/change_password.php">
                    <span class="nav-icon">🔑</span> Change Password
                </a>
            </li>
            
            <!-- Logout -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/logout.php" class="nav-logout">
                    <span class="nav-icon">🚪</span> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <p><small>Session expires in: <span id="session-timer">5:00</span></small></p>
    </div>
</aside>
