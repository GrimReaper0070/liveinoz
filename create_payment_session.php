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

    // Check if user has free posts available
    $stmt = $pdo->prepare('SELECT room_posts_count FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    $postsCount = $user['room_posts_count'] ?? 0;
    if ($postsCount < 3) {
        throw new Exception('User still has free posts available');
    }

    // Get user email for Stripe metadata
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $userEmail = $userInfo['email'] ?? null;

    // Create Stripe checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Accommodation Posting Fee',
                    'description' => 'One additional room posting'
                ],
                'unit_amount' => 500, // $5.00 in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => FRONTEND_URL . '/dashboard.html?payment=success&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => FRONTEND_URL . '/dashboard.html?payment=cancelled',
        'metadata' => [
            'user_id' => strval($userId),
            'payment_type' => 'room_posting'
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
