<?php
session_start();
// Fake session for testing
$_SESSION['user_id'] = 2;

require_once 'config.php';

// Test if room exists in VIC Melbourne instead of NSW Sydney
$state_code = 'VIC';
$room_name = 'Art & Design';

echo "Testing room '$room_name' in $state_code...\n";

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE state_code = ? AND room_name = ? AND is_active = TRUE");
    $stmt->execute([$state_code, $room_name]);

    if ($stmt->fetch()) {
        echo "✓ PASS - Room exists in $state_code\n";
    } else {
        echo "✗ FAIL - Room does not exist in $state_code\n";

        // Check what hotels rooms exist in VIC
        $check_stmt = $pdo->prepare("SELECT room_name FROM chat_rooms WHERE state_code = ? AND city_name = (SELECT main_city FROM states WHERE code = ?) AND room_type = 'hobbies' AND is_active = TRUE");
        $check_stmt->execute([$state_code, $state_code]);
        $existing = $check_stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "Hobbies rooms that exist in $state_code:\n";
        foreach ($existing as $room) {
            echo "  '$room'\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
