<?php
require_once 'config.php';
require_once 'stripe_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['userId'])) {
        throw new Exception('User ID required');
    }

    $userId = intval($input['userId']);

    // Get active subscription
    $stmt = $pdo->prepare('
        SELECT stripe_subscription_id FROM subscriptions
        WHERE user_id = ? AND status = "active"
        ORDER BY created_at DESC LIMIT 1
    ');
    $stmt->execute([$userId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscription) {
        throw new Exception('No active subscription found');
    }

    $stripeSubscriptionId = $subscription['stripe_subscription_id'];

    // Cancel subscription in Stripe
    $stripeSubscription = \Stripe\Subscription::update($stripeSubscriptionId, [
        'cancel_at_period_end' => true
    ]);

    // Update subscription status locally
    $stmt = $pdo->prepare('
        UPDATE subscriptions SET status = "cancelled", updated_at = NOW()
        WHERE stripe_subscription_id = ?
    ');
    $stmt->execute([$stripeSubscriptionId]);

    echo json_encode([
        'success' => true,
        'message' => 'Subscription will be canceled at the end of the current billing period',
        'cancelAt' => $stripeSubscription->cancel_at
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
