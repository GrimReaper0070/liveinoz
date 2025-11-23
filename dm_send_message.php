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
$thread_id = trim($_POST['thread_id'] ?? '');
$message = trim($_POST['message'] ?? '');
$file_id = intval($_POST['file_id'] ?? 0);

if (empty($thread_id)) {
    echo json_encode(['success' => false, 'message' => 'Thread ID required']);
    exit;
}

if (empty($message) && $file_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Message or file required']);
    exit;
}

if (!empty($message) && strlen($message) > 1000) {
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

// Check for bad words in message
if (!empty($message)) {
    $bad_word_check = checkMessageForBadWords($message);
    if ($bad_word_check['blocked']) {
        echo json_encode([
            'success' => false,
            'message' => 'Message contains inappropriate content and cannot be sent'
        ]);
        exit;
    }
    // Use censored message if violations found
    $message = $bad_word_check['clean'] ? $message : $bad_word_check['censored'];
}

// Get file information if file_id is provided
$file_info = null;
if ($file_id > 0) {
    $stmt = $pdo->prepare("
        SELECT file_path, original_name, file_size, mime_type
        FROM file_attachments
        WHERE id = ? AND uploaded_by = ? AND dm_message_id IS NULL
    ");
    $stmt->execute([$file_id, $user_id]);
    $file_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file_info) {
        echo json_encode(['success' => false, 'message' => 'File not found or access denied']);
        exit;
    }
}

try {
    $pdo = getDBConnection();

    // Verify user is participant in this thread
    $stmt = $pdo->prepare("
        SELECT id FROM dm_threads
        WHERE thread_id = ? AND (participant1_id = ? OR participant2_id = ?) AND is_active = TRUE
    ");
    $stmt->execute([$thread_id, $user_id, $user_id]);
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        echo json_encode(['success' => false, 'message' => 'Invalid thread or access denied']);
        exit;
    }

    // Calculate expiration time (24 hours from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Insert the message
    $stmt = $pdo->prepare("
        INSERT INTO dm_messages (thread_id, sender_id, message, file_path, file_name, file_size, file_type, mime_type, created_at, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $stmt->execute([
        $thread_id,
        $user_id,
        $message ?: null,
        $file_info ? $file_info['file_path'] : null,
        $file_info ? $file_info['original_name'] : null,
        $file_info ? $file_info['file_size'] : null,
        $file_info ? $file_info['mime_type'] : null,
        $file_info ? $file_info['mime_type'] : null,
        $expires_at
    ]);

    $message_id = $pdo->lastInsertId();

    // Link file to message if file was attached
    if ($file_info) {
        $stmt = $pdo->prepare("UPDATE file_attachments SET dm_message_id = ? WHERE id = ?");
        $stmt->execute([$message_id, $file_id]);
    }

    // Update thread's last message timestamp
    $stmt = $pdo->prepare("UPDATE dm_threads SET last_message_at = NOW() WHERE thread_id = ?");
    $stmt->execute([$thread_id]);

    // Record the message send for rate limiting
    recordMessageSend($user_id);

    echo json_encode([
        'success' => true,
        'message' => 'DM sent successfully',
        'message_id' => $pdo->lastInsertId(),
        'expires_at' => $expires_at
    ]);

} catch (Exception $e) {
    error_log("DM send message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send DM']);
}
?>
