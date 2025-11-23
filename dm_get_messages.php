<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$thread_id = trim($_GET['thread_id'] ?? '');
$last_id = intval($_GET['last_id'] ?? 0);

if (empty($thread_id)) {
    echo json_encode(['success' => false, 'message' => 'Thread ID required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Verify user is participant in this thread
    $stmt = $pdo->prepare("
        SELECT dt.*, u1.first_name as p1_first, u1.last_name as p1_last,
               u2.first_name as p2_first, u2.last_name as p2_last
        FROM dm_threads dt
        JOIN users u1 ON dt.participant1_id = u1.id
        JOIN users u2 ON dt.participant2_id = u2.id
        WHERE dt.thread_id = ? AND (dt.participant1_id = ? OR dt.participant2_id = ?) AND dt.is_active = TRUE
    ");
    $stmt->execute([$thread_id, $user_id, $user_id]);
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        echo json_encode(['success' => false, 'message' => 'Invalid thread or access denied']);
        exit;
    }

    // Get other participant info
    $other_user_id = ($thread['participant1_id'] == $user_id) ? $thread['participant2_id'] : $thread['participant1_id'];
    $other_user_name = ($thread['participant1_id'] == $user_id) ?
        $thread['p2_first'] . ' ' . $thread['p2_last'] :
        $thread['p1_first'] . ' ' . $thread['p1_last'];

    // Get other user's profile picture
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$other_user_id]);
    $other_user_profile_pic = $stmt->fetchColumn();

    // Debug: get total messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dm_messages");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get messages for this thread
    if ($last_id > 0) {
        // Get new messages since last_id
        $stmt = $pdo->prepare("
            SELECT dm.*, u.first_name, u.last_name, u.profile_picture,
                   CASE WHEN dm.sender_id = ? THEN 1 ELSE 0 END as is_from_me
            FROM dm_messages dm
            JOIN users u ON dm.sender_id = u.id
            WHERE dm.thread_id = ? AND dm.id > ?
            ORDER BY dm.created_at ASC
        ");
        $stmt->execute([$user_id, $thread_id, $last_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Get recent messages (last 50)
        $stmt = $pdo->prepare("
            SELECT dm.*, u.first_name, u.last_name, u.profile_picture,
                   CASE WHEN dm.sender_id = ? THEN 1 ELSE 0 END as is_from_me
            FROM dm_messages dm
            JOIN users u ON dm.sender_id = u.id
            WHERE dm.thread_id = ?
            ORDER BY dm.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$user_id, $thread_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $messages = array_reverse($messages); // Reverse to chronological order
    }

    // Mark messages from other user as read
    if (!empty($messages)) {
        $stmt = $pdo->prepare("
            UPDATE dm_messages
            SET is_read = TRUE
            WHERE thread_id = ? AND sender_id != ? AND is_read = FALSE
        ");
        $stmt->execute([$thread_id, $user_id]);
    }

    echo json_encode([
        'success' => true,
        'thread_id' => $thread_id,
        'other_user' => [
            'id' => $other_user_id,
            'name' => $other_user_name,
            'profile_picture' => $other_user_profile_pic
        ],
        'messages' => $messages,
        'debug' => [
            'total_messages' => $total,
            'thread_id' => $thread_id,
            'user_id' => $user_id
        ]
    ]);

} catch (Exception $e) {
    error_log("DM get messages error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load DM messages']);
}
?>
