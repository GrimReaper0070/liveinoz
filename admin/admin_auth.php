<?php
require_once '../config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
function isAdminAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && 
           isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not authenticated
function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

// Get user statistics
function getUserStatistics($pdo) {
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active users (verified and active)
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1 AND is_verified = 1");
        $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // Admin users
        $stmt = $pdo->query("SELECT COUNT(*) as admins FROM users WHERE role = 'admin'");
        $adminUsers = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];

        // Users registered in the last 30 days
        $stmt = $pdo->query("SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $recentUsers = $stmt->fetch(PDO::FETCH_ASSOC)['recent'];

        // Pending verifications
        $stmt = $pdo->query("SELECT COUNT(*) as pending FROM users WHERE is_verified = 0");
        $pendingUsers = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'admins' => $adminUsers,
            'recent' => $recentUsers,
            'pending' => $pendingUsers
        ];
    } catch (Exception $e) {
        error_log("Error getting user statistics: " . $e->getMessage());
        return [
            'total' => 0,
            'active' => 0,
            'admins' => 0,
            'recent' => 0,
            'pending' => 0
        ];
    }
}

// Get reports statistics
function getReportsStatistics($pdo) {
    try {
        // Total reports
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM reports");
        $totalReports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Pending reports
        $stmt = $pdo->query("SELECT COUNT(*) as pending FROM reports WHERE status = 'pending'");
        $pendingReports = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

        // Resolved reports
        $stmt = $pdo->query("SELECT COUNT(*) as resolved FROM reports WHERE status = 'resolved'");
        $resolvedReports = $stmt->fetch(PDO::FETCH_ASSOC)['resolved'];

        // Reports in last 7 days
        $stmt = $pdo->query("SELECT COUNT(*) as recent FROM reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $recentReports = $stmt->fetch(PDO::FETCH_ASSOC)['recent'];

        return [
            'total' => $totalReports,
            'pending' => $pendingReports,
            'resolved' => $resolvedReports,
            'recent' => $recentReports
        ];
    } catch (Exception $e) {
        error_log("Error getting reports statistics: " . $e->getMessage());
        return [
            'total' => 0,
            'pending' => 0,
            'resolved' => 0,
            'recent' => 0
        ];
    }
}

// Get room statistics
function getRoomStatistics($pdo) {
    try {
        // Check if is_approved column exists
        $columnExists = false;
        try {
            $stmt = $pdo->query("SELECT is_approved FROM rooms LIMIT 1");
            $columnExists = true;
        } catch (Exception $e) {
            $columnExists = false;
        }

        // Total rooms
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
        $totalRooms = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($columnExists) {
            // Approved rooms
            $stmt = $pdo->query("SELECT COUNT(*) as approved FROM rooms WHERE is_approved = 1");
            $approvedRooms = $stmt->fetch(PDO::FETCH_ASSOC)['approved'];

            // Pending rooms
            $stmt = $pdo->query("SELECT COUNT(*) as pending FROM rooms WHERE is_approved = 0");
            $pendingRooms = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
        } else {
            // If column doesn't exist, treat all rooms as approved and no pending
            $approvedRooms = $totalRooms;
            $pendingRooms = 0;
        }

        // Rooms posted in last 7 days
        $stmt = $pdo->query("SELECT COUNT(*) as recent FROM rooms WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $recentRooms = $stmt->fetch(PDO::FETCH_ASSOC)['recent'];

        return [
            'total' => $totalRooms,
            'approved' => $approvedRooms,
            'pending' => $pendingRooms,
            'recent' => $recentRooms
        ];
    } catch (Exception $e) {
        error_log("Error getting room statistics: " . $e->getMessage());
        return [
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'recent' => 0
        ];
    }
}

// Get recent users
function getRecentUsers($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting recent users: " . $e->getMessage());
        return [];
    }
}

// Get pending verifications
function getPendingVerifications($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, created_at FROM users WHERE is_verified = 0 ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting pending verifications: " . $e->getMessage());
        return [];
    }
}

// Get pending reports
function getPendingReports($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                r.id,
                r.reason,
                r.details,
                r.created_at,
                ru.first_name as reported_first_name,
                ru.last_name as reported_last_name,
                ru.email as reported_email,
                rep.first_name as reporter_first_name,
                rep.last_name as reporter_last_name,
                rep.email as reporter_email,
                COALESCE(cm.message, dm.message) as reported_content
            FROM reports r
            LEFT JOIN users ru ON r.reported_user_id = ru.id
            LEFT JOIN users rep ON r.reporter_id = rep.id
            LEFT JOIN chat_messages cm ON r.message_id = cm.id
            LEFT JOIN dm_messages dm ON r.dm_message_id = dm.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting pending reports: " . $e->getMessage());
        return [];
    }
}

// Get pending rooms
function getPendingRooms($pdo, $limit = 10) {
    try {
        // Check if is_approved column exists
        $columnExists = false;
        try {
            $stmt = $pdo->query("SELECT is_approved FROM rooms LIMIT 1");
            $columnExists = true;
        } catch (Exception $e) {
            $columnExists = false;
        }

        if (!$columnExists) {
            // If column doesn't exist yet, no pending rooms
            return [];
        }

        $stmt = $pdo->prepare("
            SELECT r.id, r.address, r.suburb, r.city, r.rent, r.contact_name, r.created_at,
                   u.first_name, u.last_name, u.email
            FROM rooms r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.is_approved = 0
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting pending rooms: " . $e->getMessage());
        return [];
    }
}
?>
