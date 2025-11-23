<?php
require 'config.php';

try {
    $pdo = getDBConnection();

    // First ensure the column exists
    try {
        $alterSql = "ALTER TABLE users ADD COLUMN available_posts INT NOT NULL DEFAULT 3";
        $pdo->exec($alterSql);
        echo "<p style='color: #4ecdc4;'>✅ Database column added successfully!</p>";
    } catch (Exception $alterError) {
        // Column might already exist, that's ok
        echo "<p style='color: #ffa500;'>⚠️ Column might already exist: " . $alterError->getMessage() . "</p>";
    }

    // Find an active user
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h1>No users found</h1>";
        echo "<p>Please create a user account first.</p>";
        exit;
    }

    $userId = $user['id'];
    $userEmail = $user['email'];

    // Set user's credit system: 0 available posts to trigger payment requirement
    // (they start with 3 normally, but we set to 0 for testing)
    $updateStmt = $pdo->prepare("UPDATE users SET available_posts = 0 WHERE id = ?");
    $updateStmt->execute([$userId]);

    echo "<h1>✅ Test Payment Flow Setup Complete</h1>";
    echo "<p><strong>User:</strong> {$userEmail}</p>";
    echo "<p><strong>Available Posts Set To:</strong> 0</p>";
    echo "<p><strong>Status:</strong> Payment required for next post</p>";

    echo "<hr>";

    echo "<h2>🧪 Test Instructions</h2>";
    echo "<ol>";
    echo "<li>Login as: <strong>{$userEmail}</strong></li>";
    echo "<li>Go to Dashboard</li>";
    echo "<li>Click 'Post Accommodation'</li>";
    echo "<li>You should see the payment prompt (not the form)</li>";
    echo "<li>Click 'Pay $5 & Post Now' to test payment flow</li>";
    echo "</ol>";

    echo "<p style='color: #4ecdc4;'>💡 After payment, available_posts will increase by 1, allowing you to post.</p>";
    echo "<p style='color: #4ecdc4;'>💡 To reset: Run this script again or manually update available_posts in database</p>";

    // Show current user's stats
    $statsStmt = $pdo->prepare("SELECT room_posts_count, available_posts FROM users WHERE id = ?");
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    echo "<hr>";
    echo "<h2>Current User Stats</h2>";
    echo "<p>Posts Count: <strong>{$stats['room_posts_count']}</strong></p>";
    echo "<p>Available Posts: <strong>{$stats['available_posts']}</strong></p>";
    echo "<p>Needs Payment: <strong>" . ($stats['available_posts'] <= 0 ? 'YES' : 'NO') . "</strong></p>";

} catch (Exception $e) {
    echo "<h1>❌ Setup Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
