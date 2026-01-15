<?php
/**
 * Public Homepage
 * Main landing page with dynamic content from database
 */

require_once 'includes/session.php';
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Fetch site content from database
try {
    $stmt = $pdo->query("SELECT section_name, title, content FROM site_content");
    $site_content = [];
    while ($row = $stmt->fetch()) {
        $site_content[$row['section_name']] = $row;
    }
} catch (PDOException $e) {
    error_log("Content fetch error: " . $e->getMessage());
    $site_content = [];
}

// Helper function to get content
function get_content($section, $field = 'content') {
    global $site_content;
    return $site_content[$section][$field] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    
    <?php include 'includes/header.php'; ?>
    
    <?php include 'includes/nav.php'; ?>
    
    <div class="page-container">
        <!-- Left Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <h3>Quick Links</h3>
                <div class="sidebar-content">
                    <?php echo get_content('sidebar_welcome'); ?>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3>Latest Updates</h3>
                <div class="sidebar-content">
                    <?php echo get_content('sidebar_updates'); ?>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Hero Section -->
            <section class="hero-section">
                <h1><?php echo htmlspecialchars(get_content('hero', 'title')); ?></h1>
                <p class="hero-text"><?php echo htmlspecialchars(get_content('hero', 'content')); ?></p>
                <div class="hero-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="dashboard/index.php" class="btn btn-primary">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-primary">Login</a>
                        <a href="auth/register.php" class="btn btn-secondary">Register</a>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- About Section -->
            <section id="about" class="content-section">
                <h2><?php echo htmlspecialchars(get_content('about', 'title')); ?></h2>
                <p><?php echo htmlspecialchars(get_content('about', 'content')); ?></p>
            </section>
            
            <!-- Features Section -->
            <section id="features" class="content-section">
                <h2><?php echo htmlspecialchars(get_content('features', 'title')); ?></h2>
                <div class="features-grid">
                    <?php echo get_content('features', 'content'); ?>
                </div>
            </section>
            
            <!-- Contact Section -->
            <section id="contact" class="content-section">
                <h2><?php echo htmlspecialchars(get_content('contact', 'title')); ?></h2>
                <p><?php echo htmlspecialchars(get_content('contact', 'content')); ?></p>
            </section>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
