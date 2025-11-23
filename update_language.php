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

$lang = $_POST['language'] ?? null;
if (!$lang || !in_array($lang, ['en', 'es'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid language']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Update or insert language preference
    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, language, timezone, notifications_enabled)
        VALUES (?, ?, 'UTC', TRUE)
        ON DUPLICATE KEY UPDATE language = VALUES(language)
    ");
    $stmt->execute([$user_id, $lang]);

    echo json_encode(['success' => true, 'message' => 'Language updated']);

} catch (Exception $e) {
    error_log("Update language error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update language']);
}
?>
