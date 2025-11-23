<?php
require_once '../config.php';
require_once 'admin_auth.php';
require_once 'admin_functions.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle subscription actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $subscriptionId = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    try {
        if ($action === 'cancel_subscription' && $subscriptionId > 0) {
            // Update subscription status to canceled
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'canceled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$subscriptionId]);

            // Reset user back to free plan
            $stmt = $pdo->prepare("
                UPDATE users SET plan_type = 'free', active_listings_limit = 1, boost_credits = 1, plan_expires_at = NULL
                WHERE id = (SELECT user_id FROM subscriptions WHERE id = ?)
            ");
            $stmt->execute([$subscriptionId]);

            $message = 'Subscription canceled and user reverted to free plan.';
            $messageType = 'success';

        } elseif ($action === 'change_plan' && $userId > 0) {
            $newPlan = isset($_POST['new_plan']) ? $_POST['new_plan'] : '';
            $plans = ['free', 'basic', 'premium'];

            if (in_array($newPlan, $plans)) {
                $planLimits = [
                    'free' => ['listings' => 1, 'boosts' => 1],
                    'basic' => ['listings' => 3, 'boosts' => 1],
                    'premium' => ['listings' => 10, 'boosts' => 0]
                ];

                $limits = $planLimits[$newPlan];

                if ($newPlan === 'free') {
                    // Cancel any existing subscription and set to free
                    $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'canceled' WHERE user_id = ? AND status = 'active'");
                    $stmt->execute([$userId]);

                    $stmt = $pdo->prepare("UPDATE users SET plan_type = 'free', active_listings_limit = ?, boost_credits = ?, plan_expires_at = NULL WHERE id = ?");
                    $stmt->execute([$limits['listings'], $limits['boosts'], $userId]);
                } else {
                    // For basic/premium, we would typically create a new subscription via Stripe
                    // For admin changes, we'll update the user directly
                    $nextMonth = date('Y-m-d H:i:s', strtotime('+1 month'));

                    $stmt = $pdo->prepare("UPDATE users SET plan_type = ?, active_listings_limit = ?, boost_credits = ?, plan_expires_at = ? WHERE id = ?");
                    $stmt->execute([$newPlan, $limits['listings'], $limits['boosts'], $nextMonth, $userId]);
                }

                $message = "User plan changed to " . ucfirst($newPlan) . " successfully.";
                $messageType = 'success';
            }
        }
    } catch (Exception $e) {
        $message = 'Error processing request: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get active subscriptions
try {
    $activeSubscriptions = getActiveSubscriptions($pdo);
} catch (Exception $e) {
    $activeSubscriptions = [];
}

// Get user plan distribution
try {
    $userPlanStats = getUserPlanDistribution($pdo);
} catch (Exception $e) {
    $userPlanStats = [];
}

// Get recent subscription activities
try {
    $query = "SELECT
        s.*,
        u.first_name,
        u.last_name,
        u.email
        FROM subscriptions s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.created_at DESC
        LIMIT 20";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $recentSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentSubscriptions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management - Live in Oz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(90deg, #0f3460, #16213e);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logo h1 {
            font-size: 24px;
            color: #00eeff;
        }

        .logo p {
            font-size: 14px;
            opacity: 0.8;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            margin-bottom: 5px;
        }

        .user-info a {
            color: #ff4d4d;
            text-decoration: none;
            font-size: 14px;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 250px;
            background: #16213e;
            padding: 20px 0;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 5px;
        }

        .sidebar ul li a {
            display: block;
            padding: 15px 30px;
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar ul li a:hover, .sidebar ul li a.active {
            background: rgba(0, 238, 255, 0.1);
            color: #00eeff;
            border-left: 3px solid #00eeff;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .page-title {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 28px;
        }

        .page-title p {
            color: #aaa;
            margin-top: 5px;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #00eeff;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #fff;
        }

        .stat-card .description {
            font-size: 14px;
            color: #aaa;
            margin-top: 10px;
        }

        .subscriptions-table {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
        }

        .subscriptions-table h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #00eeff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #2c3e50;
        }

        th {
            color: #00eeff;
            font-weight: 600;
        }

        tr:hover {
            background: rgba(0, 238, 255, 0.05);
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }

        .cancel-btn {
            background: #f44336;
            color: white;
        }

        .change-btn {
            background: #2196f3;
            color: white;
            margin-left: 5px;
        }

        .plan-select {
            display: inline-block;
            background: #333;
            color: white;
            padding: 5px;
            border-radius: 3px;
            font-size: 12px;
        }

        .basic-badge {
            background: #ff9800;
            color: #000;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .premium-badge {
            background: #9c27b0;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .free-badge {
            background: #666;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 10px 0;
            }

            .sidebar ul li a {
                padding: 10px 20px;
            }

            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>Subscription Management</p>
        </div>
        <div class="user-info">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                 <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">User Management</a></li>
                <li><a href="rooms.php">Room Management</a></li>
                 <li><a href="subscriptions.php" class="active">Subscriptions</a></li>
                <li><a href="marketplace_review.php">🛍️ Marketplace</a></li>
                <li><a href="events_review.php">🎉 Party & Events</a></li>
                <li><a href="blog.php">📝 Blog</a></li>
                <li><a href="whatsapp.php">💬 WhatsApp</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2>Subscription Management</h2>
                    <p>Monitor and manage user subscriptions and plans</p>
                </div>
                <a href="dashboard.php" style="background: linear-gradient(45deg, #00eeff, #0088ff); color: black; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: bold;">← Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="stats-container">
                <?php if (count($userPlanStats) > 0): ?>
                    <?php foreach ($userPlanStats as $plan): ?>
                        <div class="stat-card">
                            <h3><?php echo ucfirst($plan['plan_type']); ?> Plan Users</h3>
                            <div class="number"><?php echo $plan['user_count']; ?></div>
                            <div class="description"><?php echo $plan['active_plans']; ?> active, $<?php echo number_format($plan['revenue'], 2); ?> revenue</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="subscriptions-table">
                <h3>Active Subscriptions</h3>
                <?php if (count($activeSubscriptions) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Current Period</th>
                                <th>Next Renewal</th>
                                <th>Monthly Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeSubscriptions as $subscription): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($subscription['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($subscription['plan_type'] === 'basic'): ?>
                                            <span class="basic-badge">Basic Plan</span>
                                        <?php elseif ($subscription['plan_type'] === 'premium'): ?>
                                            <span class="premium-badge">Premium Plan</span>
                                        <?php else: ?>
                                            <span class="free-badge">Free Plan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($subscription['current_period_start'])); ?> - <?php echo date('M j, Y', strtotime($subscription['current_period_end'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($subscription['current_period_end'])); ?></td>
                                    <td>$<?php echo number_format($subscription['amount'], 2); ?>/month</td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="subscription_id" value="<?php echo $subscription['id']; ?>">
                                            <input type="hidden" name="action" value="cancel_subscription">
                                            <button type="submit" class="action-btn cancel-btn"
                                                    onclick="return confirm('Are you sure you want to cancel this subscription?')">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No active subscriptions found.</p>
                <?php endif; ?>
            </div>

            <div class="subscriptions-table" style="margin-top: 30px;">
                <h3>All Users - Change Plans</h3>
                <?php
                // Get all users for plan management
                try {
                    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, plan_type, active_listings_limit, boost_credits FROM users ORDER BY created_at DESC");
                    $stmt->execute();
                    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $allUsers = [];
                }
                ?>

                <?php if (count($allUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Current Plan</th>
                                <th>Listings Limit</th>
                                <th>Boost Credits</th>
                                <th>Change Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($user['plan_type'] === 'basic'): ?>
                                            <span class="basic-badge">Basic</span>
                                        <?php elseif ($user['plan_type'] === 'premium'): ?>
                                            <span class="premium-badge">Premium</span>
                                        <?php else: ?>
                                            <span class="free-badge">Free</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['active_listings_limit']; ?></td>
                                    <td><?php echo $user['boost_credits']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="change_plan">
                                            <select name="new_plan" class="plan-select">
                                                <option value="free" <?php echo $user['plan_type'] === 'free' ? 'selected' : ''; ?>>Free</option>
                                                <option value="basic" <?php echo $user['plan_type'] === 'basic' ? 'selected' : ''; ?>>Basic ($25/mo)</option>
                                                <option value="premium" <?php echo $user['plan_type'] === 'premium' ? 'selected' : ''; ?>>Premium ($50/mo)</option>
                                            </select>
                                            <button type="submit" class="action-btn change-btn"
                                                    onclick="return confirm('Change this user\'s plan?')">Change</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>

            <div class="subscriptions-table" style="margin-top: 30px;">
                <h3>Recent Subscription Activity</h3>
                <?php if (count($recentSubscriptions) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSubscriptions as $subscription): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($subscription['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($subscription['plan_type'] === 'basic'): ?>
                                            <span class="basic-badge">Basic</span>
                                        <?php elseif ($subscription['plan_type'] === 'premium'): ?>
                                            <span class="premium-badge">Premium</span>
                                        <?php else: ?>
                                            <span class="free-badge">Free</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($subscription['status'] === 'active'): ?>
                                            <span style="color: #4caf50; font-weight: bold;">✓ Active</span>
                                        <?php elseif ($subscription['status'] === 'canceled'): ?>
                                            <span style="color: #f44336; font-weight: bold;">✗ Canceled</span>
                                        <?php else: ?>
                                            <span style="color: #ff9800; font-weight: bold;"><?php echo ucfirst($subscription['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($subscription['amount'], 2); ?>/month</td>
                                    <td><?php echo $subscription['current_period_start'] ? date('M j', strtotime($subscription['current_period_start'])) . ' - ' . date('M j', strtotime($subscription['current_period_end'])) : 'N/A'; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($subscription['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No subscription activity found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
