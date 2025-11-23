<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);  // hide PHP errors from output
require 'config.php';

$pdo = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$userId = $_SESSION['user_id'];
$roomId = $_POST['id'] ?? null;

// Validate room ID
if (!$roomId || !is_numeric($roomId)) {
    echo json_encode(["success" => false, "message" => "Valid room ID required"]);
    exit;
}

try {
    // First check if the room exists and belongs to the user
    $checkStmt = $pdo->prepare("SELECT id, photo1, photo2, photo3 FROM rooms WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$roomId, $userId]);

    if ($checkStmt->rowCount() === 0) {
        echo json_encode(["success" => false, "message" => "Room not found or you don't have permission to delete it"]);
        exit;
    }

    $room = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $photos = [$room['photo1'], $room['photo2'], $room['photo3']];

    // Delete the room
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ? AND user_id = ?");
    $stmt->execute([$roomId, $userId]);

    if ($stmt->rowCount() > 0) {
        // Restore the user's posting credit by incrementing available_posts
        $updateStmt = $pdo->prepare("UPDATE users SET available_posts = available_posts + 1 WHERE id = ?");
        $updateStmt->execute([$userId]);

        // Delete associated photo files
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/oznewfinal/uploads/";
        foreach ($photos as $photo) {
            if ($photo) {
                $filePath = $uploadDir . $photo;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        echo json_encode(["success" => true, "message" => "Room deleted successfully"]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete room"]);
        exit;
    }
} catch (PDOException $e) {
    error_log("Delete room error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error deleting room: " . $e->getMessage()]);
    exit;
}
?>
