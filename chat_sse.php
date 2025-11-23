<?php
session_start();
require_once 'config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Disable output buffering
if (ob_get_level()) ob_end_clean();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Not authenticated']) . "\n\n";
    exit;
}

$user_id = $_SESSION['user_id'];
$room = trim($_GET['room'] ?? '');
$state = trim($_GET['state'] ?? '');
$last_id = intval($_GET['last_id'] ?? 0);

if (empty($room) || empty($state)) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Missing room or state parameter']) . "\n\n";
    exit;
}

try {
    $pdo = getDBConnection();

    // Send initial connection confirmation
    echo "event: connected\n";
    echo "data: " . json_encode(['status' => 'connected', 'room' => $room, 'state' => $state]) . "\n\n";
    flush();

    $start_time = time();
    $last_check = 0;

    while (true) {
        // Check for new messages every 5 seconds (reduced frequency)
        if (time() - $last_check >= 5) {
            $last_check = time();

            // Query for new messages
            if ($last_id > 0) {
                $stmt = $pdo->prepare("
                    SELECT cm.id, cm.message, cm.created_at, u.first_name, u.last_name
                    FROM chat_messages cm
                    JOIN users u ON cm.user_id = u.id
                    WHERE cm.room = ? AND cm.state = ? AND cm.id > ?
                    ORDER BY cm.created_at ASC
                ");
                $stmt->execute([$room, $state, $last_id]);
            } else {
                // Initial load - get recent messages
                $stmt = $pdo->prepare("
                    SELECT cm.id, cm.message, cm.created_at, u.first_name, u.last_name
                    FROM chat_messages cm
                    JOIN users u ON cm.user_id = u.id
                    WHERE cm.room = ? AND cm.state = ?
                    ORDER BY cm.created_at DESC
                    LIMIT 50
                ");
                $stmt->execute([$room, $state]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $messages = array_reverse($messages); // Reverse to chronological order
            }

            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($messages)) {
                foreach ($messages as $msg) {
                    echo "event: message\n";
                    echo "data: " . json_encode([
                        'id' => $msg['id'],
                        'message' => $msg['message'],
                        'created_at' => $msg['created_at'],
                        'first_name' => $msg['first_name'],
                        'last_name' => $msg['last_name']
                    ]) . "\n\n";

                    $last_id = max($last_id, $msg['id']);
                }
                flush();
            } else {
                // Send heartbeat to keep connection alive
                echo "event: heartbeat\n";
                echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
                flush();
            }
        }

        // Check for connection timeout (30 minutes max)
        if (time() - $start_time > 1800) {
            echo "event: timeout\n";
            echo "data: " . json_encode(['message' => 'Connection timeout']) . "\n\n";
            break;
        }

        // Small sleep to prevent CPU hogging
        usleep(500000); // 0.5 seconds

        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
    }

} catch (Exception $e) {
    error_log("SSE error: " . $e->getMessage());
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Server error occurred']) . "\n\n";
}
?>
