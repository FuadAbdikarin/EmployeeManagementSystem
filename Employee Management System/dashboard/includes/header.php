<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class=header-container">
        <div class="logo">
            <h2><?php echo SITE_NAME; ?></h2>
        </div>
        <div class="user-info">
            <img src="<?php echo '../uploads/profiles/' . ($_SESSION['profile_picture'] ?? 'default-avatar.png'); ?>" 
                 alt="Profile" class="user-avatar" onerror="this.src='../assets/images/default-avatar.png'">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
            <span class="user-role">(<?php echo htmlspecialchars($_SESSION['user_type']); ?>)</span>
            <a href="../auth/logout.php" class="btn btn-sm btn-outline">Logout</a>
        </div>
    </div>
</header>
