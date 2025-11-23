<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT room_name, room_type FROM chat_rooms WHERE room_type = '' OR room_type IS NULL LIMIT 15");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Rooms with empty type:' . "\n";
    foreach ($rooms as $room) {
        echo '- ' . $room['room_name'] . ' (type: "' . $room['room_type'] . '")' . "\n";
    }

    echo "\nTotal count of empty type rooms: ";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = '' OR room_type IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['count'] . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
