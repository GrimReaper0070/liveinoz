<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Registration Debug Test</h1>";

    // Test 1: Check current user types
    echo "<h2>Current User Types in Database:</h2>";
    $stmt = $pdo->prepare("SELECT id, email, user_type FROM users ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Email</th><th>User Type</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['id']}</td><td>{$user['email']}</td><td>{$user['user_type']}</td></tr>";
    }
    echo "</table>";

    // Test 2: Check table structure
    echo "<h2>Users Table Structure:</h2>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";

    // Test 3: Update any NULL user_types to full_access
    echo "<h2>Fixing NULL user_types:</h2>";
    $stmt = $pdo->prepare("UPDATE users SET user_type = 'full_access' WHERE user_type IS NULL OR user_type = ''");
    $result = $stmt->execute();
    echo "<p>Updated " . $stmt->rowCount() . " users with NULL/empty user_type to 'full_access'</p>";

    echo "<h2>Test Complete</h2>";

} catch (Exception $e) {
    echo "<h1>Test Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
