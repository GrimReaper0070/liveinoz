<?php
// Test subscription creation without webhook dependency

require_once 'config.php';
require_once 'stripe_config.php';

$userId = 10; // Test user
$planType = 'basic';

echo "<h1>🧪 DIRECT SUBSCRIPTION TEST (No Webhook)</h1>";

// Manually upgrade user to basic plan
try {
    $pdo = getDBConnection();

    $plans = [
        'free' => ['listingsLimit' => 1, 'boostCredits' => 1],
        'basic' => ['listingsLimit' => 3, 'boostCredits' => 1],
        'premium' => ['listingsLimit' => 10, 'boostCredits' => 0]
    ];

    $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Update user directly
    $stmt = $pdo->prepare('
        UPDATE users SET plan_type = ?, active_listings_limit = ?, boost_credits = ?, plan_expires_at = ? WHERE id = ?
    ');
    $result = $stmt->execute([
        $planType,
        $plans[$planType]['listingsLimit'],
        $plans[$planType]['boostCredits'],
        $periodEnd,
        $userId
    ]);

    if ($result) {
        echo "<p style='color: green;'>✅ User {$userId} upgraded to {$planType} plan</p>";

        // Verify
        $stmt = $pdo->prepare('SELECT plan_type, active_listings_limit FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<p>Database shows: {$user['plan_type']} plan with {$user['active_listings_limit']} listings limit</p>";
        echo "<p><strong>Test complete!</strong> Check dashboard now - should show Basic plan.</p>";
    } else {
        echo "<p style='color: red;'>❌ Update failed</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
