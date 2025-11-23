<?php
require_once '../config.php';
require_once 'admin_auth.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle message actions (mark as read/responded/delete)
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

    if ($action && $messageId > 0) {
        try {
            switch ($action) {
                case 'mark_read':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
                    $stmt->execute([$messageId]);
                    $message = 'Message marked as read.';
                    $messageType = 'success';
                    break;

                case 'mark_responded':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'responded' WHERE id = ?");
                    $stmt->execute([$messageId]);
                    $message = 'Message marked as responded.';
                    $messageType = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                    $stmt->execute([$messageId]);
                    $message = 'Message deleted successfully.';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'Error processing request: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchTerm)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY created_at DESC";

// Get messages
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading messages: ' . $e->getMessage();
    $messageType = 'error';
    $messages = [];
}

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages");
    $totalMessages = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as unread FROM contact_messages WHERE status = 'unread'");
    $unreadMessages = $stmt->fetch()['unread'];

    $stmt = $pdo->query("SELECT COUNT(*) as read FROM contact_messages WHERE status = 'read'");
    $readMessages = $stmt->fetch()['read'];

    $stmt = $pdo->query("SELECT COUNT(*) as responded FROM contact_messages WHERE status = 'responded'");
    $respondedMessages = $stmt->fetch()['responded'];
} catch (Exception $e) {
    $totalMessages = $unreadMessages = $readMessages = $respondedMessages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Live in Oz</title>
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

        .sidebar ul li a:hover {
            background: rgba(0, 238, 255, 0.1);
            color: #00eeff;
            border-left: 3px solid #00eeff;
        }

        .sidebar ul li a.active {
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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #00eeff;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
        }

        .filters {
            background: #16213e;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters select, .filters input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2c3e50;
            color: #fff;
        }

        .filters button {
            padding: 8px 15px;
            background: #00eeff;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .messages-table {
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
        }

        .status-unread {
            background: #ff9800;
            color: #000;
        }

        .status-read {
            background: #2196f3;
            color: #fff;
        }

        .status-responded {
            background: #4caf50;
            color: #fff;
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

        .read-btn {
            background: #2196f3;
            color: white;
        }

        .responded-btn {
            background: #4caf50;
            color: white;
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        .message-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .message-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-content {
            background: #16213e;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            color: #00eeff;
        }

        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
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

            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .filters form {
                flex-direction: column;
                align-items: stretch;
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
            <p>Contact Messages</p>
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
                <li><a href="reports.php">Reports</a></li>
                <li><a href="contact.php" class="active">📧 Contact</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2>Contact Messages</h2>
                    <p>Manage contact form submissions</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Messages</h3>
                    <div class="number"><?php echo $totalMessages; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Unread</h3>
                    <div class="number"><?php echo $unreadMessages; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Read</h3>
                    <div class="number"><?php echo $readMessages; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Responded</h3>
                    <div class="number"><?php echo $respondedMessages; ?></div>
                </div>
            </div>

            <div class="filters">
                <form method="GET">
                    <select name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                        <option value="responded" <?php echo $statusFilter === 'responded' ? 'selected' : ''; ?>>Responded</option>
                    </select>
                    <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit">Filter</button>
                    <a href="contact.php" class="btn" style="margin-left: 10px;">Clear Filters</a>
                </form>
            </div>

            <div class="messages-table">
                <?php if (count($messages) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Received</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($msg['id']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                    <td class="message-content"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?><?php echo strlen($msg['message']) > 100 ? '...' : ''; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $msg['status']; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></td>
                                    <td class="actions">
                                        <button class="action-btn read-btn" onclick="viewMessage(<?php echo $msg['id']; ?>, '<?php echo addslashes($msg['name']); ?>', '<?php echo addslashes($msg['email']); ?>', '<?php echo addslashes($msg['subject']); ?>', '<?php echo addslashes($msg['message']); ?>', '<?php echo $msg['status']; ?>', '<?php echo $msg['created_at']; ?>')">View</button>

                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="action-btn read-btn">Mark Read</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($msg['status'] !== 'responded'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="action" value="mark_responded">
                                                <button type="submit" class="action-btn responded-btn">Mark Responded</button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="action-btn delete-btn"
                                                    onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No messages found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="message-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Contact Message Details</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="messageDetails">
                <!-- Message content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewMessage(id, name, email, subject, message, status, createdAt) {
            const modal = document.getElementById('messageModal');
            const details = document.getElementById('messageDetails');

            details.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <strong>From:</strong> ${name} <${email}><br>
                    <strong>Subject:</strong> ${subject}<br>
                    <strong>Status:</strong> <span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span><br>
                    <strong>Received:</strong> ${new Date(createdAt).toLocaleString()}
                </div>
                <div style="margin-bottom: 20px;">
                    <strong>Message:</strong><br>
                    <div style="background: #2c3e50; padding: 15px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap;">${message}</div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="mailto:${email}?subject=Re: ${subject}" class="btn" style="background: #00eeff; color: #000;">Reply via Email</a>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="message_id" value="${id}">
                        <input type="hidden" name="action" value="${status === 'unread' ? 'mark_read' : 'mark_responded'}">
                        <button type="submit" class="btn" style="background: ${status === 'unread' ? '#2196f3' : '#4caf50'}; color: white;">
                            ${status === 'unread' ? 'Mark as Read' : 'Mark as Responded'}
                        </button>
                    </form>
                </div>
            `;

            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
