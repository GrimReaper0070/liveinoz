<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Testing Subscription Schema...</h1>";

    // Test users table new columns
    echo "<h2>Testing users table schema...</h2>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $requiredColumns = ['plan_type', 'plan_expires_at', 'active_listings_limit', 'boost_credits'];
    $foundColumns = [];

    foreach ($columns as $column) {
        if (in_array($column['Field'], $requiredColumns)) {
            $foundColumns[] = $column['Field'];
            echo "<p>✅ Found column: {$column['Field']} ({$column['Type']})</p>";
        }
    }

    $missingColumns = array_diff($requiredColumns, $foundColumns);
    if (!empty($missingColumns)) {
        echo "<p>❌ Missing columns: " . implode(', ', $missingColumns) . "</p>";
    }

    // Test rooms table new columns
    echo "<h2>Testing rooms table schema...</h2>";
    $stmt = $pdo->query("DESCRIBE rooms");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $requiredRoomColumns = ['expires_at', 'is_boosted', 'boost_expires_at', 'boost_cost'];
    $foundRoomColumns = [];

    foreach ($columns as $column) {
        if (in_array($column['Field'], $requiredRoomColumns)) {
            $foundRoomColumns[] = $column['Field'];
            echo "<p>✅ Found column: {$column['Field']} ({$column['Type']})</p>";
        }
    }

    $missingRoomColumns = array_diff($requiredRoomColumns, $foundRoomColumns);
    if (!empty($missingRoomColumns)) {
        echo "<p>❌ Missing columns: " . implode(', ', $missingRoomColumns) . "</p>";
    }

    // Test new tables exist
    echo "<h2>Testing new tables...</h2>";
    $tables = ['subscriptions', 'boosts'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
            echo "<p>✅ Table '$table' exists</p>";
        } catch (Exception $e) {
            echo "<p>❌ Table '$table' does not exist</p>";
        }
    }

    // Test payments table
    echo "<h2>Testing payments table...</h2>";
    $stmt = $pdo->query("DESCRIBE payments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $paymentTypeFound = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'payment_type') {
            $paymentTypeFound = true;
            echo "<p>✅ Found payment_type column ({$column['Type']})</p>";
            break;
        }
    }

    if (!$paymentTypeFound) {
        echo "<p>❌ payment_type column not found in payments table</p>";
    }

    // Show some sample data
    echo "<h2>Sample Data Check...</h2>";

    // Check user plan distribution
    $stmt = $pdo->prepare("SELECT plan_type, COUNT(*) as count FROM users GROUP BY plan_type");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>User Plan Distribution:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Plan Type</th><th>User Count</th></tr>";
    foreach ($plans as $plan) {
        echo "<tr><td>{$plan['plan_type']}</td><td>{$plan['count']}</td></tr>";
    }
    echo "</table>";

    // Check if rooms have expiration dates
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_rooms, SUM(CASE WHEN expires_at IS NOT NULL THEN 1 ELSE 0 END) as rooms_with_expiration FROM rooms WHERE is_approved = 1");
    $stmt->execute();
    $roomStats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Room Expiration Status:</h3>";
    echo "<p>Total approved rooms: {$roomStats['total_rooms']}</p>";
    echo "<p>Rooms with expiration: {$roomStats['rooms_with_expiration']}</p>";

    if ($roomStats['total_rooms'] > 0) {
        $percentage = round(($roomStats['rooms_with_expiration'] / $roomStats['total_rooms']) * 100, 1);
        echo "<p>Percentage with expiration: {$percentage}%</p>";
    }

    echo "<h1>✅ Schema Test Complete!</h1>";

} catch (Exception $e) {
    echo "<h1>❌ Schema Test Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
