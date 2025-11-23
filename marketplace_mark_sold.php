<?php
// marketplace_mark_sold.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require 'config.php';

$pdo = getDBConnection();

try {
    // Add debugging
    error_log("Mark as sold called. Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Unauthorized: Please log in first."]);
        exit;
    }

    // Get JSON input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);

    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["success" => false, "message" => "Invalid JSON input: " . json_last_error_msg()]);
        exit;
    }

    $itemId = $input['item_id'] ?? 0;
    error_log("Item ID: " . $itemId);

    if (!$itemId || !is_numeric($itemId)) {
        echo json_encode(["success" => false, "message" => "Valid item ID is required."]);
        exit;
    }

    // Check if user owns this item
    $stmt = $pdo->prepare("SELECT user_id, status FROM marketplace_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(["success" => false, "message" => "Item not found."]);
        exit;
    }

    error_log("Item found. Owner: " . $item['user_id'] . ", Status: " . $item['status']);

    if ($item['user_id'] != $userId) {
        echo json_encode(["success" => false, "message" => "You don't have permission to modify this item."]);
        exit;
    }

    // Update item status to sold
    $stmt = $pdo->prepare("UPDATE marketplace_items SET status = 'sold', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$itemId]);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Failed to update item status."]);
        exit;
    }

    $affectedRows = $stmt->rowCount();
    error_log("Update result: " . $affectedRows . " rows affected");

    echo json_encode([
        "success" => true,
        "message" => "Item marked as sold successfully!"
    ]);

} catch (Exception $e) {
    error_log("Mark sold error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
