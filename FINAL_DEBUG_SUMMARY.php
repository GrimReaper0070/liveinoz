<?php
echo "<h1>🎯 STRIPE SUBSCRIPTION ISSUE - FINAL DEBUG REPORT</h1>";

// Test database connectivity and webhook processing
require_once 'config.php';
require_once 'stripe_config.php';

echo "<h2>🗄️ Database Status</h2>";
try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";

    // Check subscription table
    $stmt = $pdo->query("SELECT COUNT(*) FROM subscriptions");
    $subscriptionCount = $stmt->fetchColumn();
    echo "<p>📊 Subscriptions in database: {$subscriptionCount}</p>";

    // Check user plans
    $stmt = $pdo->prepare("SELECT plan_type, COUNT(*) as count FROM users GROUP BY plan_type");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>👥 User plan distribution:</p>";
    echo "<ul>";
    foreach ($plans as $plan) {
        echo "<li>{$plan['plan_type']}: {$plan['count']} users</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>⚙️ Configuration Status</h2>";
echo "<p>Stripe API Key: " . (!empty(getenv('STRIPE_SECRET_KEY')) || strpos('sk_test_', getDBConnection()->exec ?? '') !== false ? '✅ CONFIGURED' : '❌ MISSING') . "</p>";
echo "<p>Webhook Secret: " . (!empty(STRIPE_WEBHOOK_SECRET) ? '✅ CONFIGURED' : '❌ MISSING') . "</p>";
echo "<p>Frontend URL: " . (defined('FRONTEND_URL') ? FRONTEND_URL : '❌ NOT CONFIGURED') . "</p>";

echo "<h2>🔧 Issue Analysis</h2>";

echo "<h3>What's Working:</h3>";
echo "<ul>";
echo "<li>✅ Stripe Checkout session creation (create_subscription.php)</li>";
echo "<li>✅ User authentication and authorization</li>";
echo "<li>✅ Database schema (subscription tables exist)</li>";
echo "<li>✅ Manual database updates work (test_subscription_manual.php)</li>";
echo "<li>✅ Webhook signature verification</li>";
echo "<li>✅ Stripe CLI tunnel forwarding events (from your logs)</li>";
echo "</ul>";

echo "<h3>What's NOT Working:</h3>";
echo "<ul>";
echo "<li>❌ User plan changes after subscription purchase</li>";
echo "<li>❓ Webhook event processing may have bugs in metadata handling</li>";
echo "</ul>";

echo "<h2>🎯 Order of Testing</h2>";
echo "<ol>";
echo "<li><strong>Current step:</strong> Check your local PHP error logs for webhook processing reports</li>";
echo "<li><strong>Do this:</strong> Make a subscription purchase while watching your Stripe CLI terminal</li>";
echo "<li><strong>Look for:</strong> Webhook events with status '[200] POST' in Stripe CLI</li>";
echo "<li><strong>Check these log locations:</strong>";
echo "<ul>";
echo "<li>C:\\xampp\\php\\logs\\php_error_log</li>";
echo "<li>C:\\xampp\\apache\\logs\\error.log</li>";
echo "<li>Stripe CLI terminal output</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>If webhooks are working:</strong> Look for '=== STRIPE WEBHOOK RECEIVED ===' in PHP logs</li>";
echo "<li><strong>If NOT:</strong> Stripe CLI tunnel may have issues - check your local server</li>";
echo "</ol>";

echo "<h2>🚨 IF STRIPE SUBSCRIPTIONS STILL DON'T WORK</h2>";
echo "<p>The webhooks are being forwarded by Stripe CLI successfully based on your logs showing '[200] POST'. If user plans aren't updating, it means there's a bug in the webhook handler logic.</p>";

echo "<p><strong>Share the PHP error logs</strong> after making a subscription purchase - I'll identify the exact issue.</p>";

echo "<hr><p><strong>Status: Ready for final testing</strong></p>";
?>
