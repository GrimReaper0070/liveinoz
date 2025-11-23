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
$reason = trim($_POST['reason'] ?? '');

if ($target_user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Target user ID required']);
    exit;
}

if ($user_id === $target_user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot block yourself']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if users exist
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id IN (?, ?)");
    $stmt->execute([$user_id, $target_user_id]);
    if ($stmt->rowCount() !== 2) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if already blocked
    $stmt = $pdo->prepare("
        SELECT id FROM user_blocks
        WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Users are already blocked']);
        exit;
    }

    // Insert block record
    $stmt = $pdo->prepare("
        INSERT INTO user_blocks (blocker_id, blocked_id, reason)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $target_user_id, $reason ?: null]);

    // Delete any pending DM requests between these users
    $stmt = $pdo->prepare("
        DELETE FROM dm_requests
        WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?)
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);

    // Deactivate any active DM threads between these users
    $stmt = $pdo->prepare("
        UPDATE dm_threads
        SET is_active = FALSE
        WHERE (participant1_id = ? AND participant2_id = ?) OR (participant1_id = ? AND participant2_id = ?)
    ");
    $stmt->execute([$user_id, $target_user_id, $target_user_id, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'User blocked successfully'
    ]);

} catch (Exception $e) {
    error_log("Block user error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to block user']);
}
?>
