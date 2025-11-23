<?php
// Test script for contact form functionality
require_once 'config.php';

echo "<h1>Contact Form Test</h1>";

// Test database connection
try {
    $pdo = getDBConnection();
    echo "<p>✅ Database connection successful</p>";

    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
    if ($result->rowCount() > 0) {
        echo "<p>✅ Contact messages table exists</p>";
    } else {
        echo "<p>❌ Contact messages table does not exist</p>";
    }

    // Show current messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages");
    $count = $stmt->fetch()['total'];
    echo "<p>Total messages in database: $count</p>";

    if ($count > 0) {
        echo "<h2>Recent Messages:</h2>";
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>From:</strong> " . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['email']) . ")<br>";
            echo "<strong>Subject:</strong> " . htmlspecialchars($row['subject']) . "<br>";
            echo "<strong>Status:</strong> " . $row['status'] . "<br>";
            echo "<strong>Created:</strong> " . $row['created_at'] . "<br>";
            echo "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($row['message']));
            echo "</div>";
        }
    }

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// Test form submission simulation
echo "<h2>Test Form Submission</h2>";
echo "<form method='POST' action='contact_process.php' style='border: 1px solid #ccc; padding: 20px; max-width: 500px;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Name: <input type='text' name='name' value='Test User' required></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Email: <input type='email' name='email' value='test@example.com' required></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Subject: <input type='text' name='subject' value='Test Subject' required></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Message: <textarea name='message' rows='4' required>Test message content</textarea></label>";
echo "</div>";
echo "<button type='submit'>Submit Test Message</button>";
echo "</form>";

echo "<p><a href='contact.html'>View Contact Form</a></p>";
?>
