<?php
require_once '../config.php';
require_once 'admin_auth.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle room actions (approve/reject/delete)
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;

    if ($action && $roomId > 0) {
        try {
            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare("UPDATE rooms SET is_approved = 1 WHERE id = ?");
                    $stmt->execute([$roomId]);
                    $message = 'Room listing approved successfully.';
                    $messageType = 'success';
                    break;

                case 'unapprove':
                    $stmt = $pdo->prepare("UPDATE rooms SET is_approved = 0 WHERE id = ?");
                    $stmt->execute([$roomId]);
                    $message = 'Room listing unapproved and hidden from public view.';
                    $messageType = 'success';
                    break;

                case 'reject':
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
                    $stmt->execute([$roomId]);
                    $message = 'Room listing rejected and removed.';
                    $messageType = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
                    $stmt->execute([$roomId]);
                    $message = 'Room listing deleted successfully.';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'Error processing request: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all rooms (both approved and pending)
try {
    // First check if is_approved column exists
    $columnExists = false;
    try {
        $stmt = $pdo->query("SELECT is_approved FROM rooms LIMIT 1");
        $columnExists = true;
    } catch (Exception $e) {
        // Column doesn't exist yet
        $columnExists = false;
    }

    if ($columnExists) {
        $stmt = $pdo->query("
            SELECT r.*, u.first_name, u.last_name, u.email, COALESCE(r.is_approved, 1) as is_approved
            FROM rooms r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY COALESCE(r.is_approved, 1) ASC, r.created_at DESC
        ");
    } else {
        // Column doesn't exist, treat all rooms as approved for now
        $stmt = $pdo->query("
            SELECT r.*, u.first_name, u.last_name, u.email, 1 as is_approved
            FROM rooms r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
    }
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading rooms: ' . $e->getMessage();
    $messageType = 'error';
    $rooms = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Live in Oz</title>
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
        }

        .btn {
            padding: 10px 20px;
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            opacity: 0.9;
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

        .rooms-table {
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

        .approved-badge {
            background: #4caf50;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .pending-badge {
            background: #ff9800;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }

        .approve-btn {
            background: #4caf50;
            color: white;
        }

        .reject-btn {
            background: #f44336;
            color: white;
        }

        .delete-btn {
            background: #ff9800;
            color: white;
        }

        .photo-preview {
            max-width: 50px;
            max-height: 50px;
            border-radius: 3px;
        }

        .room-details {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>Room Management</p>
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
                <li><a href="rooms.php" class="active">Room Management</a></li>
                 <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="marketplace_review.php">🛍️ Marketplace</a></li>
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
                <div>
                    <h2>Room Management</h2>
                    <p>Approve or reject room listings</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="rooms-table">
                <?php if (count($rooms) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Rent</th>
                                <th>Contact</th>
                                <th>Posted By</th>
                                <th>Photos</th>
                                <th>Description</th>
                                <th>Posted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['id']); ?></td>
                                    <td>
                                        <?php if ($room['is_approved']): ?>
                                            <span class="approved-badge">APPROVED</span>
                                        <?php else: ?>
                                            <span class="pending-badge">PENDING</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($room['address']); ?>, <?php echo htmlspecialchars($room['suburb']); ?></td>
                                    <td><?php echo htmlspecialchars($room['city']); ?></td>
                                    <td>$<?php echo htmlspecialchars($room['rent']); ?>/week</td>
                                    <td><?php echo htmlspecialchars($room['contact_name']); ?><br><small><?php echo htmlspecialchars($room['contact_number']); ?></small></td>
                                    <td>
                                        <?php echo htmlspecialchars($room['first_name'] . ' ' . $room['last_name']); ?>
                                        <br><small><?php echo htmlspecialchars($room['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $photoCount = 0;
                                        if ($room['photo1']) $photoCount++;
                                        if ($room['photo2']) $photoCount++;
                                        if ($room['photo3']) $photoCount++;
                                        echo $photoCount . ' photos';
                                        ?>
                                    </td>
                                    <td class="room-details" title="<?php echo htmlspecialchars($room['description']); ?>">
                                        <?php echo htmlspecialchars(substr($room['description'], 0, 50)); ?><?php echo strlen($room['description']) > 50 ? '...' : ''; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($room['created_at'])); ?></td>
                                    <td class="actions">
                                        <?php if (!$room['is_approved']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="action-btn approve-btn">Approve</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="action-btn reject-btn"
                                                        onclick="return confirm('Are you sure you want to reject this room listing?')">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <input type="hidden" name="action" value="unapprove">
                                                <button type="submit" class="action-btn" style="background: #ff5722; color: white;"
                                                        onclick="return confirm('Are you sure you want to unapprove this room listing? It will be hidden from public view.')">Unapprove</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="action-btn delete-btn"
                                                        onclick="return confirm('Are you sure you want to DELETE this approved room listing?')">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No rooms found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
