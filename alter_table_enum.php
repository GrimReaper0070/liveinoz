<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Adding 'hobbies' and 'work' to room_type enum...\n";

    // Alter the table to add new enum values
    $pdo->exec("ALTER TABLE chat_rooms MODIFY COLUMN room_type ENUM('general','housing','jobs','buy_sell','help','off_topic','suggestions','hobbies','work') DEFAULT 'general'");

    echo "Table altered successfully!\n";

    // Now update all the empty room_type fields that should be 'hobbies'
    $affected = $pdo->exec("UPDATE chat_rooms SET room_type = 'hobbies' WHERE room_name IN ('Gamers & Tech', 'Movies & Series', 'Photography', 'Art & Design', 'Weed') AND room_type = ''");

    echo "Updated {$affected} Hobbies rooms with correct type\n";

    // Also update the Entrepreneurs room which should be 'work'
    $entrepreneurs = $pdo->exec("UPDATE chat_rooms SET room_type = 'work' WHERE room_name = 'Entrepreneurs' AND room_type = ''");

    echo "Updated {$entrepreneurs} Entrepreneurs rooms with type 'work'\n";

    // Verification
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies'");
    $hobbies = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'work'");
    $work = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Hobbies rooms: {$hobbies['count']} | Work rooms: {$work['count']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
