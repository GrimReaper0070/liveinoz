<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Final verification of Hobbies rooms...\n";

    // Check total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM chat_rooms");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total chat_rooms: {$total['total']}\n";

    // Check Hobbies rooms
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies'");
    $stmt->execute();
    $hobbies = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Hobbies rooms (type='hobbies'): {$hobbies['count']}\n";

    // Check rooms with Hobbies names
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM chat_rooms WHERE room_name IN ('Gamers & Tech', 'Movies & Series', 'Photography', 'Art & Design', 'Weed')");
    $stmt->execute();
    $named = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Rooms with Hobbies names: {$named['count']}\n";

    if ($named['count'] > 0) {
        // Show what they look like
        $stmt = $pdo->prepare("SELECT room_name, room_type FROM chat_rooms WHERE room_name IN ('Gamers & Tech', 'Movies & Series', 'Photography', 'Art & Design', 'Weed') LIMIT 5");
        $stmt->execute();
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nSample Hobbies rooms:\n";
        foreach ($samples as $room) {
            echo "- {$room['room_name']}: '{$room['room_type']}'\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
