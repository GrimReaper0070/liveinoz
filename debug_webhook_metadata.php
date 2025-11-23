<?php
// Debug webhook metadata issues
require_once 'config.php';
require_once 'stripe_config.php';

echo "<h1>Webhook Metadata Debug</h1>";

// Check what the subscription creation sets as metadata
$userId = 2; // Test user ID
$planType = 'basic';

echo "<h3>Simulating subscription creation metadata...</h3>";
echo "<p>User ID being sent: {$userId}</p>";
echo "<p>Plan type being sent: {$planType}</p>";

// Check what database expects vs what webhook receives
echo "<h3>Expected vs Received Comparison:</h3>";

$expectedMetadata = [
    'user_id' => strval($userId),
    'plan_type' => $planType,
    'payment_type' => 'subscription'
];

echo "<h4>Expected metadata (from create_subscription.php):</h4>";
echo "<pre>" . json_encode($expectedMetadata, JSON_PRETTY_PRINT) . "</pre>";

// Check webhook processing
echo "<h4>Webhook processing validation:</h4>";

// Test the exact logic from webhook
$userIdFromWebhook = intval($expectedMetadata['user_id'] ?? 0);
$planTypeFromWebhook = $expectedMetadata['plan_type'] ?? '';
$paymentTypeFromWebhook = $expectedMetadata['payment_type'] ?? '';

echo "<p>User ID extracted: {$userIdFromWebhook} (should be {$userId})</p>";
echo "<p>Plan type extracted: '{$planTypeFromWebhook}' (should be '{$planType}')</p>";
echo "<p>Payment type extracted: '{$paymentTypeFromWebhook}' (should be 'subscription')</p>";

$plans = [
    'free' => ['name' => 'Free Plan', 'listingsLimit' => 1, 'boostCredits' => 1, 'price' => 0],
    'basic' => ['name' => 'Basic Plan', 'listingsLimit' => 3, 'boostCredits' => 1, 'price' => 2500],
    'premium' => ['name' => 'Premium Plan', 'listingsLimit' => 10, 'boostCredits' => 0, 'price' => 5000]
];

$metadataCheck = [
    'has_payment_type' => $paymentTypeFromWebhook === 'subscription',
    'valid_user_id' => $userIdFromWebhook > 0,
    'valid_plan_type' => isset($plans[$planTypeFromWebhook]),
    'user_exists' => false
];

if ($userIdFromWebhook > 0) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = ?');
    $stmt->execute([$userIdFromWebhook]);
    $metadataCheck['user_exists'] = $stmt->fetchColumn() > 0;
}

echo "<h4>Webhook validation results:</h4>";
echo "<ul>";
foreach ($metadataCheck as $check => $result) {
    $status = $result ? '✅' : '❌';
    echo "<li>{$status} {$check}: {$result}</li>";
}
echo "</ul>";

$allChecksPass = array_filter($metadataCheck);
if (count($allChecksPass) === count($metadataCheck)) {
    echo "<p style='color: green; font-weight: bold;'>✅ All metadata checks pass! Webhook should process correctly.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Some metadata checks failed! This explains why subscriptions aren't being processed.</p>";
}

echo "<h3>Common Issues to Check:</h3>";
echo "<ol>";
echo "<li>Verify webhook URL in Stripe Dashboard is exactly: <code>http://localhost/oznewfinal/stripe_webhook.php</code></li>";
echo "<li>Ensure webhook secret matches STRIPE_WEBHOOK_SECRET in stripe_config.php</li>";
echo "<li>Check if Stripe dashboard shows webhook events being sent</li>";
echo "<li>Monitor PHP error logs during subscription attempts</li>";
echo "</ol>";
?>
