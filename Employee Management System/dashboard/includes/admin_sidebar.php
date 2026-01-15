<!-- Admin Sidebar Navigation -->
<aside class="dashboard-sidebar admin-sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
            </li>
            
            <!-- User Management - Admin Only -->
            <?php if (is_admin()): ?>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/users/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span> Users
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Employee Management -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/employees/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'employees') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">👨‍💼</span> Employees
                </a>
            </li>
            
            <!-- Departments -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/departments/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'departments') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">🏢</span> Departments
                </a>
            </li>
            
            <!-- Attendance -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/attendance/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'attendance') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">🕒</span> Attendance
                </a>
            </li>
            
            <!-- Payroll -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/payroll/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'payroll') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">💰</span> Payroll
                </a>
            </li>
            
            <!-- Leave Requests -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/leaves/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'leaves') !== false && !strpos($_SERVER['PHP_SELF'], 'my_leaves') ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span> Leave Requests
                </a>
            </li>
            
            <!-- Reports -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/reports/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">📂</span> Reports
                </a>
            </li>
            
            <!-- System Settings - Admin Only -->
            <?php if (is_admin()): ?>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/settings/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">⚙️</span> System Settings
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-divider"></li>
            
            <!-- Change Password -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/profile/change_password.php">
                    <span class="nav-icon">🔑</span> Change Password
                </a>
            </li>
            
            <!-- Public Site -->
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php">
                    <span class="nav-icon">🏠</span> Public Site
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
