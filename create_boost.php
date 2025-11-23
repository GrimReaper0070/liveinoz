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

    // Log request for debugging
    error_log('Boost request: ' . json_encode($input));

    if (!$input || !isset($input['userId']) || !isset($input['roomId'])) {
        error_log('Invalid input: ' . json_encode($input));
        throw new Exception('userId and roomId required');
    }

    $userId = intval($input['userId']);
    $roomId = intval($input['roomId']);

    error_log("Processing boost for user ID: $userId, room ID: $roomId");

    // Add CORS headers for debugging
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Check room ownership and boost status
    $stmt = $pdo->prepare('SELECT city, is_boosted FROM rooms WHERE id = ? AND user_id = ? AND is_approved = 1');
    $stmt->execute([$roomId, $userId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception('Room not found or not owned by user');
    }

    if ($room['is_boosted']) {
        throw new Exception('Room is already boosted');
    }

    $city = $room['city'];

    // Check for maximum 6 boosted listings per city
    $stmt = $pdo->prepare('SELECT COUNT(*) as active_boosts FROM boosts WHERE city = ? AND status = "active" AND expires_at > NOW()');
    $stmt->execute([$city]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['active_boosts'] >= 6) {
        throw new Exception('Maximum 6 boosted listings allowed per city');
    }

    // Get user plan for pricing
    $stmt = $pdo->prepare('SELECT plan_type FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $planType = $user['plan_type'] ?? 'free';

    // Set boost price based on plan
    $boostPrice = ($planType === 'premium') ? 1000 : 1500; // $10 for premium, $15 for others

    // Get user email
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $userEmail = $userInfo['email'];

    // Create Stripe checkout session for boost
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Room Listing Boost',
                    'description' => '3-day top placement in city search results'
                ],
                'unit_amount' => $boostPrice,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => FRONTEND_URL . '/dashboard.html?boost=success&session_id={CHECKOUT_SESSION_ID}&room_id=' . $roomId,
        'cancel_url' => FRONTEND_URL . '/dashboard.html?boost=cancelled',
        'metadata' => [
            'user_id' => strval($userId),
            'room_id' => strval($roomId),
            'payment_type' => 'boost'
        ],
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
