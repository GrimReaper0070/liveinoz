<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Check rooms for ACT
    $stmt = $pdo->query("SELECT state_code, city_name, room_name FROM chat_rooms WHERE state_code = 'ACT'");
    $act_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ACT Rooms:\n";
    foreach ($act_rooms as $room) {
        echo "- {$room['state_code']} | {$room['city_name']} | {$room['room_name']}\n";
    }

    // Check all rooms count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "\nTotal rooms: {$total['count']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
