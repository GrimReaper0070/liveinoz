<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$state_code = trim($_GET['state'] ?? '');
$city_name = trim($_GET['city'] ?? '');

if (empty($state_code) || empty($city_name)) {
    echo json_encode(['success' => false, 'message' => 'State and city parameters required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get user language preference, or use query param if provided
    $lang = $_GET['lang'] ?? null;
    if (!$lang) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT language FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC);
        $lang = $pref['language'] ?? 'en';
    }

    // Get rooms for the specified state and city
    $stmt = $pdo->prepare("
        SELECT room_name, room_name_es, room_type, color, emoji, display_name
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
                WHEN 'more' THEN 8
                WHEN 'daily_static' THEN 9
                WHEN 'fitness_static' THEN 10
                WHEN 'hobbies_static' THEN 11
                ELSE 12
            END
    ");
    $stmt->execute([$state_code, $city_name]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response based on language
    $formatted_rooms = array_map(function($room) use ($lang) {
        if ($lang === 'es') {
            $display_name = $room['room_name_es'];
        } else {
            $display_name = $room['display_name'] ?: $room['room_name'];
        }
        return [
            'name' => $display_name,
            'type' => $room['room_type'],
            'identifier' => $room['room_name'], // Keep room_name for internal use
            'color' => $room['color'],
            'emoji' => $room['emoji']
        ];
    }, $rooms);

    echo json_encode([
        'success' => true,
        'state' => $state_code,
        'city' => $city_name,
        'rooms' => $formatted_rooms,
        'language' => $lang
    ]);

} catch (Exception $e) {
    error_log("Chat rooms API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load rooms']);
}
?>
