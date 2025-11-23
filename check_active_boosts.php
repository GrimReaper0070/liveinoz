<?php
require_once 'config.php';

$pdo = getDBConnection();

echo "=== ACTIVE BOOSTS VERIFICATION ===\n\n";

try {
    // Check boosts table for active boosts
    $stmt = $pdo->prepare('SELECT b.id, b.room_id, b.city, b.expires_at, b.status, r.is_boosted FROM boosts b LEFT JOIN rooms r ON b.room_id = r.id WHERE b.status = "active" ORDER BY b.expires_at DESC');
    $stmt->execute();
    $activeBoosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Active boosts in boosts table:\n";
    foreach ($activeBoosts as $boost) {
        $roomBoosted = $boost['is_boosted'] ? 'YES' : 'NO';
        echo "Boost {$boost['id']}: Room {$boost['room_id']} in {$boost['city']} - expires {$boost['expires_at']} - room.is_boosted: {$roomBoosted}\n";
    }
    echo "\nTotal active boosts in table: " . count($activeBoosts) . "\n\n";

    // Check rooms that are currently marked as boosted
    $stmt = $pdo->prepare('SELECT r.id, r.address, r.city, r.is_boosted, r.boost_expires_at FROM rooms r WHERE r.is_boosted = 1 ORDER BY r.boost_expires_at DESC');
    $stmt->execute();
    $boostedRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Rooms currently marked as boosted:\n";
    foreach ($boostedRooms as $room) {
        echo "Room {$room['id']}: {$room['address']} ({$room['city']}) - expires {$room['boost_expires_at']}\n";
    }
    echo "\nTotal boosted rooms: " . count($boostedRooms) . "\n\n";

    // Check active boosts with expiry check
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM boosts WHERE status = "active" AND expires_at > NOW()');
    $stmt->execute();
    $reallyActive = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Really active boosts (not expired): {$reallyActive['count']}\n\n";

    // Check by city
    $stmt = $pdo->prepare('SELECT city, COUNT(*) as count FROM boosts WHERE status = "active" AND expires_at > NOW() GROUP BY city');
    $stmt->execute();
    $byCity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Active boosts by city:\n";
    foreach ($byCity as $city) {
        echo "- {$city['city']}: {$city['count']} active boosts\n";
    }

    // Summary
    echo "\n=== SUMMARY ===\n";
    echo "Total active boost records: " . count($activeBoosts) . "\n";
    echo "Total rooms marked as boosted: " . count($boostedRooms) . "\n";
    echo "Really active (not expired): {$reallyActive['count']}\n";
    echo "\nThe discrepancy: We have 6 boost records but only 4 rooms are boosted.\n";

    if (count($activeBoosts) > count($boostedRooms)) {
        echo "\nCleanup needed: Some boost records are invalid/duplicates.\n";

        // Find duplicates - group by room_id and find rooms with multiple boosts
        $boostsByRoom = [];
        foreach ($activeBoosts as $boost) {
            $boostsByRoom[$boost['room_id']][] = $boost;
        }

        echo "\n=== CLEANUP PLAN ===\n";
        echo "Rooms with multiple boost records (should only have 1):\n";
        $cleanupSQL = [];
        foreach ($boostsByRoom as $roomId => $boosts) {
            if (count($boosts) > 1) {
                echo "Room {$roomId}: " . count($boosts) . " boost records\n";
                // Keep the one with latest expiry, mark others as invalid
                usort($boosts, function($a, $b) {
                    return strtotime($b['expires_at']) - strtotime($a['expires_at']);
                });
                for ($i = 1; $i < count($boosts); $i++) {
                    $cleanupSQL[] = "UPDATE boosts SET status = 'invalid' WHERE id = {$boosts[$i]['id']};";
                    echo "  - Mark boost {$boosts[$i]['id']} as invalid (duplicate)\n";
                }
            }
        }

        // Find boosts for rooms that aren't actually boosted
        $boostedRoomIds = array_column($boostedRooms, 'id');
        $invalidBoosts = [];
        foreach ($activeBoosts as $boost) {
            if (!in_array($boost['room_id'], $boostedRoomIds)) {
                $invalidBoosts[] = $boost['id'];
                $cleanupSQL[] = "UPDATE boosts SET status = 'invalid' WHERE id = {$boost['id']};";
                echo "Boost {$boost['id']} for room {$boost['room_id']} - room not boosted, marking invalid\n";
            }
        }

        echo "\n=== CLEANUP SQL ===\n";
        foreach ($cleanupSQL as $sql) {
            echo "$sql\n";
        }

        echo "\nExecute this cleanup to fix the inconsistency.\n";
    } else {
        echo "No cleanup needed - data is consistent.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
