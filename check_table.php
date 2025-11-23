<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("DESCRIBE chat_rooms");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . "\n";
    }
    echo "\nRoom types enum: ";
    $stmt = $pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = 'chat_rooms' AND COLUMN_NAME = 'room_type'");
    $type = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $type['COLUMN_TYPE'] . "\n";

    // Check if hobbies_static exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies_static'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nHobbies static count: " . $result['count'] . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
