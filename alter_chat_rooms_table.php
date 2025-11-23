<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Add columns if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM chat_rooms LIKE 'color'");
    if ($columns->rowCount() == 0) {
        $pdo->exec("ALTER TABLE chat_rooms ADD COLUMN color varchar(20) DEFAULT NULL");
    }
    $columns = $pdo->query("SHOW COLUMNS FROM chat_rooms LIKE 'emoji'");
    if ($columns->rowCount() == 0) {
        $pdo->exec("ALTER TABLE chat_rooms ADD COLUMN emoji varchar(50) DEFAULT NULL");
    }
    $columns = $pdo->query("SHOW COLUMNS FROM chat_rooms LIKE 'display_name'");
    if ($columns->rowCount() == 0) {
        $pdo->exec("ALTER TABLE chat_rooms ADD COLUMN display_name varchar(100) DEFAULT NULL");
    }

    // Update enum to include new room types
    $pdo->exec("ALTER TABLE chat_rooms MODIFY COLUMN room_type enum('general','housing','jobs','buy_sell','help','off_topic','suggestions','more','daily_static','fitness_static','hobbies_static') DEFAULT 'general'");

    echo 'Table alterations completed successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
