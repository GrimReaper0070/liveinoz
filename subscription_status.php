<?php
require_once 'config.php';
require_once 'stripe_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$plans = [
    'free' => ['name' => 'Free Plan', 'listingsLimit' => 1, 'boostCredits' => 1, 'price' => 0],
    'basic' => ['name' => 'Basic Plan', 'listingsLimit' => 3, 'boostCredits' => 1, 'price' => 2500],
    'premium' => ['name' => 'Premium Plan', 'listingsLimit' => 10, 'boostCredits' => 0, 'price' => 5000]
];

try {
    if (!isset($_GET['userId'])) {
        throw new Exception('User ID required');
    }

    $userId = intval($_GET['userId']);

    // Get subscription and user plan data
    $stmt = $pdo->prepare('
        SELECT s.*, u.plan_type, u.plan_expires_at, u.active_listings_limit
        FROM subscriptions s
        RIGHT JOIN users u ON s.user_id = u.id
        WHERE u.id = ? AND (s.status = "active" OR s.id IS NULL)
        ORDER BY s.created_at DESC
        LIMIT 1
    ');
    $stmt->execute([$userId]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('User not found');
    }

    $subscriptionInfo = null;
    if ($data['stripe_subscription_id']) {
        $subscriptionInfo = [
            'id' => $data['id'],
            'plan_type' => $data['plan_type'],
            'status' => $data['status'],
            'current_period_start' => $data['current_period_start'],
            'current_period_end' => $data['current_period_end'],
            'amount' => $data['amount']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'current_plan' => $data['plan_type'],
            'plan_expires_at' => $data['plan_expires_at'],
            'active_listings_limit' => $data['active_listings_limit'],
            'subscription' => $subscriptionInfo,
            'plan_details' => $plans[$data['plan_type']] ?? $plans['free']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
