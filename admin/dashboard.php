<?php
require_once '../config.php';
require_once 'admin_auth.php';
require_once 'admin_functions.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Get user statistics
$stats = getUserStatistics($pdo);

// Get reports statistics
$reportsStats = getReportsStatistics($pdo);

// Get room statistics
$roomStats = getRoomStatistics($pdo);

// Get payment statistics
try {
    $paymentStats = getPaymentStatistics($pdo);
    error_log("Payment Stats loaded: " . json_encode($paymentStats));
    echo "<!-- DEBUG: Payment Stats loaded: " . json_encode($paymentStats) . " -->";
} catch (Exception $e) {
    echo "<!-- DEBUG: Error loading payment stats: " . $e->getMessage() . " -->";
    $paymentStats = [
        'total_revenue' => 0,
        'total_payments' => 0,
        'paying_users' => 0,
        'monthly_revenue' => 0,
        'monthly_payments' => 0
    ];
}

// Get subscription statistics
try {
    $subscriptionStats = getSubscriptionStatistics($pdo);
    error_log("Subscription Stats loaded: " . json_encode($subscriptionStats));
} catch (Exception $e) {
    echo "<!-- DEBUG: Error loading subscription stats: " . $e->getMessage() . " -->";
    $subscriptionStats = [
        'plan_stats' => ['basic' => ['total' => 0, 'active' => 0], 'premium' => ['total' => 0, 'active' => 0]],
        'basic_revenue' => 0,
        'premium_revenue' => 0,
        'total_revenue' => 0
    ];
}

// Get boost statistics
try {
    $boostStats = getBoostStatistics($pdo);
    error_log("Boost Stats loaded: " . json_encode($boostStats));
} catch (Exception $e) {
    echo "<!-- DEBUG: Error loading boost stats: " . $e->getMessage() . " -->";
    $boostStats = [
        'city_boosts' => [],
        'total_boost_revenue' => 0,
        'total_boosts' => 0,
        'average_boost_cost' => 0
    ];
}

// Get user plan distribution
try {
    $userPlanStats = getUserPlanDistribution($pdo);
    error_log("User Plan Stats loaded: " . json_encode($userPlanStats));
} catch (Exception $e) {
    echo "<!-- DEBUG: Error loading user plan stats: " . $e->getMessage() . " -->";
    $userPlanStats = [];
}

// Get recent users
$recentUsers = getRecentUsers($pdo);

// Get pending verifications
$pendingUsers = getPendingVerifications($pdo);

// Get pending reports
$pendingReports = getPendingReports($pdo);

// Get pending rooms
$pendingRooms = getPendingRooms($pdo);

