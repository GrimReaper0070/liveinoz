<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies_static'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'Hobbies static count: ' . $result['count'] . "\n";

    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT room_name FROM chat_rooms WHERE room_type = 'hobbies_static' LIMIT 5");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample:\n";
        foreach ($rooms as $room) {
            echo '- ' . $room['room_name'] . "\n";
        }
    } else {
        echo "No hobbies_static rooms found. Inserting hobbies rooms manually...\n";

        // Manual insert of hobbies rooms
        $moreColors = ['#00e5ff', '#ff3fd8', '#ff8a5c', '#00ffb0', '#5599ff', '#ffa03c'];

        $staticHobbiesRooms = [
            ['name' => 'Gamers & Tech', 'color_index' => 0, 'emoji' => '🎮'],
            ['name' => 'Movies & Series', 'color_index' => 1, 'emoji' => '🎬'],
            ['name' => 'Photography', 'color_index' => 2, 'emoji' => '📸'],
            ['name' => 'Art & Design', 'color_index' => 3, 'emoji' => '🎨'],
            ['name' => 'Weed', 'color_index' => 4, 'emoji' => '🌿']
        ];

        // Get all states
        $stmt = $pdo->query("SELECT code, main_city FROM states");
        $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Try direct insert for one room
        try {
            $pdo->exec("INSERT INTO chat_rooms (state_code, city_name, room_name, room_name_es, room_type, color, emoji, display_name, is_active)
                        VALUES ('NSW', 'Sydney', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', '#00e5ff', '🎮', 'Gamers & Tech', 1)");
            echo "Direct insert succeeded\n";
        } catch (Exception $e) {
            echo "Direct insert error: " . $e->getMessage() . "\n";
        }

        // Check count again
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies_static'");
        $newResult = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "New hobbies static count: " . $newResult['count'] . "\n";

        if ($newResult['count'] > 0) {
            $stmt = $pdo->query("SELECT room_name FROM chat_rooms WHERE room_type = 'hobbies_static' LIMIT 5");
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample inserted rooms:\n";
            foreach ($rooms as $room) {
                echo '- ' . $room['room_name'] . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
