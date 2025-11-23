<?php
require_once 'config.php';

$moreColors = ['#00e5ff', '#ff3fd8', '#ff8a5c', '#00ffb0', '#5599ff', '#ffa03c'];

$staticHobbiesRooms = [
    ['name' => 'Gamers & Tech', 'color_index' => 0, 'emoji' => '🎮'],
    ['name' => 'Movies & Series', 'color_index' => 1, 'emoji' => '🎬'],
    ['name' => 'Photography', 'color_index' => 2, 'emoji' => '📸'],
    ['name' => 'Art & Design', 'color_index' => 3, 'emoji' => '🎨'],
    ['name' => 'Weed', 'color_index' => 4, 'emoji' => '🌿']
];

$pdo = getDBConnection();

$states = [
    ['code' => 'NSW', 'city' => 'Sydney'],
    ['code' => 'VIC', 'city' => 'Melbourne'],
    ['code' => 'QLD', 'city' => 'Brisbane'],
    ['code' => 'WA', 'city' => 'Perth'],
    ['code' => 'SA', 'city' => 'Adelaide'],
    ['code' => 'TAS', 'city' => 'Hobart'],
    ['code' => 'NT', 'city' => 'Darwin'],
    ['code' => 'ACT', 'city' => 'Canberra']
];

foreach ($states as $state) {
    foreach ($staticHobbiesRooms as $room) {
        $sql = "INSERT IGNORE INTO chat_rooms 
                (state_code, city_name, room_name, room_name_es, room_type, color, emoji, display_name, is_active) 
                VALUES (?, ?, ?, ?, 'hobbies_static', ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $state['code'],
            $state['city'],
            $room['name'],
            $room['name'],
            $moreColors[$room['color_index']],
            $room['emoji'],
            $room['name']
        ]);
    }
}

echo "Hobbies rooms inserted\n";
?>
