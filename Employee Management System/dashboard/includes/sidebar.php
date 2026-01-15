<!-- Dashboard Sidebar Navigation -->
<aside class="dashboard-sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
            </li>
            
            <?php if (has_role(['Admin', 'HR', 'Manager'])): ?>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/employees/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'employees') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span> Employees
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (has_role(['Admin'])): ?>
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/users/manage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span> User Management
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php">
                    <span class="nav-icon">🏠</span> Public Site
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <p><small>Session expires in: <span id="session-timer">5:00</span></small></p>
    </div>
</aside>
