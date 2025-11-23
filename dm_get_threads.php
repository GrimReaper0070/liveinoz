<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Get user's language preference
    $stmt = $pdo->prepare("SELECT language FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pref = $stmt->fetch(PDO::FETCH_ASSOC);
    $lang = $pref['language'] ?? 'en';

    // Get active DM threads for this user
    $stmt = $pdo->prepare("
        SELECT
            dt.thread_id,
            dt.last_message_at,
            dt.created_at,
            CASE
                WHEN dt.participant1_id = ? THEN dt.participant2_id
                ELSE dt.participant1_id
            END as other_user_id,
            CASE
                WHEN dt.participant1_id = ? THEN CONCAT(u2.first_name, ' ', u2.last_name)
                ELSE CONCAT(u1.first_name, ' ', u1.last_name)
            END as other_user_name,
            CASE
                WHEN dt.participant1_id = ? THEN u2.profile_picture
                ELSE u1.profile_picture
            END as other_user_profile_picture,
            COALESCE(dm.message, '') as last_message,
            COALESCE(dm.file_name, '') as last_file_name,
            dm.created_at as last_message_time,
            CASE WHEN dm.sender_id = ? THEN 1 ELSE 0 END as is_from_me,
            (SELECT COUNT(*) FROM dm_messages WHERE thread_id = dt.thread_id AND sender_id != ? AND is_read = FALSE) as unread_count
        FROM dm_threads dt
        JOIN users u1 ON dt.participant1_id = u1.id
        JOIN users u2 ON dt.participant2_id = u2.id
        LEFT JOIN dm_messages dm ON dm.id = (
            SELECT id FROM dm_messages
            WHERE thread_id = dt.thread_id
            ORDER BY created_at DESC LIMIT 1
        )
        WHERE (dt.participant1_id = ? OR dt.participant2_id = ?) AND dt.is_active = TRUE
        ORDER BY dt.last_message_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);

    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pending DM requests for this user
    $stmt = $pdo->prepare("
        SELECT
            dr.id as request_id,
            dr.created_at,
            u.first_name,
            u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as requester_name
        FROM dm_requests dr
        JOIN users u ON dr.from_user_id = u.id
        WHERE dr.to_user_id = ? AND dr.status = 'pending'
        ORDER BY dr.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get outgoing pending requests (for UI state management)
    $stmt = $pdo->prepare("
        SELECT to_user_id, status, created_at
        FROM dm_requests
        WHERE from_user_id = ? AND status IN ('pending', 'declined')
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $outgoing_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'threads' => $threads,
        'pending_requests' => $pending_requests,
        'outgoing_requests' => $outgoing_requests,
        'language' => $lang
    ]);

} catch (Exception $e) {
    error_log("DM get threads error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load DM threads']);
}
?>
