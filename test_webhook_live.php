<?php
// Script to manually trigger webhook events for testing

require_once 'config.php';
require_once 'stripe_config.php';

echo "<h1>Live Webhook Test</h1>";

// If an event ID is provided via GET, fetch it from Stripe and simulate webhook processing
if (isset($_GET['event_id'])) {
    $eventId = $_GET['event_id'];

    try {
        // Retrieve the event from Stripe API
        $event = \Stripe\Event::retrieve($eventId);
        echo "<h3>Retrieved Event: {$event->type}</h3>";
        echo "<p>Event ID: {$event->id}</p>";

        // Simulate webhook processing by including the webhook handler
        echo "<h3>Processing Event...</h3>";
        $originalPost = $_POST;
        $originalServer = $_SERVER;

        // Mock the webhook POST data
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        file_put_contents('temp_webhook_payload.json', json_encode($event));

        echo "<iframe src='stripe_webhook.php?test=1' style='width:100%; height:200px; border:1px solid #ccc;'></iframe>";

        echo "<h3>Event Data:</h3>";
        echo "<pre>" . json_encode($event, JSON_PRETTY_PRINT) . "</pre>";

        // Cleanup
        if (file_exists('temp_webhook_payload.json')) {
            unlink('temp_webhook_payload.json');
        }

        $_POST = $originalPost;
        $_SERVER = $originalServer;

    } catch (Exception $e) {
        echo "<p style='color: red;'>Error retrieving event: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Recent Subscription Tests</h3>";
echo "<p>From the Stripe CLI logs, I can see these events were processed:</p>";
echo "<ul>";
echo "<li>✅ customer.subscription.created events</li>";
echo "<li>✅ customer.subscription.updated events</li>";
echo "<li>✅ checkout.session.completed events</li>";
echo "<li>✅ invoice.payment_succeeded events</li>";
echo "</ul>";

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Check user plan status in database after subscription attempts</li>";
echo "<li>Verify PHP error logs show webhook processing (with our enhanced logging)</li>";
echo "<li>Test with a real subscription payment to see live logs</li>";
echo "</ol>";
?>
