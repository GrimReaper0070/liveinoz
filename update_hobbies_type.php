<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Update rooms with empty type that are hobbies
    $hobbyNames = [
        'Gamers & Tech',
        'Movies & Series',
        'Photography',
        'Art & Design',
        'Weed'
    ];

    $inClause = str_repeat('?,', count($hobbyNames) - 1) . '?';

    $sql = "UPDATE chat_rooms SET room_type = 'hobbies_static' WHERE room_name IN ($inClause) AND (room_type = '' OR room_type IS NULL)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($hobbyNames);

    echo "Updated rooms with hobbies_static type.\n";

    // Verify
    $stmt = $pdo->prepare("SELECT room_name, room_type FROM chat_rooms WHERE room_name IN ($inClause)");
    $stmt->execute($hobbyNames);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Updated rooms:\n";
    foreach ($rooms as $room) {
        echo "- {$room['room_name']}: {$room['room_type']}\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
