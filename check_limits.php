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

    // Get user plan and posting stats
    $stmt = $pdo->prepare("
        SELECT u.plan_type, u.plan_expires_at, u.active_listings_limit,
               COUNT(r.id) as active_rooms
        FROM users u
        LEFT JOIN rooms r ON u.id = r.user_id AND r.is_approved = 1
            AND (r.expires_at IS NULL OR r.expires_at > NOW())
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        throw new Exception('User not found');
    }

    $user = $userData;
    $currentPlan = $plans[$user['plan_type']] ?? $plans['free'];
    $activeRooms = intval($user['active_rooms'] ?? 0);
    $listingsLimit = intval($user['active_listings_limit'] ?? $currentPlan['listingsLimit']);

    // Check if plan is expired
    $planExpired = false;
    if ($user['plan_expires_at'] && strtotime($user['plan_expires_at']) < time()) {
        $planExpired = true;
        $user['plan_type'] = 'free';
        $user['active_listings_limit'] = $plans['free']['listingsLimit'];
    }

    $canPostMore = $activeRooms < $listingsLimit;
    $remainingSlots = max(0, $listingsLimit - $activeRooms);

    echo json_encode([
        'success' => true,
        'data' => [
            'plan_type' => $user['plan_type'],
            'plan_expired' => $planExpired,
            'active_rooms' => $activeRooms,
            'listings_limit' => $listingsLimit,
            'remaining_slots' => $remainingSlots,
            'can_post_more' => $canPostMore,
            'plan_details' => $currentPlan
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