// Get recent payments
try {
    $recentPayments = getRecentPayments($pdo, 10);
    echo "<!-- DEBUG: Recent payments loaded: " . count($recentPayments) . " records -->";
} catch (Exception $e) {
    echo "<!-- DEBUG: Error loading recent payments: " . $e->getMessage() . " -->";
    $recentPayments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Live in Oz</title>
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
        }
        
        .page-title h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .page-title p {
            color: #aaa;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #00eeff;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
        }
        
        .stat-card .description {
            font-size: 14px;
            color: #aaa;
            margin-top: 10px;
        }
        
        .recent-users {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .recent-users h3 {
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
        
        .admin-badge {
            background: #ff9800;
            color: #000;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .user-badge {
            background: #2196f3;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .date {
            color: #aaa;
            font-size: 14px;
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
            <p>Admin Dashboard</p>
        </div>
        <div class="user-info">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul>
              <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="users.php">User Management</a></li>
                <li><a href="rooms.php">Room Management</a></li>
                 <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="marketplace_review.php" >🛍️ Marketplace</a></li>
                <li><a href="events_review.php">🎉 Party & Events</a></li>
                <li><a href="blog.php">📝 Blog</a></li>
                <li><a href="whatsapp.php">💬 WhatsApp</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="contact.php">📧 Contact</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="page-title">
                <h2>Dashboard Overview</h2>
                <p>Monitor your website statistics and user activity</p>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $stats['total']; ?></div>
                    <div class="description">All registered users</div>
                </div>
                
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <div class="number"><?php echo $stats['active']; ?></div>
                    <div class="description">Currently active accounts</div>
                </div>
                
                <div class="stat-card">
                    <h3>Admin Users</h3>
                    <div class="number"><?php echo $stats['admins']; ?></div>
                    <div class="description">Administrative accounts</div>
                </div>
                
                <div class="stat-card">
                    <h3>New Users (30 days)</h3>
                    <div class="number"><?php echo $stats['recent']; ?></div>
                    <div class="description">Registered this month</div>
                </div>

                <div class="stat-card">
                    <h3>Pending Verifications</h3>
                    <div class="number"><?php echo $stats['pending']; ?></div>
                    <div class="description">Awaiting admin approval</div>
                </div>

                <div class="stat-card">
                    <h3>Pending Reports</h3>
                    <div class="number"><?php echo $reportsStats['pending']; ?></div>
                    <div class="description">User reports to review</div>
                </div>

                <div class="stat-card">
                    <h3>Pending Rooms</h3>
                    <div class="number"><?php echo $roomStats['pending']; ?></div>
                    <div class="description">Awaiting admin approval</div>
                </div>

                <div class="stat-card">
                    <h3>Approved Rooms</h3>
                    <div class="number"><?php echo $roomStats['approved']; ?></div>
                    <div class="description">Live room listings</div>
                </div>

                <!-- Subscription Statistics -->
                <div class="stat-card">
                    <h3>Basic Plan Subscribers</h3>
                    <div class="number"><?php echo $subscriptionStats['plan_stats']['basic']['active']; ?></div>
                    <div class="description">$<?php echo number_format($subscriptionStats['basic_revenue'], 2); ?> total revenue</div>
                </div>

                <div class="stat-card">
                    <h3>Premium Plan Subscribers</h3>
                    <div class="number"><?php echo $subscriptionStats['plan_stats']['premium']['active']; ?></div>
                    <div class="description">$<?php echo number_format($subscriptionStats['premium_revenue'], 2); ?> total revenue</div>
                </div>

                <!-- Boost Statistics -->
                <div class="stat-card">
                    <h3>Active Boosts</h3>
                    <div class="number"><?php echo $boostStats['total_boosts']; ?></div>
                    <div class="description">$<?php echo number_format($boostStats['total_boost_revenue'], 2); ?> total revenue</div>
                </div>

                <div class="stat-card">
                    <h3>Avg Boost Cost</h3>
                    <div class="number">$<?php echo number_format($boostStats['average_boost_cost'], 2); ?></div>
                    <div class="description">Per boost transaction</div>
                </div>

                <!-- Revenue Statistics -->
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="number">$<?php echo number_format($paymentStats['total_revenue'] + $subscriptionStats['total_revenue'] + $boostStats['total_boost_revenue'], 2); ?></div>
                    <div class="description">All-time earnings</div>
                </div>

                <div class="stat-card">
                    <h3>Monthly Revenue</h3>
                    <div class="number">$<?php echo number_format($paymentStats['monthly_revenue'], 2); ?></div>
                    <div class="description">This month's earnings</div>
                </div>

                <!-- Plan Distribution -->
                <?php if (count($userPlanStats) > 0): ?>
                    <?php foreach ($userPlanStats as $plan): ?>
                        <div class="stat-card">
                            <h3><?php echo ucfirst($plan['plan_type']); ?> Users</h3>
                            <div class="number"><?php echo $plan['user_count']; ?></div>
                            <div class="description"><?php echo $plan['active_plans']; ?> active, $<?php echo number_format($plan['revenue'], 2); ?> revenue</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>




            </div>
            
            <div class="recent-users">
                <h3>Recent Registrations</h3>
                <?php if (count($recentUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="admin-badge">ADMIN</span>
                                        <?php else: ?>
                                            <span class="user-badge">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>

            <div class="recent-users" style="margin-top: 30px;">
                <h3>Pending Verifications</h3>
                <?php if (count($pendingUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="admin-badge">ADMIN</span>
                                        <?php else: ?>
                                            <span class="user-badge">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="users.php" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="verify">
                                            <button type="submit" style="background: #4caf50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Verify</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending verifications.</p>
                <?php endif; ?>
            </div>

            <div class="recent-users" style="margin-top: 30px;">
                <h3>Pending Rooms</h3>
                <?php if (count($pendingRooms) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>City</th>
                                <th>Rent</th>
                                <th>Posted By</th>
                                <th>Posted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['address']); ?><br><small><?php echo htmlspecialchars($room['suburb']); ?></small></td>
                                    <td><?php echo htmlspecialchars($room['city']); ?></td>
                                    <td>$<?php echo htmlspecialchars($room['rent']); ?>/week</td>
                                    <td><?php echo htmlspecialchars($room['first_name'] . ' ' . $room['last_name']); ?></td>
                                    <td class="date"><?php echo date('M j, Y', strtotime($room['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="rooms.php" style="display: inline;">
                                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" style="background: #4caf50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="margin-top: 15px;"><a href="rooms.php" style="color: #00eeff;">View all pending rooms →</a></p>
                <?php else: ?>
                    <p>No pending rooms.</p>
                <?php endif; ?>
            </div>

            <div class="recent-users" style="margin-top: 30px;">
                <h3>Pending Reports</h3>
                <?php if (count($pendingReports) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Reporter</th>
                                <th>Reported User</th>
                                <th>Reason</th>
                                <th>Content</th>
                                <th>Reported Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingReports as $report): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($report['reporter_first_name'] . ' ' . $report['reporter_last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($report['reporter_email']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($report['reported_first_name'] . ' ' . $report['reported_last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($report['reported_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $report['reason']))); ?></td>
                                    <td>
                                        <?php if ($report['reported_content']): ?>
                                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars(substr($report['reported_content'], 0, 50)); ?><?php echo strlen($report['reported_content']) > 50 ? '...' : ''; ?>
                                            </div>
                                        <?php else: ?>
                                            <em>General report</em>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="users.php" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                            <input type="hidden" name="action" value="resolve_report">
                                            <button type="submit" style="background: #4caf50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Resolve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending reports.</p>
                <?php endif; ?>
            </div>

            <div class="recent-users" style="margin-top: 30px;">
                <h3>Recent Payments</h3>
                <?php if (count($recentPayments) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($payment['email']); ?></small>
                                    </td>
                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                    <td>
                                        <?php if ($payment['status'] === 'completed'): ?>
                                            <span style="color: #4caf50; font-weight: bold;">✓ <?php echo ucfirst($payment['status']); ?></span>
                                        <?php elseif ($payment['status'] === 'pending'): ?>
                                            <span style="color: #ff9800; font-weight: bold;">⏳ <?php echo ucfirst($payment['status']); ?></span>
                                        <?php else: ?>
                                            <span style="color: #f44336; font-weight: bold;">✗ <?php echo ucfirst($payment['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No payments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
