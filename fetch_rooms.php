<?php
header('Content-Type: application/json');
require 'config.php';

try {
    $pdo = getDBConnection();

    // Check if is_approved column exists
    $columnExists = false;
    try {
        $stmt = $pdo->query("SELECT is_approved FROM rooms LIMIT 1");
        $columnExists = true;
    } catch (Exception $e) {
        // Column doesn't exist yet
        $columnExists = false;
    }

    if ($columnExists) {
        // Fetch approved rooms with boost and plan info
        $stmt = $pdo->prepare("
            SELECT r.*,
                   u.first_name, u.last_name, u.plan_type,
                   r.is_boosted,
                   CASE
                       WHEN r.is_boosted = 1 AND r.boost_expires_at > NOW() THEN 1
                       ELSE 0
                   END as is_active_boost,
                   r.boost_expires_at,
                   TIMESTAMPDIFF(SECOND, NOW(), r.boost_expires_at) as boost_time_remaining,
                   CASE
                       WHEN r.expires_at IS NOT NULL AND r.expires_at > NOW() THEN 1
                       WHEN r.expires_at IS NULL THEN 1
                       ELSE 0
                   END as is_active_listing
            FROM rooms r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.is_approved = 1
                AND (r.expires_at IS NULL OR r.expires_at > NOW())
            ORDER BY r.city ASC,
                     CASE WHEN r.is_boosted = 1 AND r.boost_expires_at > NOW() THEN 0 ELSE 1 END ASC,
                     r.boost_expires_at DESC,
                     r.created_at DESC
        ");
    } else {
        // Column doesn't exist, show all rooms (backward compatibility)
        $stmt = $pdo->prepare("
            SELECT r.*, u.first_name, u.last_name,
                   0 as is_boosted, 0 as is_active_boost, NULL as boost_expires_at,
                   0 as boost_time_remaining, 1 as is_active_listing,
                   'free' as plan_type
            FROM rooms r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.city ASC, r.created_at DESC
        ");
    }

    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group rooms by city
    $groupedRooms = [];
    $cityStats = [];

    foreach ($rooms as $room) {
        $city = $room['city'];

        if (!isset($groupedRooms[$city])) {
            $groupedRooms[$city] = [
                'boosted' => [],
                'regular' => []
            ];
            $cityStats[$city] = [
                'total' => 0,
                'boosted' => 0
            ];
        }

        if ($room['is_active_boost']) {
            $groupedRooms[$city]['boosted'][] = $room;
            $cityStats[$city]['boosted']++;
        } else {
            $groupedRooms[$city]['regular'][] = $room;
        }

        $cityStats[$city]['total']++;
    }

    // Count active boosts per city (max 6 allowed)
    $boostCounts = [];
    foreach ($groupedRooms as $city => $cityRooms) {
        $boostCounts[$city] = count($cityRooms['boosted']);
    }

    echo json_encode([
        'success' => true,
        'rooms' => $rooms, // Keep original flat array for backward compatibility
        'grouped_rooms' => $groupedRooms,
        'city_stats' => $cityStats,
        'boost_counts' => $boostCounts,
        'max_boosts_per_city' => 6
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch rooms: ' . $e->getMessage()
    ]);
}
?>
