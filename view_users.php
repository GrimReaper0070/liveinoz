<?php
require_once 'config.php';

// Simple authentication - in a real application, you would have proper admin authentication
$authenticated = false;
$authError = '';

// Check if user is logged in as admin (simplified for this example)
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && 
    isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $authenticated = true;
} else {
    $authError = 'You must be logged in as an administrator to view this page.';
}

if (!$authenticated) {
    // Show login form for admin
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login - View Users</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #1a1a2e; color: white; }
            .container { max-width: 400px; margin: 0 auto; background: #16213e; padding: 30px; border-radius: 10px; }
            input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #00eeff; }
            button { width: 100%; padding: 12px; background: #00eeff; color: #000; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
            .error { color: #ff4d4d; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Admin Login Required</h1>
            <?php if ($authError): ?>
                <div class="error"><?php echo htmlspecialchars($authError); ?></div>
            <?php endif; ?>
            
            <p>Please log in as administrator to view users:</p>
            
            <form method="POST" action="login_process.php">
                <input type="email" name="email" placeholder="Admin Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login as Admin</button>
            </form>
            
            <p><a href="login.html" style="color: #00eeff;">Back to Login Page</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If authenticated, show users list
try {
    $pdo = getDBConnection();
    
    // Get all users
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, created_at, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Users List - Admin Panel</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a2e; color: white; }
            .container { max-width: 1200px; margin: 0 auto; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
            th { background-color: #0f3460; }
            tr:hover { background-color: #16213e; }
            .active { color: #4caf50; }
            .inactive { color: #f44336; }
            .admin { color: #ff9800; }
            .user { color: #2196f3; }
            .actions a { color: #00eeff; margin-right: 10px; text-decoration: none; }
            .actions a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Users Management</h1>
            <p>Logged in as administrator: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="<?php echo $user['role']; ?>"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                        <td class="actions">
                            <a href="#">Edit</a>
                            <a href="#">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p><a href="logout.php" style="color: #ff4d4d;">Logout</a></p>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>