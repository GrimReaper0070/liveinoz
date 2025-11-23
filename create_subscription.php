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

$plans = [
    'free' => ['name' => 'Free Plan', 'listingsLimit' => 1, 'boostCredits' => 1, 'price' => 0],
    'basic' => ['name' => 'Basic Plan', 'listingsLimit' => 3, 'boostCredits' => 1, 'price' => 2500], // $25/month
    'premium' => ['name' => 'Premium Plan', 'listingsLimit' => 10, 'boostCredits' => 0, 'price' => 5000] // $50/month
];

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['userId']) || !isset($input['planType'])) {
        throw new Exception('Valid userId and planType (basic/premium) required');
    }

    $userId = intval($input['userId']);
    $planType = $input['planType'];

    if (!isset($plans[$planType]) || $planType === 'free') {
        throw new Exception('Cannot create subscription for free plan');
    }

    $plan = $plans[$planType];

    // Get user email
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    $userEmail = $user['email'];

    // Create Stripe checkout session for subscription
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $plan['name'] . ' - Monthly Subscription',
                    'description' => 'Up to ' . $plan['listingsLimit'] . ' active listings per month'
                ],
                'unit_amount' => $plan['price'],
                'recurring' => [
                    'interval' => 'month'
                ]
            ],
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => FRONTEND_URL . '/dashboard.html?subscription=success&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => FRONTEND_URL . '/dashboard.html?subscription=cancelled',
        'metadata' => [
            'user_id' => strval($userId),
            'plan_type' => $planType,
            'payment_type' => 'subscription'
        ],
        'client_reference_id' => strval($userId), // Additional reference
        'customer_email' => $userEmail
    ]);

    echo json_encode([
        'success' => true,
        'sessionId' => $session->id,
        'url' => $session->url
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
