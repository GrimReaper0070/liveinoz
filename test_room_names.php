<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT room_name, room_name_es FROM chat_rooms WHERE room_type = ? AND state_code = ? AND city_name = ? AND is_active = TRUE');
    $stmt->execute(['hobbies', 'NSW', 'Sydney']);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Database hobbies rooms for NSW Sydney:\n';
    foreach ($rooms as $room) {
        echo '- Room Name: ' . $room['room_name'] . '\n';
        echo '  Spanish: ' . $room['room_name_es'] . '\n';
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . '\n';
}
?>
