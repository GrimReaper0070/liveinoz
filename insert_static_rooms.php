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
        ['name_en' => 'Football', 'name_es' => 'Fútbol', 'identifier' => 'football', 'color_index' => 0, 'emoji' => '⚽'],
        ['name_en' => 'Meetups', 'name_es' => 'Encuentros', 'identifier' => 'meetups', 'color_index' => 1, 'emoji' => '🧍'],
        ['name_en' => 'BBQ Meetups', 'name_es' => 'Encuentros BBQ', 'identifier' => 'bbq-meetups', 'color_index' => 2, 'emoji' => '🍖'],
        ['name_en' => 'Mate lovers', 'name_es' => 'Amantes del mate', 'identifier' => 'mate-lovers', 'color_index' => 3, 'emoji' => '🍵'],
        ['name_en' => 'Let\'s go to the beach', 'name_es' => 'Vamos a la playa', 'identifier' => 'beach-meetups', 'color_index' => 4, 'emoji' => '🏖️'],
        ['name_en' => 'Music & Dance', 'name_es' => 'Música y Baile', 'identifier' => 'music-dance', 'color_index' => 5, 'emoji' => '🎵'],
        ['name_en' => 'Latinas United', 'name_es' => 'Latinas Unidas', 'identifier' => 'latinas-united', 'color_index' => 0, 'emoji' => '👩'],
        ['name_en' => 'Argentinians', 'name_es' => 'Argentinos', 'identifier' => 'argentinians', 'color_index' => 1, 'emoji' => '[AR]'],
        ['name_en' => 'Colombians', 'name_es' => 'Colombianos', 'identifier' => 'colombians', 'color_index' => 2, 'emoji' => '[CO]'],
        ['name_en' => 'Spanish', 'name_es' => 'Españoles', 'identifier' => 'spanish', 'color_index' => 3, 'emoji' => '[ES]'],
        ['name_en' => 'Brazilians', 'name_es' => 'Brasileños', 'identifier' => 'brazilians', 'color_index' => 4, 'emoji' => '[BR]'],
        ['name_en' => 'Chileans', 'name_es' => 'Chilenos', 'identifier' => 'chileans', 'color_index' => 5, 'emoji' => '[CL]'],
        ['name_en' => 'Mexicans', 'name_es' => 'Mexicanos', 'identifier' => 'mexicans', 'color_index' => 0, 'emoji' => '[MX]'],
        ['name_en' => 'Peruvians', 'name_es' => 'Peruanos', 'identifier' => 'peruvians', 'color_index' => 1, 'emoji' => '[PE]']
    ];

    $staticDailyRooms = [
        ['name_en' => 'Visas & Docs', 'name_es' => 'Visas y Documentos', 'color_index' => 0],
        ['name_en' => 'Rides & Transport', 'name_es' => 'Viajes y Transporte', 'color_index' => 1],
        ['name_en' => 'Shopping & Deals', 'name_es' => 'Compras y Ofertas', 'color_index' => 2],
        ['name_en' => 'Lost & Found', 'name_es' => 'Objetos Perdidos', 'color_index' => 3],
        ['name_en' => 'New arrivals', 'name_es' => 'Nuevos llegados', 'color_index' => 4]
    ];

    $staticFitnessRooms = [
        ['name_en' => 'Gym & Fitness', 'name_es' => 'Gimnasio y Fitness', 'color_index' => 0, 'emoji' => '🏋️'],
        ['name_en' => 'Running & Marathons', 'name_es' => 'Correr y Maratones', 'color_index' => 1, 'emoji' => '🏃'],
        ['name_en' => 'Calisthenics / Streets', 'name_es' => 'Calistenia / Calles', 'color_index' => 2, 'emoji' => '🤸'],
        ['name_en' => 'Tennis / Padel', 'name_es' => 'Tenis / Padel', 'color_index' => 3, 'emoji' => '🎾'],
        ['name_en' => 'Surf', 'name_es' => 'Surf', 'color_index' => 4, 'emoji' => '🏄']
    ];

    $staticHobbiesRooms = [
        ['name_en' => 'Gamers & Tech', 'name_es' => 'Gamers y Tecnología', 'color_index' => 0, 'emoji' => '🎮'],
        ['name_en' => 'Movies & Series', 'name_es' => 'Películas y Series', 'color_index' => 1, 'emoji' => '🎬'],
        ['name_en' => 'Photography', 'name_es' => 'Fotografía', 'color_index' => 2, 'emoji' => '📸'],
        ['name_en' => 'Art & Design', 'name_es' => 'Arte y Diseño', 'color_index' => 3, 'emoji' => '🎨'],
        ['name_en' => 'Weed', 'name_es' => 'Marihuana', 'color_index' => 4, 'emoji' => '🌿']
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
                $room['name_es'], // room_name_es Spanish translation
                'more',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name_en'] // display_name English name
            ]);
        }

        // Insert daily rooms
        foreach ($staticDailyRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name_en'], // room_name English
                $room['name_es'], // room_name_es Spanish
                'daily_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name_en'] // display_name English
            ]);
        }

        // Insert fitness rooms
        foreach ($staticFitnessRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name_en'], // room_name English
                $room['name_es'], // room_name_es Spanish
                'fitness_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name_en'] // display_name English
            ]);
        }

        // Insert hobbies rooms
        foreach ($staticHobbiesRooms as $room) {
            $stmt->execute([
                $stateCode,
                $cityName,
                $room['name_en'], // room_name English
                $room['name_es'], // room_name_es Spanish
                'hobbies_static',
                $moreColors[$room['color_index']],
                $room['emoji'] ?? null,
                $room['name_en'] // display_name English
            ]);
        }
    }

    echo 'Static rooms inserted successfully for all states';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
