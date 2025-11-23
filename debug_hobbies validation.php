<?php
session_start();
// Fake session for testing
$_SESSION['user_id'] = 2;

require_once 'config.php';

$state_code = 'NSW';
$city_name = 'Sydney';

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

            // Debug: check what rooms actually exist
            $debug_stmt = $pdo->prepare("SELECT room_name FROM chat_rooms WHERE state_code = ? AND city_name = ? AND room_type = 'hobbies' AND is_active = TRUE");
            $debug_stmt->execute([$state_code, $city_name]);
            $existing_rooms = $debug_stmt->fetchAll(PDO::FETCH_COLUMN);

            echo "Existing hobbies rooms in database:\n";
            foreach ($existing_rooms as $existing) {
                echo "  '$existing'\n";
            }
            echo "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}
?>
