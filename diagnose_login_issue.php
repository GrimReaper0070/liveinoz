<?php
// Diagnostic script to check database connection and user data
require_once 'config.php';

echo "<h1>Login Issue Diagnosis</h1>";

// 1. Check database connection
echo "<h2>1. Database Connection Check</h2>";
try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // 2. Check if database exists
    echo "<h2>2. Database and Table Check</h2>";
    try {
        $stmt = $pdo->query("USE live_in_oz");
        echo "<p style='color: green;'>✓ Database 'live_in_oz' exists</p>";
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Users table exists</p>";
            
            // 3. Check user data
            echo "<h2>3. User Data Check</h2>";
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Total users in database: " . $result['count'] . "</p>";
            
            if ($result['count'] > 0) {
                echo "<p style='color: green;'>✓ Users found in database</p>";
                
                // Show first user (without password)
                $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, is_active FROM users LIMIT 1");
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Sample user data:</p>";
                echo "<ul>";
                foreach ($user as $key => $value) {
                    if ($key !== 'password') {
                        echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>⚠ No users found in database</p>";
                echo "<p>You need to register a user first or check if the database setup was completed.</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Users table does not exist</p>";
            echo "<p>Please run the database setup SQL commands from database_setup.sql</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>That XAMPP MySQL service is running</li>";
    echo "<li>Database configuration in config.php</li>";
    echo "<li>That the database 'live_in_oz' has been created</li>";
    echo "</ul>";
}

echo "<h2>4. Troubleshooting Steps</h2>";
echo "<ol>";
echo "<li>Ensure XAMPP is running (Apache and MySQL services)</li>";
echo "<li>Verify database configuration in <code>config.php</code></li>";
echo "<li>Run the SQL commands in <code>database_setup.sql</code> if not already done</li>";
echo "<li>Register a new user via <a href='signup.html'>signup.html</a> if no users exist</li>";
echo "<li>Check that you're using the correct email and password</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "</ol>";

echo "<p><a href='login.html'>← Back to Login</a> | <a href='signup.html'>Register New User</a></p>";
?>