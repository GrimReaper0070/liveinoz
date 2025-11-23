<?php

function getPaymentStatistics($pdo) {
    try {
        // Get total revenue from completed payments
        $query = "SELECT
            SUM(amount) as total_revenue,
            COUNT(*) as total_payments,
            COUNT(DISTINCT user_id) as paying_users
            FROM payments
            WHERE status = 'completed'";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $revenueData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get payments for current month
        $query = "SELECT
            SUM(amount) as monthly_revenue,
            COUNT(*) as monthly_payments
            FROM payments
            WHERE status = 'completed'
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
            AND MONTH(created_at) = MONTH(CURRENT_DATE())";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $monthlyData = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_revenue' => $revenueData['total_revenue'] ?? 0,
            'total_payments' => $revenueData['total_payments'] ?? 0,
            'paying_users' => $revenueData['paying_users'] ?? 0,
            'monthly_revenue' => $monthlyData['monthly_revenue'] ?? 0,
            'monthly_payments' => $monthlyData['monthly_payments'] ?? 0
        ];
    } catch (Exception $e) {
        error_log("Error getting payment statistics: " . $e->getMessage());
        return [
            'total_revenue' => 0,
            'total_payments' => 0,
            'paying_users' => 0,
            'monthly_revenue' => 0,
            'monthly_payments' => 0
        ];
    }
}

function getRecentPayments($pdo, $limit = 10) {
    try {
        $query = "SELECT
            p.*,
            u.first_name,
            u.last_name,
            u.email
            FROM payments p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'completed'
            ORDER BY p.created_at DESC
            LIMIT ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting recent payments: " . $e->getMessage());
        return [];
    }
}

function getPaymentStatusCounts($pdo) {
    try {
        $query = "SELECT
            status,
            COUNT(*) as count,
            SUM(amount) as total_amount
            FROM payments
            GROUP BY status";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $statusCounts = [];
        foreach ($results as $row) {
            $statusCounts[$row['status']] = [
                'count' => $row['count'],
                'total_amount' => $row['total_amount']
            ];
        }

        return $statusCounts;
    } catch (Exception $e) {
        error_log("Error getting payment status counts: " . $e->getMessage());
        return [];
    }
}

// Subscription management functions
function getSubscriptionStatistics($pdo) {
    try {
        // Get subscription counts by plan type
        $query = "SELECT
            plan_type,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count
            FROM subscriptions
            GROUP BY plan_type";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $planData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $planStats = [
            'basic' => ['total' => 0, 'active' => 0],
            'premium' => ['total' => 0, 'active' => 0]
        ];

        foreach ($planData as $row) {
            $planStats[$row['plan_type']] = [
                'total' => (int)$row['count'],
                'active' => (int)$row['active_count']
            ];
        }

        // Get subscription revenue
        $query = "SELECT
            SUM(CASE WHEN plan_type = 'basic' THEN amount ELSE 0 END) as basic_revenue,
            SUM(CASE WHEN plan_type = 'premium' THEN amount ELSE 0 END) as premium_revenue,
            SUM(amount) as total_revenue
            FROM subscriptions
            WHERE status = 'active'";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $revenueData = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'plan_stats' => $planStats,
            'basic_revenue' => (float)$revenueData['basic_revenue'],
            'premium_revenue' => (float)$revenueData['premium_revenue'],
            'total_revenue' => (float)$revenueData['total_revenue']
        ];
    } catch (Exception $e) {
        error_log("Error getting subscription statistics: " . $e->getMessage());
        return [
            'plan_stats' => ['basic' => ['total' => 0, 'active' => 0], 'premium' => ['total' => 0, 'active' => 0]],
            'basic_revenue' => 0,
            'premium_revenue' => 0,
            'total_revenue' => 0
        ];
    }
}

