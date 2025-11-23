<?php
// Migration to add profile_picture column to users table

require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Check if column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $stmt->execute();
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add the profile_picture column
        $sql = "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER last_name";
        $pdo->exec($sql);

        echo "✅ Successfully added profile_picture column to users table\n";
    } else {
        echo "ℹ️  profile_picture column already exists\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed successfully!\n";
?>
