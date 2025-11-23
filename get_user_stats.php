<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require 'config.php';

$pdo = getDBConnection();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Unauthorized: Please log in first."]);
    exit;
}

try {
    // Get user's plan and active room counts
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
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $planType = $user['plan_type'] ?: 'free';
        $activeRooms = (int)$user['active_rooms'];
        $listingsLimit = (int)$user['active_listings_limit'] ?: 1;
        $boostCredits = (int)$user['boost_credits'];
        $totalPosts = (int)$user['room_posts_count'];

        // Check if plan is expired
        $planExpired = false;
        if ($user['plan_expires_at'] && strtotime($user['plan_expires_at']) < time()) {
            $planExpired = true;
            $planType = 'free';
            $listingsLimit = 1;
            error_log("Plan expired for user {$userId} at " . $user['plan_expires_at']);
        } else {
            error_log("Plan active for user {$userId}: {$planType}, limit: {$listingsLimit}, expires: " . ($user['plan_expires_at'] ?? 'never'));
        }

        $remainingSlots = max(0, $listingsLimit - $activeRooms);
        $canPostMore = $remainingSlots > 0;

        echo json_encode([
            "success" => true,
            "stats" => [
                "plan_type" => $planType,
                "plan_expired" => $planExpired,
                "active_rooms" => $activeRooms,
                "listings_limit" => $listingsLimit,
                "remaining_slots" => $remainingSlots,
                "can_post_more" => $canPostMore,
                "boost_credits" => $boostCredits,
                "total_posts" => $totalPosts,
                "active_boosts" => (int)$user['active_boosts']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (Exception $e) {
    error_log("Get user stats error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error retrieving stats"]);
}
?>
