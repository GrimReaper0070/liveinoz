<?php
session_start();
require_once 'config.php';
require_once 'check_bad_words.php';
require_once 'rate_limiter.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if user is verified
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT is_verified FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['is_verified'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Account not verified. Please wait for admin approval.']);
        exit;
    }
} catch (Exception $e) {
    error_log("Verification check error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Authentication error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$room = trim($_POST['room'] ?? '');
$state = trim($_POST['state'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($room) || empty($state) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate that the room exists for the given state
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE state_code = ? AND room_name = ? AND is_active = TRUE");
    $stmt->execute([$state, $room]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid room or state']);
        exit;
    }
} catch (Exception $e) {
    error_log("Room validation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Validation error']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message too long']);
    exit;
}

// Check rate limit
$rate_limit = checkRateLimit($user_id);
if (!$rate_limit['allowed']) {
    echo json_encode([
        'success' => false,
        'message' => $rate_limit['message']
    ]);
    exit;
}

// Check for bad words
$bad_word_check = checkMessageForBadWords($message);
if ($bad_word_check['blocked']) {
    echo json_encode([
        'success' => false,
        'message' => 'Message contains inappropriate content and cannot be sent'
    ]);
    exit;
}

// Use censored message if violations found
$message_to_store = $bad_word_check['clean'] ? $message : $bad_word_check['censored'];

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, room, state, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $room, $state, $message_to_store]);

    // Record the message send for rate limiting
    recordMessageSend($user_id);

    echo json_encode(['success' => true, 'message' => 'Message sent']);
} catch (Exception $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>
