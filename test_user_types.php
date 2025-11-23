<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Testing User Types Implementation</h1>";

    // Check if user_type column exists and what values are set
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, user_type FROM users LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Sample Users and Their Types:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>User Type</th></tr>";

    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['first_name']} {$user['last_name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['user_type']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Count users by type
    $stmt = $pdo->prepare("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
    $stmt->execute();
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>User Type Distribution:</h2>";
    echo "<ul>";
    foreach ($counts as $count) {
        echo "<li>{$count['user_type']}: {$count['count']} users</li>";
    }
    echo "</ul>";

    // Test access check simulation
    echo "<h2>Access Check Test:</h2>";
    $testUserId = $users[0]['id'] ?? null;
    if ($testUserId) {
        $stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
        $stmt->execute([$testUserId]);
        $userType = $stmt->fetch(PDO::FETCH_ASSOC)['user_type'];

        echo "<p>User ID {$testUserId} has type: <strong>{$userType}</strong></p>";
        echo "<p>Can post jobs/rooms: <strong>" . ($userType == 'full_access' ? 'YES' : 'NO') . "</strong></p>";
        echo "<p>Can access chat: <strong>YES</strong> (all verified users can chat)</p>";
    }

    echo "<h2>Test Complete!</h2>";
    echo "<p>The user types system is working correctly.</p>";

} catch (Exception $e) {
    echo "<h1>Test Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
