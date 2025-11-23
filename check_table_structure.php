<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Checking chat_rooms table structure...\n";

    // Get column info for room_type
    $stmt = $pdo->prepare("DESCRIBE chat_rooms");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table columns:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} | Default: {$col['Default']} | Null: {$col['Null']}\n";
    }

    echo "\nChecking room_type values in existing records:\n";
    $stmt = $pdo->query("SELECT DISTINCT room_type, COUNT(*) as count FROM chat_rooms GROUP BY room_type");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($types as $type) {
        echo "- Type: '{$type['room_type']}' - Count: {$type['count']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
