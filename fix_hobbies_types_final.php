<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Fixing Hobbies room types using direct SQL execution...\n";

    // Use direct exec() which returns affected rows
    $affected = $pdo->exec("UPDATE chat_rooms SET room_type = 'hobbies' WHERE room_name IN ('Gamers & Tech', 'Movies & Series', 'Photography', 'Art & Design', 'Weed')");

    echo "Updated {$affected} Hobbies rooms\n";

    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies'");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total rooms with type 'hobbies': {$count['count']}\n";

    // List a few to confirm
    $stmt = $pdo->query("SELECT room_name FROM chat_rooms WHERE room_type = 'hobbies' LIMIT 5");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Sample rooms fixed: ";
    foreach ($rooms as $room) {
        echo "{$room['room_name']}, ";
    }
    echo "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
