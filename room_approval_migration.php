<?php
require 'config.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $pdo = getDBConnection();
        $message = "<h2>Adding room approval system...</h2>";

        // Add is_approved column if it doesn't exist
        $pdo->exec("
            ALTER TABLE rooms
            ADD COLUMN IF NOT EXISTS is_approved BOOLEAN DEFAULT FALSE
        ");
        $message .= "<p>✅ is_approved column added or already exists.</p>";

        // Set existing rooms as approved (assuming they were previously visible)
        $stmt = $pdo->prepare("UPDATE rooms SET is_approved = 1 WHERE is_approved = 0 OR is_approved IS NULL");
        $stmt->execute();
        $affected = $stmt->rowCount();
        $message .= "<p>✅ Set $affected existing rooms as approved.</p>";

        $message .= "<h2>Room approval system setup complete!</h2>";
        $message .= "<p>New room listings will require admin approval before being displayed.</p>";
        $message .= "<p><a href='admin/rooms.php'>Go to Room Management</a> | <a href='admin/dashboard.php'>Go to Dashboard</a></p>";
        $messageType = 'success';

    } catch (Exception $e) {
        $message = "<h2>Room approval setup failed!</h2><p>Error: " . $e->getMessage() . "</p>";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Approval Migration - Live in Oz</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #fff;
            padding: 40px;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #16213e;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #00eeff;
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: #00eeff;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .warning {
            background: rgba(255, 152, 0, 0.2);
            border: 1px solid #ff9800;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            color: #fff;
        }

        .button {
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #0b0124;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 20px auto;
            transition: all 0.3s ease;
        }

        .button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
        }

        .message.info {
            background: rgba(0, 238, 255, 0.1);
            border: 1px solid #00eeff;
        }

        ul {
            margin: 15px 0;
            padding-left: 30px;
        }

        li {
            margin: 8px 0;
        }

        a {
            color: #00eeff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Room Approval System Migration</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php else: ?>
            <div class="warning">
                <h3>⚠️ Important: Database Migration Required</h3>
                <p>This migration will add an approval system for accommodation listings. Here's what will happen:</p>
                <ul>
                    <li>Add an <code>is_approved</code> column to the rooms table</li>
                    <li>Set all existing rooms as approved (they were visible before)</li>
                    <li>Future room listings will require admin approval before appearing publicly</li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="run_migration" class="button">🚀 Run Migration</button>
            </form>

            <h2>After Migration</h2>
            <p>Once the migration completes:</p>
            <ul>
                <li>New room listings will be hidden until approved by an admin</li>
                <li>Admins can manage rooms through <strong>Room Management</strong> in the admin panel</li>
                <li>The dashboard will show statistics about pending and approved rooms</li>
                <li>Users will see a message that their listing is pending approval</li>
            </ul>

            <h2>Admin Features</h2>
            <ul>
                <li><strong>Dashboard Statistics:</strong> Shows pending room approvals</li>
                <li><strong>Room Management Page:</strong> Approve/reject room listings</li>
                <li><strong>Quick Actions:</strong> Approve directly from dashboard</li>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
