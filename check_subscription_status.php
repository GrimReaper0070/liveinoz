<?php
// Quick check of current subscription status
require_once 'config.php';
require_once 'stripe_config.php';

echo "<h1>🎯 SUBSCRIPTION STATUS CHECK</h1>";

// Get current user ID (hardcoded for testing)
$userId = 2; // Change this to the logged-in user ID

try {
    $pdo = getDBConnection();

    echo "<h2>📊 Database Status</h2>";

    // Check user plan
    $stmt = $pdo->prepare("SELECT id, first_name, plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p><strong>User:</strong> {$user['first_name']} (ID: {$user['id']})</p>";
        echo "<p><strong>Plan:</strong> {$user['plan_type']}</p>";
        echo "<p><strong>Listings Limit:</strong> {$user['active_listings_limit']}</p>";
        echo "<p><strong>Expires:</strong> {$user['plan_expires_at']}</p>";

        $expectedLimit = match($user['plan_type']) {
            'free' => 1,
            'basic' => 3,
            'premium' => 10,
            default => 1
        };

        if ($user['active_listings_limit'] === $expectedLimit) {
            echo "<p style='color: green;'>✅ Plan correctly configured</p>";
        } else {
            echo "<p style='color: red;'>❌ Plan limit mismatch! Expected: {$expectedLimit}, Got: {$user['active_listings_limit']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User not found!</p>";
    }

    // Check subscriptions
    echo "<h2>🔄 Recent Subscriptions</h2>";
    $stmt = $pdo->query("SELECT * FROM subscriptions ORDER BY created_at DESC LIMIT 3");
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subs)) {
        echo "<p>No subscriptions found</p>";
    } else {
        foreach ($subs as $sub) {
            echo "<p><strong>ID {$sub['id']}:</strong> User {$sub['user_id']} - {$sub['plan_type']} plan - Status: {$sub['status']}</p>";
        }
    }

    echo "<h2>🔍 What Should Happen</h2>";
    if ($user && $user['plan_type'] === 'basic' && $user['active_listings_limit'] === 3) {
        echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: User should see 'Basic plan | 1/3 active listings'</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ FAILURE: User will see 'Free plan | 1/1 active listings'</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
