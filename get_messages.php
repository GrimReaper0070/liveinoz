<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
error_log("=== GET MESSAGES REQUEST ===");

if (!isset($_SESSION['user_id'])) {
    error_log("Not authenticated");
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$room = trim($_GET['room'] ?? '');
$state = trim($_GET['state'] ?? '');
$last_id = intval($_GET['last_id'] ?? 0);

error_log("Room: $room, State: $state, Last ID: $last_id");

if (empty($room) || empty($state)) {
    error_log("Missing room or state");
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if profile_picture column exists
    $columnsCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $hasProfilePicture = $columnsCheck->rowCount() > 0;
    
    error_log("Profile picture column exists: " . ($hasProfilePicture ? 'yes' : 'no'));
    
    // Build query based on whether profile_picture exists
    $profilePictureField = $hasProfilePicture ? 'u.profile_picture' : 'NULL as profile_picture';
    
    // Check if room exists
    error_log("Checking if room exists...");
    $checkStmt = $pdo->prepare("SELECT id, room_name, state_code FROM chat_rooms WHERE state_code = ? AND room_name = ?");
    $checkStmt->execute([$state, $room]);
    $roomData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$roomData) {
        error_log("Room not found: state=$state, room=$room");
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }
    
    error_log("Room found: " . json_encode($roomData));
    
    // Get messages
    if ($last_id > 0) {
        error_log("Fetching new messages after ID $last_id");
        $stmt = $pdo->prepare("
            SELECT 
                cm.id, 
                cm.message, 
                cm.created_at, 
                cm.user_id,
                u.first_name, 
                u.last_name,
                $profilePictureField
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.room = ? AND cm.state = ? AND cm.id > ?
            ORDER BY cm.created_at ASC, cm.id ASC
        ");
        $stmt->execute([$room, $state, $last_id]);
    } else {
        error_log("Fetching last 50 messages");
        $stmt = $pdo->prepare("
            SELECT 
                cm.id, 
                cm.message, 
                cm.created_at, 
                cm.user_id,
                u.first_name, 
                u.last_name,
                $profilePictureField
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.room = ? AND cm.state = ?
            ORDER BY cm.created_at DESC, cm.id DESC
            LIMIT 50
        ");
        $stmt->execute([$room, $state]);
    }
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($messages) . " messages");
    
    // Reverse if initial load
    if ($last_id == 0) {
        $messages = array_reverse($messages);
    }
    
    // Log first message for debugging
    if (count($messages) > 0) {
        error_log("First message: " . json_encode($messages[0]));
    }
    
    echo json_encode([
        'success' => true, 
        'messages' => $messages,
        'room' => $room,
        'state' => $state,
        'count' => count($messages)
    ]);
    
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
?>