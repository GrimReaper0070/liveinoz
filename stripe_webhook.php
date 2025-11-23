<?php
require_once 'config.php';
require_once 'stripe_config.php';

// Enhanced logging for webhook debugging
error_log("=== STRIPE WEBHOOK RECEIVED ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Get raw body for webhook signature verification
$payload = file_get_contents('php://input');
error_log("Payload size: " . strlen($payload) . " bytes");

$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
error_log("Signature header present: " . (!empty($sig_header) ? 'yes' : 'no'));

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, STRIPE_WEBHOOK_SECRET
    );
    error_log("Webhook signature verified successfully");
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    error_log("WEBHOOK ERROR - Invalid payload: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log("WEBHOOK ERROR - Invalid signature: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

error_log("Event type: " . $event->type);
error_log("Event ID: " . $event->id);

$plans = [
    'free' => ['name' => 'Free Plan', 'listingsLimit' => 1, 'boostCredits' => 1, 'price' => 0],
    'basic' => ['name' => 'Basic Plan', 'listingsLimit' => 3, 'boostCredits' => 1, 'price' => 2500],
    'premium' => ['name' => 'Premium Plan', 'listingsLimit' => 10, 'boostCredits' => 0, 'price' => 5000]
];

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        $paymentType = $session->metadata->payment_type ?? 'unknown';
        error_log("Processing checkout.session.completed - Payment type: {$paymentType}");

        try {
            $userId = intval($session->metadata->user_id);
            $amountInDollars = $session->amount_total / 100;
            error_log("User ID: {$userId}, Amount: \${$amountInDollars}");

            // Insert payment record
            $stmt = $pdo->prepare('
                INSERT INTO payments
                (stripe_payment_id, user_id, amount, currency, status, payment_type, stripe_session_id, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $session->payment_intent ?: $session->id,
                $userId,
                $amountInDollars,
                $session->currency ?: 'usd',
                'completed',
                $paymentType,
                $session->id,
                json_encode($session->metadata)
            ]);

            error_log("✅ Payment record created for user {$userId}, type: {$paymentType}, amount: \${$amountInDollars}");

            if ($paymentType === 'boost') {
                $roomId = intval($session->metadata->room_id ?? 0);
                if ($roomId) {
                    $boostExpiresAt = date('Y-m-d H:i:s', strtotime('+3 days'));

                    // Get city for the room
                    $stmt = $pdo->prepare('SELECT city FROM rooms WHERE id = ?');
                    $stmt->execute([$roomId]);
                    $roomData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $city = $roomData ? $roomData['city'] : 'Unknown';

                    // Update room boost status
                    $stmt = $pdo->prepare('
                        UPDATE rooms SET is_boosted = 1, boost_expires_at = ?, boost_cost = ? WHERE id = ?
                    ');
                    $stmt->execute([$boostExpiresAt, $amountInDollars, $roomId]);

                    // Insert boost record
                    $stmt = $pdo->prepare('
                        INSERT INTO boosts
                        (room_id, user_id, city, cost, stripe_payment_id, activated_at, expires_at, status)
                        VALUES (?, ?, ?, ?, ?, NOW(), ?, "active")
                    ');
                    $stmt->execute([
                        $roomId, $userId, $city, $amountInDollars,
                        $session->payment_intent ?: $session->id, $boostExpiresAt
                    ]);

                    error_log("✅ Boost activated for room {$roomId} in {$city}");
                } else {
                    error_log("❌ Boost processing failed - no room_id in metadata");
                }
            } elseif ($paymentType === 'subscription') {
                // Initial subscription payment handled by customer.subscription.created event
                error_log("✅ Initial subscription checkout completed for user {$userId} - waiting for subscription.created event");
            } elseif ($paymentType === 'room_posting') {
                // Only increment posts if not development mode
                if (getenv('NODE_ENV') !== 'development') {
                    $stmt = $pdo->prepare('UPDATE users SET available_posts = available_posts + 1 WHERE id = ?');
                    $stmt->execute([$userId]);
                    error_log("✅ Incremented available_posts for user {$userId}");
                } else {
                    error_log("ℹ️ Development mode - available_posts not incremented");
                }
            } else {
                error_log("ℹ️ Unhandled payment type: {$paymentType}");
            }

        } catch (Exception $dbError) {
            error_log('❌ Database update error in checkout.session.completed: ' . $dbError->getMessage());
            error_log('Stack trace: ' . $dbError->getTraceAsString());
        }

        // Add aggressive fallback to handle subscription activation if the subscription event fails
        if ($paymentType === 'subscription') {
            error_log("🔄 Implementing aggressive fallback subscription activation for user {$userId}");

            // Extract plan type reliably from metadata - convert to array and debug
            $metadataArray = [];
            if (isset($session->metadata)) {
                $metadataArray = (array) $session->metadata;
            }

            error_log("DEBUG - Checkout session metadata: " . json_encode($metadataArray));

            $planTypeFromMetadata = $metadataArray['plan_type'] ?? $metadataArray['planType'] ?? '';

            error_log("Checkout fallback - Plan type extracted: '{$planTypeFromMetadata}'");

            // If we can't find it in standard places, try the object properties
            if (empty($planTypeFromMetadata) && isset($session->metadata->plan_type)) {
                $planTypeFromMetadata = $session->metadata->plan_type;
                error_log("Found plan_type via object access: '{$planTypeFromMetadata}'");
            }

            if (empty($planTypeFromMetadata) && isset($session->metadata->planType)) {
                $planTypeFromMetadata = $session->metadata->planType;
                error_log("Found planType via object access: '{$planTypeFromMetadata}'");
            }

            try {
                // Activate immediately if we can determine the plan
                if (!empty($planTypeFromMetadata) && isset($plans[$planTypeFromMetadata])) {
                    $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days')); // Default 30 days

                    $stmt = $pdo->prepare('
                        UPDATE users SET plan_type = ?, active_listings_limit = ?, boost_credits = ?, plan_expires_at = ? WHERE id = ?
                    ');
                    $updateResult = $stmt->execute([
                        $planTypeFromMetadata,
                        $plans[$planTypeFromMetadata]['listingsLimit'],
                        $plans[$planTypeFromMetadata]['boostCredits'],
                        $periodEnd,
                        $userId
                    ]);

                    if ($updateResult) {
                        error_log("✅ AGGRESSIVE FALLBACK: User {$userId} activated {$planTypeFromMetadata} plan immediately - {$plans[$planTypeFromMetadata]['listingsLimit']} listings, expires {$periodEnd}");

                        // Verify it worked
                        $stmt = $pdo->prepare('SELECT plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?');
                        $stmt->execute([$userId]);
                        $userAfter = $stmt->fetch(PDO::FETCH_ASSOC);
                        error_log("✅ Checkout fallback verification: " . json_encode($userAfter));
                    } else {
                        error_log("❌ Aggressive fallback update failed for user {$userId}");
                    }
                } else {
                    error_log("❌ Could not determine plan type from checkout metadata. Available keys: " . implode(', ', array_keys($metadataArray)));
                }
            } catch (Exception $fallbackError) {
                error_log('❌ Aggressive fallback activation error: ' . $fallbackError->getMessage());
                error_log('Stack trace: ' . $fallbackError->getTraceAsString());
            }
        }
        break;

    case 'customer.subscription.created':
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        error_log("=== PROCESSING SUBSCRIPTION EVENT ===");
        error_log("Event type: " . $event->type . " - Subscription ID: " . $subscription->id);

        // Extract metadata - ensure it's accessible as array for reliable key access
        $metadata = [];
        if (isset($subscription->metadata)) {
            $metadata = (array) $subscription->metadata;
        }

        error_log("Full subscription object: " . json_encode($subscription));
        error_log("Subscription metadata: " . json_encode($metadata));

        // Get required fields with better validation
        $paymentType = $metadata['payment_type'] ?? '';
        $userId = intval($metadata['user_id'] ?? $metadata['userId'] ?? 0); // Support both formats
        $planType = $metadata['plan_type'] ?? $metadata['planType'] ?? ''; // Support both formats

        error_log("CRITICAL EXTRACTED DATA - Type: {$paymentType}, User ID: {$userId}, Plan: {$planType}");

        // Validate this is our subscription
        if ($paymentType !== 'subscription') {
            error_log("ℹ️ Skipping non-subscription event (type: {$paymentType}) - not for us");
            break;
        }

        // Ensure we have required data
        if (!$userId || !$planType) {
            error_log("❌ MISSING REQUIRED DATA - User ID: {$userId}, Plan Type: {$planType} - cannot proceed!");
            error_log("Full metadata debug: " . json_encode($metadata));
            break;
        }

        // Validate plan exists
        if (!isset($plans[$planType])) {
            error_log("❌ INVALID PLAN TYPE: {$planType} - available: " . implode(', ', array_keys($plans)));
            break;
        }

        error_log("✅ ALL VALIDATIONS PASSED - proceeding with plan activation for user {$userId} to {$planType}");

            try {
                $periodStart = date('Y-m-d H:i:s', $subscription->current_period_start);
                $periodEnd = date('Y-m-d H:i:s', $subscription->current_period_end);

                // Insert/update subscription record
                $stmt = $pdo->prepare('
                    INSERT INTO subscriptions
                    (user_id, plan_type, stripe_subscription_id, status, current_period_start, current_period_end, amount, currency)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    current_period_start = VALUES(current_period_start),
                    current_period_end = VALUES(current_period_end),
                    updated_at = NOW()
                ');
                $stmt->execute([
                    $userId,
                    $planType,
                    $subscription->id,
                    $subscription->status,
                    $periodStart,
                    $periodEnd,
                    ($plans[$planType]['price'] ?? 0) / 100,
                    $subscription->currency
                ]);

                error_log("✅ Subscription record created/updated for user {$userId}");

                // Update user plan
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
                    error_log("✅ User {$userId} upgraded to {$planType} plan (listings: {$plans[$planType]['listingsLimit']}, expires: {$periodEnd})");

                    // Verify the update worked
                    $stmt = $pdo->prepare('SELECT plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?');
                    $stmt->execute([$userId]);
                    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log("✅ Verification - User plan after update: " . json_encode($updatedUser));

                    // Check if we need to update SESSION variable for immediate effect
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        error_log("Session is active, user update should be visible immediately");
                    } else {
                        error_log("Session not active - user may need to refresh or logout/login");
                    }
                } else {
                    error_log("❌ User plan update failed for user {$userId} - no database rows affected");
                }

            } catch (Exception $dbError) {
                error_log('❌ Database error in subscription event: ' . $dbError->getMessage());
                error_log('Stack trace: ' . $dbError->getTraceAsString());
            }
        break;

    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        $userId = intval($subscription->metadata->user_id ?? null);

        if ($userId) {
            // Cancel subscription
            $stmt = $pdo->prepare('
                UPDATE subscriptions SET status = "canceled" WHERE stripe_subscription_id = ?
            ');
            $stmt->execute([$subscription->id]);

            // Revert user to free plan
            $stmt = $pdo->prepare('
                UPDATE users SET plan_type = "free", active_listings_limit = ?, boost_credits = ?, plan_expires_at = NULL WHERE id = ?
            ');
            $stmt->execute([$plans['free']['listingsLimit'], $plans['free']['boostCredits'], $userId]);

            error_log("User {$userId} subscription canceled, reverted to free plan");
        }
        break;

    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        error_log("Processing invoice.payment_succeeded - Invoice ID: " . $invoice->id . ", Billing reason: " . ($invoice->billing_reason ?? 'none'));

        if ($invoice->billing_reason === 'subscription_create') {
            if ($invoice->subscription) {
                error_log("Subscription create invoice - looking up subscription: " . $invoice->subscription);

                $stmt = $pdo->prepare('
                    SELECT user_id, plan_type FROM subscriptions WHERE stripe_subscription_id = ?
                ');
                $stmt->execute([$invoice->subscription]);
                $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($subscription) {
                    $planType = $subscription['plan_type'];
                    $userId = $subscription['user_id'];

                    // Use period_end from subscription, not hardcoded 30 days
                    $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days')); // Fallback

                    // Try to get the actual period end from subscription if possible
                    if (isset($invoice->subscription_details) && isset($invoice->subscription_details->current_period_end)) {
                        $periodEnd = date('Y-m-d H:i:s', $invoice->subscription_details->current_period_end);
                    }

                    error_log("Activating plan via invoice - User: {$userId}, Plan: {$planType}, Expires: {$periodEnd}");

                    $stmt = $pdo->prepare('
                        UPDATE users SET plan_type = ?, active_listings_limit = ?, boost_credits = ?, plan_expires_at = ? WHERE id = ?
                    ');
                    $stmt->execute([
                        $planType,
                        $plans[$planType]['listingsLimit'],
                        $plans[$planType]['boostCredits'],
                        $periodEnd,
                        $userId
                    ]);

                    error_log("✅ User {$userId} plan activated via invoice - {$planType} plan");

                    // Verify the update
                    $stmt = $pdo->prepare('SELECT plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?');
                    $stmt->execute([$userId]);
                    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log("✅ Invoice activation verification: " . json_encode($updatedUser));

                } else {
                    error_log("❌ Subscription not found for invoice: " . $invoice->subscription);
                }
            } else {
                error_log("❌ Invoice payment succeeded but no subscription ID");
            }
        } else {
            error_log("ℹ️ Invoice payment succeeded but not for subscription creation (reason: " . ($invoice->billing_reason ?? 'none') . ")");
        }
        break;

    case 'invoice.payment_failed':
        $failedInvoice = $event->data->object;
        error_log('Invoice payment failed: ' . $failedInvoice->subscription);
        break;

    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object;
        error_log('Payment failed: ' . $paymentIntent->id);
        break;

    default:
        error_log("Unhandled event type: {$event->type}");
}

error_log("=== WEBHOOK PROCESSING COMPLETE ===");
error_log("Event ID: {$event->id}, Type: {$event->type}");

http_response_code(200);
echo json_encode(['received' => true, 'event_id' => $event->id, 'timestamp' => date('Y-m-d H:i:s')]);
?>
