<?php
require_once 'config.php';

echo "<h1>Database Connection Test</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    echo "<p>Connected to database: " . DB_NAME . " on " . DB_HOST . "</p>";

    // Test a simple query
    $stmt = $pdo->query("SELECT 1");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Query test successful!</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please ensure MySQL is running in XAMPP Control Panel.</p>";
}
?>
