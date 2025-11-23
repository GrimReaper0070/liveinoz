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

    // Check if user preferences exist
    $stmt = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if (!$stmt->fetch()) {
        // Create default preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (user_id, language, timezone, notifications_enabled)
            VALUES (?, 'en', 'UTC', TRUE)
        ");
        $stmt->execute([$user_id]);
    }

    echo json_encode(['success' => true, 'message' => 'User preferences initialized']);

} catch (Exception $e) {
    error_log("Init user prefs error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to initialize preferences']);
}
?>
