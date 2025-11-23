<?php
// test_events.php - Test if events system is working

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful\n";

    // Check if events table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'events'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Events table exists\n";
    } else {
        echo "❌ Events table does not exist\n";
        exit;
    }

    // Check if event_photos table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'event_photos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Event photos table exists\n";
    } else {
        echo "❌ Event photos table does not exist\n";
    }

    // Count events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total events in database: " . $result['count'] . "\n";

    // Count approved events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status = 'approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Approved events: " . $result['count'] . "\n";

    // Show sample events
    $stmt = $pdo->query("SELECT id, title, city, status FROM events LIMIT 5");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($events) > 0) {
        echo "\n📋 Sample events:\n";
        foreach ($events as $event) {
            echo "- {$event['title']} ({$event['city']}) - {$event['status']}\n";
        }
    }

    echo "\n🎉 Events system test complete!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
