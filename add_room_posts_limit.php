<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Adding Room Posts Limit to Database...</h1>";

    // Add room_posts_count column to users table
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN room_posts_count INT DEFAULT 0
    ");

    echo "<p>✅ Successfully added room_posts_count column to users table.</p>";
    echo "<p>📝 New users will start with 0 posts (can post 3 free).</p>";
    echo "<p>💰 Users with 3+ posts will need to pay for additional posts.</p>";

    // Show current user counts for verification
    echo "<h2>Current User Room Post Counts:</h2>";
    $stmt = $pdo->query("SELECT email, room_posts_count FROM users ORDER BY room_posts_count DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>Email</th><th>Room Posts Count</th><th>Status</th></tr>";
        foreach ($users as $user) {
            $status = $user['room_posts_count'] < 3 ? 'Free posts available' : 'Payment required for next post';
            echo "<tr>";
            echo "<td>{$user['email']}</td>";
            echo "<td style='text-align: center;'>{$user['room_posts_count']}</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<h2>✅ Database Migration Complete!</h2>";
    echo "<p>Room posting limits are now active.</p>";
    echo "<p>• Users can post 3 rooms for free</p>";
    echo "<p>• 4th and subsequent posts require $5 payment via Stripe</p>";

} catch (Exception $e) {
    echo "<h1>❌ Database Migration Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Note: This error might occur if the column already exists.</p>";
}
?>
