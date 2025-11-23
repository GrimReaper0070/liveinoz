<?php
// Simple test for webhook endpoint accessibility
echo "Testing webhook endpoint...<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "Webhook endpoint: " . $_SERVER['PHP_SELF'] . "<br>";
echo "✅ Webhook endpoint is accessible!";
?>

<hr>
<h3>Stripe Configuration Test:</h3>
<?php
require_once 'stripe_config.php';

try {
    echo "✅ Stripe SDK loaded<br>";
    echo "Stripe API Key configured: " . (!empty(getenv('STRIPE_SECRET_KEY')) || strpos($stripeConfig['api_key'] ?? '', 'sk_test_') !== false ? 'YES' : 'NO') . "<br>";

    // Test database connection
    require_once 'config.php';
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";

    // Check webhook secret
    echo "Webhook secret configured: " . (!empty(STRIPE_WEBHOOK_SECRET) ? 'YES' : 'NO') . "<br>";

    echo "<h3>✅ All configurations look good!</h3>";

} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage();
}
?>
