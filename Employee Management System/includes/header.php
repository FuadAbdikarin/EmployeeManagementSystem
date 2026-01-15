<!-- Header Component -->
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <h1><?php echo SITE_NAME; ?></h1>
        </div>
        <div class="header-actions">
            <?php if (is_logged_in()): ?>
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/index.php" class="btn btn-sm btn-primary">Dashboard</a>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/logout.php" class="btn btn-sm btn-outline">Logout</a>
            <?php else: ?>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/login.php" class="btn btn-sm btn-primary">Login</a>
                <a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/register.php" class="btn btn-sm btn-outline">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
