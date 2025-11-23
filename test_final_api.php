<?php
session_start();
// Fake session for testing
$_SESSION['user_id'] = 2;

require_once 'config.php';
header('Content-Type: application/json');

$state_code = 'NSW';
$city_name = 'Sydney';

try {
    $pdo = getDBConnection();

    // Get user language preference (default to en for testing)
    $lang = 'en';

    // Get rooms for the specified state and city
    $stmt = $pdo->prepare("
        SELECT room_name, room_name_es, room_type
        FROM chat_rooms
        WHERE state_code = ? AND city_name = ? AND is_active = TRUE
        ORDER BY
            CASE room_type
                WHEN 'general' THEN 1
                WHEN 'help' THEN 2
                WHEN 'housing' THEN 3
                WHEN 'jobs' THEN 4
                WHEN 'buy_sell' THEN 5
                WHEN 'suggestions' THEN 6
                WHEN 'off_topic' THEN 7
                ELSE 8
            END
    ");
    $stmt->execute([$state_code, $city_name]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response based on language
    $formatted_rooms = array_map(function($room) use ($lang) {
        return [
            'name' => $lang === 'es' ? $room['room_name_es'] : $room['room_name'],
            'type' => $room['room_type'],
            'identifier' => $room['room_name'] // Keep English for internal use
        ];
    }, $rooms);

    // Count by type
    $counts = [];
    foreach ($formatted_rooms as $room) {
        $type = $room['type'];
        $counts[$type] = ($counts[$type] ?? 0) + 1;
    }

    echo "API Response Summary:\n";
    echo "Total rooms: " . count($formatted_rooms) . "\n";
    echo "Counts by type:\n";
    foreach ($counts as $type => $count) {
        echo "- $type: $count\n";
    }

    echo "\nHobbies rooms found:\n";
    $hobbies_rooms = array_filter($formatted_rooms, function($room) {
        return $room['type'] === 'hobbies';
    });

    foreach ($hobbies_rooms as $room) {
        echo "- {$room['name']} ({$room['type']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
