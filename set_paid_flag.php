<?php
header('Content-Type: application/json');
require 'config.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

try {
    $pdo = getDBConnection();

    // Add 1 credit to user's available posts
    $stmt = $pdo->prepare("UPDATE users SET available_posts = available_posts + 1 WHERE id = ?");
    $stmt->execute([$userId]);

    echo json_encode(["success" => true, "message" => "Flag set"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
