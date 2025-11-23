<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Assigning user types to existing users...</h1>";

    // Get all users (since migration sets default to full_access, we need to randomly change some to chat_only)
    $stmt = $pdo->prepare("SELECT id FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalUsers = count($users);
    echo "<p>Found $totalUsers users to assign types to.</p>";

    if ($totalUsers > 0) {
        $chatOnlyCount = 0;
        $fullAccessCount = 0;

        foreach ($users as $user) {
            // Randomly assign: 0 = chat_only, 1 = full_access
            $userType = (mt_rand(0, 1) == 0) ? 'chat_only' : 'full_access';

            if ($userType == 'chat_only') {
                $chatOnlyCount++;
            } else {
                $fullAccessCount++;
            }

            $updateStmt = $pdo->prepare("UPDATE users SET user_type = ? WHERE id = ?");
            $updateStmt->execute([$userType, $user['id']]);
        }

        echo "<p>Assignment complete:</p>";
        echo "<ul>";
        echo "<li>Chat Only: $chatOnlyCount users</li>";
        echo "<li>Full Access: $fullAccessCount users</li>";
        echo "</ul>";
    } else {
        echo "<p>No users found that need type assignment.</p>";
    }

    echo "<h2>Process complete!</h2>";

} catch (Exception $e) {
    echo "<h1>Assignment failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
