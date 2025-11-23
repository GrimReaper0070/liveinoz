<?php
session_start();
// Fake session for testing
$_SESSION['user_id'] = 2;

require_once 'config.php';

// Test sending message to each hobbies room
$state_code = 'NSW';
$city_name = 'Sydney';

try {
    $pdo = getDBConnection();

    // Get hobbies rooms
    $stmt = $pdo->prepare("
        SELECT room_name, room_name_es
        FROM chat_rooms
        WHERE state_code = ? AND city_name = ? AND room_type = 'hobbies' AND is_active = TRUE
        ORDER BY room_name
    ");
    $stmt->execute([$state_code, $city_name]);
    $hobbies_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Testing message send to Hobbies rooms in $state_code, $city_name...\n\n";

    foreach ($hobbies_rooms as $room) {
        echo "Testing room: {$room['room_name']}\n";

        // Simulate the validation in send_message.php
        $room_param = $room['room_name'];
        $state_param = $state_code;

        $check_stmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE state_code = ? AND room_name = ? AND is_active = TRUE");
        $check_stmt->execute([$state_param, $room_param]);
        $room_exists = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($room_exists) {
            echo "✓ Validation successful - Room exists\n";

            // Try to send a test message
            $test_message = "Test message to {$room['room_name']} at " . date('Y-m-d H:i:s');
            $insert_stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, room, state, message) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$_SESSION['user_id'], $room_param, $state_param, $test_message]);

            echo "✓ Test message sent successfully\n";
        } else {
            echo "✗ Validation failed - Room does not exist in database\n";
        }

        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
