<?php
// Debug session issues
session_start();

echo "<h1>🔍 Session Debug</h1>";
echo "<p>Checking current session state...</p>";

echo "<h2>Session Info:</h2>";
echo "<ul>";
echo "<li>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</li>";
echo "<li>Session ID: " . session_id() . "</li>";
echo "<li>Session Status: " . session_status() . "</li>";
echo "</ul>";

// Test database directly
require_once 'config.php';

// Get the actual user ID from check_auth.php
$authResponse = json_decode(file_get_contents('http://localhost/oznewfinal/check_auth.php'), true);
$userId = $authResponse['authenticated'] ? $authResponse['user']['id'] : 2; // Default to user 2

try {
    $pdo = getDBConnection();

    echo "<h2>Database User Status (using session user):</h2>";
    $stmt = $pdo->prepare("SELECT plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<ul>";
        echo "<li>Database Plan: {$user['plan_type']}</li>";
        echo "<li>Database Limit: {$user['active_listings_limit']}</li>";
        echo "<li>Database Expires: {$user['plan_expires_at']}</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>User not found in database!</p>";
    }

    // Just get current stats manually instead of including file (which causes header conflicts)
    echo "<h2>User Stats Query (SQL Debug):</h2>";
    $stmt = $pdo->prepare("
        SELECT u.plan_type, u.plan_expires_at, u.active_listings_limit,
               u.room_posts_count, u.boost_credits,
               COUNT(r.id) as active_rooms,
               SUM(CASE WHEN r.is_boosted = 1 AND r.boost_expires_at > NOW() THEN 1 ELSE 0 END) as active_boosts
        FROM users u
        LEFT JOIN rooms r ON u.id = r.user_id AND r.is_approved = 1
            AND (r.expires_at IS NULL OR r.expires_at > NOW())
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo json_encode($stats, JSON_PRETTY_PRINT);
    echo "</pre>";

    // Check what the API would return
    $planType = $stats['plan_type'] ?: 'free';
    $activeRooms = (int)$stats['active_rooms'];
    $listingsLimit = (int)$stats['active_listings_limit'] ?: 1;

    $planExpired = false;
    if ($stats['plan_expires_at'] && strtotime($stats['plan_expires_at']) < time()) {
        $planExpired = true;
        $planType = 'free';
        $listingsLimit = 1;
    }

    echo "<h2>What Dashboard Should Show:</h2>";
    echo "<ul>";
    echo "<li>Plan Type: <strong>{$planType}</strong></li>";
    echo "<li>Active Rooms: <strong>{$activeRooms}</strong></li>";
    echo "<li>Listings Limit: <strong>{$listingsLimit}</strong></li>";
    echo "<li>Display: <strong>{$activeRooms}/{$listingsLimit}</strong></li>";
    echo "<li>Can Post More: <strong>" . (($activeRooms < $listingsLimit) ? 'YES' : 'NO') . "</strong></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Manual Database Check (All Users):</h2>";
try {
    $stmt = $pdo->query("SELECT id, plan_type, active_listings_limit, plan_expires_at, first_name FROM users LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Plan</th><th>Limit</th><th>Expires</th></tr>";
    foreach ($users as $u) {
        echo "<tr>
        <td>{$u['id']}</td>
        <td>{$u['first_name']}</td>
        <td>{$u['plan_type']}</td>
        <td>{$u['active_listings_limit']}</td>
        <td>{$u['plan_expires_at']}</td>
        </tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
