<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT DISTINCT state_code, city_name FROM chat_rooms WHERE room_type = 'hobbies' AND is_active = TRUE ORDER BY state_code, city_name");
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "States and cities that have hobbies rooms:\n";
    foreach ($states as $state) {
        echo "- {$state['state_code']}: {$state['city_name']}\n";
    }

    echo "\nTotal hobbies rooms by state:\n";
    $count_stmt = $pdo->query("SELECT state_code, COUNT(*) as count FROM chat_rooms WHERE room_type = 'hobbies' AND is_active = TRUE GROUP BY state_code ORDER BY state_code");
    $counts = $count_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($counts as $count) {
        echo "- {$count['state_code']}: {$count['count']} rooms\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . '\n';
}
?>
