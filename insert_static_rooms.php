<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Get all states and their main cities
    $stmt = $pdo->query("SELECT code, name, main_city FROM states");
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define static rooms data
    $moreColors = ['#00e5ff', '#ff3fd8', '#ff8a5c', '#00ffb0', '#5599ff', '#ffa03c'];

    $staticMoreRooms = [
        ['name' => 'Football', 'identifier' => 'football', 'color_index' => 0, 'emoji' => '⚽'],
        ['name' => 'Meetups', 'identifier' => 'meetups', 'color_index' => 1, 'emoji' => '🧍'],
        ['name' => 'BBQ Meetups', 'identifier' => 'bbq-meetups', 'color_index' => 2, 'emoji' => '🍖'],
        ['name' => 'Mate lovers', 'identifier' => 'mate-lovers', 'color_index' => 3, 'emoji' => '🍵'],
        ['name' => 'Let\'s go to the beach', 'identifier' => 'beach-meetups', 'color_index' => 4, 'emoji' => '🏖️'],
        ['name' => 'Music & Dance', 'identifier' => 'music-dance', 'color_index' => 5, 'emoji' => '🎵'],
        ['name' => 'Latinas United', 'identifier' => 'latinas-united', 'color_index' => 0, 'emoji' => '👩'],
        ['name' => 'Argentinians', 'identifier' => 'argentinians', 'color_index' => 1],
        ['name' => 'Colombians', 'identifier' => 'colombians', 'color_index' => 2],
        ['name' => 'Spaniards', 'identifier' => 'spaniards', 'color_index' => 3],
        ['name' => 'Brazilians', 'identifier' => 'brazilians', 'color_index' => 4],
        ['name' => 'Chileans', 'identifier' => 'chileans', 'color_index' => 5],
        ['name' => 'Mexicans', 'identifier' => 'mexicans', 'color_index' => 0],
        ['name' => 'Peruvians', 'identifier' => 'peruvians', 'color_index' => 1]
    ];

    $staticDailyRooms = [
        ['name' => 'Visas & Docs', 'color_index' => 0],
        ['name' => 'Rides & Transport', 'color_index' => 1],
        ['name' => 'Shopping & Deals', 'color_index' => 2],
        ['name' => 'Lost & Found', 'color_index' => 3],
        ['name' => 'New arrivals', 'color_index' => 4]
    ];

    $staticFitnessRooms = [
        ['name' => 'Gym & Fitness', 'color_index' => 0, 'emoji' => '🏋️'],
        ['name' => 'Running & Marathons', 'color_index' => 1, 'emoji' => '🏃'],
        ['name' => 'Calisthenics / Streets', 'color_index' => 2, 'emoji' => '🤸'],
        ['name' => 'Tennis / Padel', 'color_index' => 3, 'emoji' => '🎾'],
        ['name' => 'Surf', 'color_index' => 4, 'emoji' => '🏄']
    ];

    $staticHobbiesRooms = [
        ['name' => 'Gamers & Tech', 'color_index' => 0, 'emoji' => '🎮'],
        ['name' => 'Movies & Series', 'color_index' => 1, 'emoji' => '🎬'],
        ['name' => 'Photography', 'color_index' => 2, 'emoji' => '📸'],
        ['name' => 'Art & Design', 'color_index' => 3, 'emoji' => '🎨'],
        ['name' => 'Weed', 'color_index' => 4, 'emoji' => '🌿']
    ];

    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO chat_rooms (state_code, city_name, room_name, room_name_es, room_type, color, emoji, display_name, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
            room_name_es = VALUES(room_name_es),
            color = VALUES(color),
            emoji = VALUES(emoji),
            display_name = VALUES(display_name),
            is_active = 1
    ");

    // Insert for each state
    foreach ($states as $state) {
        $stateCode = $state['code'];
        $cityName = $state['main_city'];

        // Insert more rooms
        foreach ($staticMoreRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['identifier'], // room_name as identifier
                $room['name'], // room_name_es display name
                'more',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name'] // display_name
            ]);
        }

        // Insert daily rooms
        foreach ($staticDailyRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name'],
                $room['name'],
                'daily_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name']
            ]);
        }

        // Insert fitness rooms
        foreach ($staticFitnessRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name'],
                $room['name'],
                'fitness_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name']
            ]);
        }

        // Insert hobbies rooms
        foreach ($staticHobbiesRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name'],
                $room['name'],
                'hobbies_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name']
            ]);
        }
    }

    echo 'Static rooms inserted successfully for all states';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
