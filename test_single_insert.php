<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Testing single INSERT...\n";

    // Try inserting one Hobbies room manually
    $pdo->exec("INSERT INTO chat_rooms (state_code, city_name, room_name, room_name_es, room_type, is_active)
                VALUES ('NSW', 'Sydney', 'Test Hobbies', 'Prueba Hobbies', 'hobbies', 1)");

    // Check what was inserted
    $stmt = $pdo->query("SELECT room_name, room_type FROM chat_rooms WHERE room_name = 'Test Hobbies'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Inserted room: {$result['room_name']} with type: '{$result['room_type']}'\n";
    } else {
        echo "No room found after insert!\n";
    }

    // Clean up
    $pdo->exec("DELETE FROM chat_rooms WHERE room_name = 'Test Hobbies'");

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
