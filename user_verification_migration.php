<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Adding user verification fields...</h1>";

    // Add is_verified column if it doesn't exist
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT FALSE
    ");
    echo "<p>is_verified column added or already exists.</p>";

    // Update existing users to be verified (assuming they were active before)
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE is_active = 1");
    $stmt->execute();
    echo "<p>Existing active users marked as verified.</p>";

    // Set is_active to FALSE for unverified users (new behavior)
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE is_verified = 0");
    $stmt->execute();
    echo "<p>Unverified users set to inactive.</p>";

    echo "<h2>Migration complete!</h2>";
    echo "<p>User verification system is now active.</p>";

} catch (Exception $e) {
    echo "<h1>Migration failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
