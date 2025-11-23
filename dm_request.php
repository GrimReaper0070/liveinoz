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
$target_user_id = intval($_POST['target_user_id'] ?? 0);

if (!$target_user_id) {
    echo json_encode(['success' => false, 'message' => 'Target user ID required']);
    exit;
}

if ($target_user_id === $user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send request to yourself']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if target user exists and is active
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = TRUE");
    $stmt->execute([$target_user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if users are already blocked
    $stmt = $pdo->prepare("
        SELECT id FROM user_blocks
        WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cannot send request - user interaction blocked']);
        exit;
    }

    // Check if there's already an active DM thread between these users
    $stmt = $pdo->prepare("
        SELECT id FROM dm_threads
        WHERE ((participant1_id = ? AND participant2_id = ?) OR (participant1_id = ? AND participant2_id = ?))
        AND is_active = TRUE
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'DM conversation already exists']);
        exit;
    }

    // Check if there's already an active request between these users (pending or accepted)
    $stmt = $pdo->prepare("
        SELECT id FROM dm_requests
        WHERE ((from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?))
        AND status IN ('pending', 'accepted')
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);
    $existing_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_request) {
        echo json_encode(['success' => false, 'message' => 'DM request already exists between these users']);
        exit;
    }

    // Create the DM request
    $stmt = $pdo->prepare("
        INSERT INTO dm_requests (from_user_id, to_user_id, status, created_at)
        VALUES (?, ?, 'pending', NOW())
    ");
    $stmt->execute([$user_id, $target_user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'DM request sent successfully',
        'request_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log("DM request error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send DM request']);
}
?>
