<?php
session_start();
// Fake session for testing
$_SESSION['user_id'] = 2;

require_once 'config.php';

$state_code = 'NSW';

// Test each hobbies room individually
$hobbies_rooms = ['Art & Design', 'Gamers & Tech', 'Movies & Series', 'Photography', 'Weed'];

echo "Testing validation for each hobbies room...\n\n";

foreach ($hobbies_rooms as $room_name) {
    echo "Testing room: '$room_name'\n";

    // Test the exact query used in send_message.php validation
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE state_code = ? AND room_name = ? AND is_active = TRUE");
        $stmt->execute([$state_code, $room_name]);

        if ($stmt->fetch()) {
            echo "✓ PASS - Room exists and validation would succeed\n";
        } else {
            echo "✗ FAIL - Room does not exist in database!\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}
?>
