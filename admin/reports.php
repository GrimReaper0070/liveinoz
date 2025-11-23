<?php
require_once '../config.php';
require_once 'admin_auth.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle report actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;

    if ($action === 'resolve_report' && $reportId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $reportId]);
            $message = 'Report resolved successfully.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error resolving report: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get reports statistics
$reportsStats = getReportsStatistics($pdo);

// Get all reports with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $stmt = $pdo->prepare("
        SELECT
            r.id,
            r.reason,
            r.details,
            r.status,
            r.created_at,
            r.reviewed_at,
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
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reports");
    $totalReports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalReports / $perPage);

} catch (Exception $e) {
    $message = 'Error loading reports: ' . $e->getMessage();
    $messageType = 'error';
    $reports = [];
    $totalPages = 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Management - Live in Oz</title>
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
            text-align: center;
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

        .btn {
            padding: 12px 25px;
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #0b0124;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 2px solid;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4caf50;
            color: #81c784;
        }

        .message.error {
            background: rgba(244, 67, 54, 0.1);
            border-color: #f44336;
            color: #ef5350;
        }

        .reports-table {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
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

        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #ff9800;
            color: #000;
        }

        .status-resolved {
            background: #4caf50;
            color: #fff;
        }

        .status-reviewed {
            background: #2196f3;
            color: #fff;
        }

        .pagination {
            margin-top: 30px;
            text-align: center;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 3px;
            background: #16213e;
            color: #00eeff;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid #00eeff;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #00eeff;
            color: #000;
        }

        .pagination .current {
            background: #00eeff;
            color: #000;
            font-weight: bold;
        }

        .resolve-btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .resolve-btn:hover {
            background: #45a049;
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

            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .main-content {
                padding: 20px;
            }

            .page-title h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>Reports Management</p>
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
                 <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="marketplace_review.php">🛍️ Marketplace</a></li>
                <li><a href="events_review.php">🎉 Party & Events</a></li>
                <li><a href="blog.php">📝 Blog</a></li>
                <li><a href="whatsapp.php">💬 WhatsApp</a></li>
                <li><a href="reports.php"  class="active">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2>Reports Management</h2>
                    <p>Review and manage user reports</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Reports</h3>
                    <div class="number"><?php echo $reportsStats['total']; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Pending Reports</h3>
                    <div class="number"><?php echo $reportsStats['pending']; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Resolved Reports</h3>
                    <div class="number"><?php echo $reportsStats['resolved']; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Recent Reports (7 days)</h3>
                    <div class="number"><?php echo $reportsStats['recent']; ?></div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="reports-table">
                <?php if (count($reports) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reporter</th>
                                <th>Reported User</th>
                                <th>Reason</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['id']); ?></td>
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
                                            <div style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars(substr($report['reported_content'], 0, 30)); ?><?php echo strlen($report['reported_content']) > 30 ? '...' : ''; ?>
                                            </div>
                                        <?php else: ?>
                                            <em>General report</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $report['status']; ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <?php if ($report['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                                <input type="hidden" name="action" value="resolve_report">
                                                <button type="submit" class="resolve-btn">Resolve</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #aaa;">Resolved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i === $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No reports found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
