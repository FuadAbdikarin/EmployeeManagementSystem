<?php
/**
 * Update Admin Password to admin123
 * Run this script once to set the admin password
 */

require_once 'config/db_connect.php';
require_once 'includes/functions.php';

echo "<h2>Updating Admin Password</h2>";

try {
    // New password
    $new_password = 'admin123';
    $hashed_password = hash_password($new_password);
    
    // Update admin user
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashed_password]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Admin password successfully updated to: <strong>admin123</strong></p>";
        
        // Verify the update
        $stmt = $pdo->prepare("SELECT id, username, user_type, user_status FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<h3>Admin User Details:</h3>";
            echo "<ul>";
            echo "<li>ID: " . $admin['id'] . "</li>";
            echo "<li>Username: " . $admin['username'] . "</li>";
            echo "<li>User Type: " . $admin['user_type'] . "</li>";
            echo "<li>Status: " . $admin['user_status'] . "</li>";
            echo "</ul>";
            
            // Test password verification
            $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (verify_password('admin123', $user['password'])) {
                echo "<p style='color: green;'>✓ Password verification test: <strong>PASSED</strong></p>";
            } else {
                echo "<p style='color: red;'>✗ Password verification test: <strong>FAILED</strong></p>";
            }
        }
        
        // Show all users
        echo "<h3>All Users in System:</h3>";
        $stmt = $pdo->query("SELECT id, username, user_type, user_status FROM users ORDER BY id");
        $users = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>User Type</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['user_type'] . "</td>";
            echo "<td>" . $user['user_status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<hr>";
        echo "<p><strong>You can now login with:</strong></p>";
        echo "<ul>";
        echo "<li>Username: <code>admin</code></li>";
        echo "<li>Password: <code>admin123</code></li>";
        echo "</ul>";
        echo "<p><a href='auth/login.php'>Go to Login Page</a></p>";
        
    } else {
        echo "<p style='color: orange;'>⚠ No admin user found. Creating one...</p>";
        
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, user_type, user_status, first_name, last_name, email) VALUES (?, ?, 'Admin', 'Active', 'System', 'Administrator', 'admin@example.com')");
        $stmt->execute(['admin', $hashed_password]);
        
        echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file after running it for security reasons!</p>";
?>
