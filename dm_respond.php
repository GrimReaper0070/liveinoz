<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = intval($_POST['request_id'] ?? 0);
$action = trim($_POST['action'] ?? ''); // 'accept' or 'decline'

if (!$request_id || !in_array($action, ['accept', 'decline'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get the request and verify ownership
    $stmt = $pdo->prepare("
        SELECT dr.*, u1.first_name as from_first_name, u1.last_name as from_last_name,
               u2.first_name as to_first_name, u2.last_name as to_last_name
        FROM dm_requests dr
        JOIN users u1 ON dr.from_user_id = u1.id
        JOIN users u2 ON dr.to_user_id = u2.id
        WHERE dr.id = ? AND dr.to_user_id = ? AND dr.status = 'pending'
    ");
    $stmt->execute([$request_id, $user_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
        exit;
    }

    // Update the request status
    $stmt = $pdo->prepare("
        UPDATE dm_requests
        SET status = ?, responded_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$action === 'accept' ? 'accepted' : 'declined', $request_id]);

    $response_message = '';
    $thread_id = null;

    if ($action === 'accept') {
        // Check if user already has 10 active DMs
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as dm_count FROM dm_threads
            WHERE (participant1_id = ? OR participant2_id = ?) AND is_active = TRUE
        ");
        $stmt->execute([$user_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['dm_count'] >= 10) {
            // Revert the request status
            $stmt = $pdo->prepare("UPDATE dm_requests SET status = 'pending', responded_at = NULL WHERE id = ?");
            $stmt->execute([$request_id]);

            echo json_encode(['success' => false, 'message' => 'Maximum DM limit reached (10 active conversations)']);
            exit;
        }

        // Check if DM thread already exists between these users
        $participant1_id = min($request['from_user_id'], $request['to_user_id']);
        $participant2_id = max($request['from_user_id'], $request['to_user_id']);

        $stmt = $pdo->prepare("
            SELECT thread_id FROM dm_threads
            WHERE participant1_id = ? AND participant2_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$participant1_id, $participant2_id]);
        $existing_thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_thread) {
            // Use existing thread
            $thread_id = $existing_thread['thread_id'];
            $response_message = 'DM request accepted. Conversation restored.';
        } else {
            // Create new DM thread
            $thread_id = uniqid('dm_', true);
            $stmt = $pdo->prepare("
                INSERT INTO dm_threads (thread_id, participant1_id, participant2_id, created_at, last_message_at, is_active)
                VALUES (?, ?, ?, NOW(), NOW(), TRUE)
            ");
            $stmt->execute([$thread_id, $participant1_id, $participant2_id]);
            $response_message = 'DM request accepted. Conversation started.';
        }
    } else {
        $response_message = 'DM request declined.';
    }

    echo json_encode([
        'success' => true,
        'message' => $response_message,
        'action' => $action,
        'thread_id' => $thread_id,
        'from_user' => [
            'id' => $request['from_user_id'],
            'name' => $request['from_first_name'] . ' ' . $request['from_last_name']
        ]
    ]);

} catch (Exception $e) {
    error_log("DM respond error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to process DM request']);
}
?>
