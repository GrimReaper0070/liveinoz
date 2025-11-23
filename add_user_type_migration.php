<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Adding user_type column to users table...</h1>";

    // Add user_type column if it doesn't exist
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS user_type ENUM('chat_only', 'full_access') DEFAULT 'full_access'
    ");
    echo "<p>user_type column added or already exists.</p>";

    echo "<h2>Migration complete!</h2>";
    echo "<p>user_type column is now available for user access control.</p>";

} catch (Exception $e) {
    echo "<h1>Migration failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
