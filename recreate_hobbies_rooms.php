<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Recreating Hobbies rooms with correct types...\n";

    // First, delete the existing Hobbies rooms with empty types
    $deleteCount = $pdo->exec("DELETE FROM chat_rooms WHERE room_name IN ('Gamers & Tech', 'Movies & Series', 'Photography', 'Art & Design', 'Weed') AND (room_type IS NULL OR room_type = '')");

    echo "Deleted {$deleteCount} incorrectly created Hobbies rooms\n";

    // Now re-insert with correct types
    $hobbiesRooms = [
        ['Gamers & Tech', 'Jugadores y Tecnología'],
        ['Movies & Series', 'Películas y Series'],
        ['Photography', 'Fotografía'],
        ['Art & Design', 'Arte y Diseño'],
        ['Weed', 'Marihuana']
    ];

    $states = [
        ['NSW', 'Sydney'],
        ['VIC', 'Melbourne'],
        ['QLD', 'Brisbane'],
        ['WA', 'Perth'],
        ['SA', 'Adelaide'],
        ['TAS', 'Hobart'],
        ['NT', 'Darwin'],
        ['ACT', 'Canberra']
    ];

    $insertStmt = $pdo->prepare("
        INSERT INTO chat_rooms (state_code, city_name, room_name, room_name_es, room_type, is_active)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $insertedCount = 0;
    foreach ($states as $state) {
        foreach ($hobbiesRooms as $room) {
            $insertStmt->execute([$state[0], $state[1], $room[0], $room[1], 'hobbies', 1]);
            $insertedCount++;
        }
    }

    echo "Successfully inserted {$insertedCount} Hobbies rooms with correct types\n";

    // Final verification
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies'");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total rooms with type 'hobbies': {$count['count']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
