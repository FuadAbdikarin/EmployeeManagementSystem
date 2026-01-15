<!-- Navigation Component -->
<nav class="main-nav">
    <ul class="nav-list">
        <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php">Home</a></li>
        <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php#about">About</a></li>
        <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php#features">Features</a></li>
        <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php#contact">Contact</a></li>
        <?php if (is_logged_in()): ?>
            <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/dashboard/index.php">Dashboard</a></li>
        <?php endif; ?>
    </ul>
</nav>
