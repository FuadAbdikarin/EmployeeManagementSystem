<!-- Footer Component -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>About <?php echo SITE_NAME; ?></h3>
            <p>Professional employee management solution for modern businesses.</p>
        </div>
        
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/index.php">Home</a></li>
                <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/login.php">Login</a></li>
                <li><a href="<?php echo rtrim(SITE_URL, '/'); ?>/auth/register.php">Register</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Contact</h3>
            <p>Email: admin@ems.local</p>
            <p>Phone: +252 612222222</p>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </div>
</footer>
