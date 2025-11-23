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
$reported_user_id = intval($_POST['reported_user_id'] ?? 0);
$message_id = intval($_POST['message_id'] ?? 0);
$dm_message_id = intval($_POST['dm_message_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
$details = trim($_POST['details'] ?? '');

if ($reported_user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Reported user ID required']);
    exit;
}

if ($user_id === $reported_user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot report yourself']);
    exit;
}

if (empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Report reason required']);
    exit;
}

$valid_reasons = ['harassment', 'spam', 'inappropriate_content', 'threats', 'hate_speech', 'other'];
if (!in_array($reason, $valid_reasons)) {
    echo json_encode(['success' => false, 'message' => 'Invalid report reason']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if users exist
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id IN (?, ?)");
    $stmt->execute([$user_id, $reported_user_id]);
    if ($stmt->rowCount() !== 2) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Verify message exists and belongs to reported user if message_id provided
    if ($message_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM chat_messages WHERE id = ? AND user_id = ?");
        $stmt->execute([$message_id, $reported_user_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Message not found or does not belong to reported user']);
            exit;
        }
    }

    // Verify DM message exists and belongs to reported user if dm_message_id provided
    if ($dm_message_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM dm_messages WHERE id = ? AND sender_id = ?");
        $stmt->execute([$dm_message_id, $reported_user_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'DM message not found or does not belong to reported user']);
            exit;
        }
    }

    // Check if user already reported this (prevent spam reporting)
    $stmt = $pdo->prepare("
        SELECT id FROM reports
        WHERE reporter_id = ? AND reported_user_id = ? AND status = 'pending'
        AND (
            (message_id = ? AND ? > 0) OR
            (dm_message_id = ? AND ? > 0) OR
            (message_id IS NULL AND dm_message_id IS NULL)
        )
    ");
    $stmt->execute([$user_id, $reported_user_id, $message_id, $message_id, $dm_message_id, $dm_message_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already reported this user/content']);
        exit;
    }

    // Insert report
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, reported_user_id, message_id, dm_message_id, reason, details)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $reported_user_id,
        $message_id > 0 ? $message_id : null,
        $dm_message_id > 0 ? $dm_message_id : null,
        $reason,
        $details ?: null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully. Our moderation team will review it.'
    ]);

} catch (Exception $e) {
    error_log("Report user error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
}
?>