// Boost management functions
function getBoostStatistics($pdo) {
    try {
        // Active boosts by city
        $query = "SELECT
            city,
            COUNT(*) as active_boosts
            FROM boosts
            WHERE status = 'active' AND expires_at > NOW()
            GROUP BY city
            ORDER BY active_boosts DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $cityBoosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Boost revenue
        $query = "SELECT
            SUM(cost) as total_boost_revenue,
            COUNT(*) as total_boosts,
            AVG(cost) as average_boost_cost
            FROM boosts
            WHERE status = 'active'";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $revenueData = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'city_boosts' => $cityBoosts,
            'total_boost_revenue' => (float)$revenueData['total_boost_revenue'],
            'total_boosts' => (int)$revenueData['total_boosts'],
            'average_boost_cost' => (float)$revenueData['average_boost_cost']
        ];
    } catch (Exception $e) {
        error_log("Error getting boost statistics: " . $e->getMessage());
        return [
            'city_boosts' => [],
            'total_boost_revenue' => 0,
            'total_boosts' => 0,
            'average_boost_cost' => 0
        ];
    }
}

// Enhanced revenue analytics
function getRevenueAnalytics($pdo) {
    try {
        // Monthly revenue breakdown
        $query = "SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            payment_type,
            SUM(amount) as revenue,
            COUNT(*) as transaction_count
            FROM payments
            WHERE status = 'completed'
            AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), payment_type
            ORDER BY month DESC, payment_type";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Revenue by payment type
        $query = "SELECT
            payment_type,
            SUM(amount) as total_revenue,
            COUNT(*) as transaction_count,
            AVG(amount) as average_transaction
            FROM payments
            WHERE status = 'completed'
            GROUP BY payment_type
            ORDER BY total_revenue DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $revenueByType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top paying users
        $query = "SELECT
            u.first_name,
            u.last_name,
            u.email,
            u.plan_type,
            SUM(p.amount) as total_spent,
            COUNT(p.id) as transaction_count
            FROM users u
            JOIN payments p ON u.id = p.user_id
            WHERE p.status = 'completed'
            GROUP BY u.id, u.first_name, u.last_name, u.email, u.plan_type
            ORDER BY total_spent DESC
            LIMIT 10";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_by_type' => $revenueByType,
            'top_users' => $topUsers
        ];
    } catch (Exception $e) {
        error_log("Error getting revenue analytics: " . $e->getMessage());
        return [
            'monthly_revenue' => [],
            'revenue_by_type' => [],
            'top_users' => []
        ];
    }
}

// User plan distribution
function getUserPlanDistribution($pdo) {
    try {
        $query = "SELECT
            plan_type,
            COUNT(*) as user_count,
            SUM(CASE WHEN plan_expires_at > NOW() THEN 1 ELSE 0 END) as active_plans,
            AVG(active_listings_limit) as avg_listing_limit,
            AVG(boost_credits) as avg_boost_credits
            FROM users
            GROUP BY plan_type
            ORDER BY user_count DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get plan revenue
        $query = "SELECT
            u.plan_type,
            SUM(s.amount) as plan_revenue
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
            GROUP BY u.plan_type";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $planRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $revenueMap = [];
        foreach ($planRevenue as $row) {
            $revenueMap[$row['plan_type']] = (float)$row['plan_revenue'];
        }

        // Merge revenue data
        foreach ($plans as &$plan) {
            $plan['revenue'] = $revenueMap[$plan['plan_type']] ?? 0;
        }

        return $plans;
    } catch (Exception $e) {
        error_log("Error getting user plan distribution: " . $e->getMessage());
        return [];
    }
}

// Active subscriptions with details
function getActiveSubscriptions($pdo) {
    try {
        $query = "SELECT
            s.*,
            u.first_name,
            u.last_name,
            u.email,
            u.active_listings_limit,
            u.boost_credits
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            WHERE s.status = 'active'
            ORDER BY s.current_period_end ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting active subscriptions: " . $e->getMessage());
        return [];
    }
}

// Active boosts with details
function getActiveBoosts($pdo) {
    try {
        $query = "SELECT
            b.*,
            r.address,
            r.suburb,
            r.city,
            u.first_name,
            u.last_name,
            u.email
            FROM boosts b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.user_id = u.id
            WHERE b.status = 'active' AND b.expires_at > NOW()
            ORDER BY b.expires_at ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting active boosts: " . $e->getMessage());
        return [];
    }
}

?>
