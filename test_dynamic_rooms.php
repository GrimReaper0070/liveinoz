<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Test: Get sample rooms
    $stmt = $pdo->query("
        SELECT room_name, room_type, display_name, color, emoji
        FROM chat_rooms
        WHERE room_type IN ('more', 'daily_static', 'fitness_static', 'hobbies_static')
        AND state_code = 'NSW'
        LIMIT 10
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Sample dynamic rooms from NSW:\n";
    foreach ($rooms as $room) {
        echo "- {$room['room_type']}: {$room['display_name']} (ID: {$room['room_name']}, Color: {$room['color']}, Emoji: {$room['emoji']})\n";
    }

    // Count by type
    echo "\nCount by type:\n";
    $stmt = $pdo->query("SELECT room_type, COUNT(*) as count FROM chat_rooms WHERE room_type IN ('more', 'daily_static', 'fitness_static', 'hobbies_static') GROUP BY room_type");
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($counts as $count) {
        echo "- {$count['room_type']}: {$count['count']} rooms\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
