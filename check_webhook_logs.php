<?php
// Check webhook logs and database state

echo "<h1>🔍 Webhook Test Results - Check Database & Logs</h1>";
echo "<p>Examining subscription status and webhook processing from your recent test...</p>";

// Check database subscription state
require_once 'config.php';
require_once 'stripe_config.php';

try {
    $pdo = getDBConnection();

    // Check current subscriptions
    echo "<h2>📊 Current Subscription Database State</h2>";
    $stmt = $pdo->query("SELECT * FROM subscriptions ORDER BY created_at DESC LIMIT 5");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subscriptions)) {
        echo "<p style='color: orange;'>📝 No subscriptions in database yet</p>";
    } else {
        echo "<table border='1' style='width:100%; border-collapse:collapse;'>
        <tr><th>ID</th><th>User</th><th>Plan</th><th>Status</th><th>Stripe ID</th><th>Created</th></tr>";
        foreach ($subscriptions as $sub) {
            echo "<tr>
            <td>{$sub['id']}</td>
            <td>{$sub['user_id']}</td>
            <td>{$sub['plan_type']}</td>
            <td>{$sub['status']}</td>
            <td>" . substr($sub['stripe_subscription_id'], 0, 20) . "...</td>
            <td>{$sub['created_at']}</td>
            </tr>";
        }
        echo "</table>";
    }

    // Check user plan distribution
    echo "<h2>👥 User Plan Status</h2>";
    $stmt = $pdo->prepare("SELECT plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?");
    $stmt->execute([2]); // User ID 2 from test
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<h3>User 2 (Ashraful) Status:</h3>";
        echo "<ul>";
        echo "<li>Plan: {$user['plan_type']}</li>";
        echo "<li>Listings limit: {$user['active_listings_limit']}</li>";
        echo "<li>Expires: {$user['plan_expires_at']}</li>";
        echo "</ul>";

        if ($user['plan_type'] === 'basic' && $user['active_listings_limit'] == 3) {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: User has been upgraded to Basic plan!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ FAILURE: User was not upgraded - still has {$user['plan_type']} plan</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User not found!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>📋 What Happened in Your Test</h2>";
echo "<p>From your Stripe CLI logs, I saw:</p>";
echo "<ul>";
echo "<li>🔹 <code>customer.subscription.created</code> - ✅ SUBSCRIPTION CREATED</li>";
echo "<li>🔹 <code>checkout.session.completed</code> - ✅ PAYMENT COMPLETED</li>";
echo "<li>🔹 Multiple <code>[200] POST</code> responses - ✅ WEBHOOKS DELIVERED</li>";
echo "</ul>";

echo "<h2>🔍 What To Check Next</h2>";
echo "<p><strong>Visit this URL to check PHP logs:</strong></p>";
echo "<p><code>file:///C:/xampp/php/logs/php_error_log</code></p>";

echo "<h3>Look for these entries in the logs:</h3>";
echo "<ul>";
echo "<li><code>=== STRIPE WEBHOOK RECEIVED ===</code></li>";
echo "<li><code>Processing customer.subscription.created</code></li>";
echo "<li><code>✅ User X upgraded to basic plan</code></li>";
echo "<li><code>❌ Database error</code> - if any</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🎯 FINAL DIAGNOSIS</h3>";

if ($user && $user['plan_type'] === 'basic') {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>
    🎉 SUCCESS! STRIPE SUBSCRIPTIONS ARE WORKING!
    </p>";
} else {
    echo "<p style='color: red; font-size: 16px; font-weight: bold;'>
    🔧 SUBSCRIPTIONS NOT WORKING - Check the PHP logs
    </p>";

    echo "<h3>If logs show:</h3>";
    echo "<ul>";
    echo "<li><strong>NO webhook logs:</strong> Stripe CLI tunnel issue - restart it</li>";
    echo "<li><strong>Webhook received but no user update:</strong> Subscription metadata issue in webhook handler</li>";
    echo "<li><strong>Database errors:</strong> Permission/connection issues</li>";
    echo "</ul>";
}

echo "<p><strong>Share the PHP error logs</strong> and we'll have the complete picture!</p>";
?>
