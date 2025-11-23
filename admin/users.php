<?php
require_once '../config.php';
require_once 'admin_auth.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle user actions (activate/deactivate/delete)
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($action && $userId > 0) {
        try {
            switch ($action) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                    $stmt->execute([$userId]);
                    $message = 'User activated successfully.';
                    $messageType = 'success';
                    break;
                    
                case 'deactivate':
                    // Prevent deactivating the current admin user
                    if ($userId != $_SESSION['user_id']) {
                        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                        $stmt->execute([$userId]);
                        $message = 'User deactivated successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'You cannot deactivate your own account.';
                        $messageType = 'error';
                    }
                    break;
                    
                case 'delete':
                    // Prevent deleting the current admin user
                    if ($userId != $_SESSION['user_id']) {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $message = 'User deleted successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'You cannot delete your own account.';
                        $messageType = 'error';
                    }
                    break;
                    
                case 'make_admin':
                    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    $stmt->execute([$userId]);
                    $message = 'User granted admin privileges.';
                    $messageType = 'success';
                    break;
                    
                case 'remove_admin':
                    // Prevent removing admin privileges from current user
                    if ($userId != $_SESSION['user_id']) {
                        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                        $stmt->execute([$userId]);
                        $message = 'Admin privileges removed.';
                        $messageType = 'success';
                    } else {
                        $message = 'You cannot remove your own admin privileges.';
                        $messageType = 'error';
                    }
                    break;

                case 'verify':
                    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, is_active = 1 WHERE id = ?");
                    $stmt->execute([$userId]);
                    $message = 'User verified and activated successfully.';
                    $messageType = 'success';
                    break;

                case 'resolve_report':
                    $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
                    if ($reportId > 0) {
                        $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id'], $reportId]);
                        $message = 'Report resolved successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Invalid report ID.';
                        $messageType = 'error';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = 'Error processing request: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all users
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, is_active, is_verified, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading users: ' . $e->getMessage();
    $messageType = 'error';
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Live in Oz</title>
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
        
        .users-table {
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
        
        .active-badge {
            background: #4caf50;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .inactive-badge {
            background: #f44336;
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
        
        .activate-btn {
            background: #4caf50;
            color: white;
        }
        
        .deactivate-btn {
            background: #f44336;
            color: white;
        }
        
        .delete-btn {
            background: #ff9800;
            color: white;
        }
        
        .make-admin-btn {
            background: #9c27b0;
            color: white;
        }
        
        .remove-admin-btn {
            background: #607d8b;
            color: white;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>User Management</p>
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
                <li><a href="users.php" class="active">User Management</a></li>
                <li><a href="rooms.php">Room Management</a></li>
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
                    <h2>User Management</h2>
                    <p>Manage all registered users</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="users-table">
                <?php if (count($users) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="admin-badge">ADMIN</span>
                                        <?php else: ?>
                                            <span class="user-badge">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="active-badge">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="inactive-badge">INACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="actions">
                                        <?php if (!$user['is_verified']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="verify">
                                                <button type="submit" class="action-btn" style="background: #4caf50; color: white;">Verify</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($user['is_active']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="deactivate">
                                                <button type="submit" class="action-btn deactivate-btn"
                                                        onclick="return confirm('Are you sure you want to deactivate this user?')">Deactivate</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="action-btn activate-btn">Activate</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($user['role'] === 'admin'): ?>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="remove_admin">
                                                    <button type="submit" class="action-btn remove-admin-btn"
                                                            onclick="return confirm('Are you sure you want to remove admin privileges from this user?')">Remove Admin</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="make_admin">
                                                <button type="submit" class="action-btn make-admin-btn"
                                                        onclick="return confirm('Are you sure you want to grant admin privileges to this user?')">Make Admin</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="action-btn delete-btn"
                                                        onclick="return confirm('Are you sure you want to DELETE this user? This cannot be undone!')">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
