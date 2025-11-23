<?php
require 'config.php';

try {
    $pdo = getDBConnection();

    // Check if available_posts column already exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'available_posts'");
    $columnExists = $checkColumn->fetch();

    if (!$columnExists) {
        // Add available_posts column if it doesn't exist
        $sql1 = "
            ALTER TABLE users
            ADD COLUMN available_posts INT NOT NULL DEFAULT 3
        ";
        $pdo->exec($sql1);
        echo "Added available_posts column to users table.\n";
    } else {
        echo "available_posts column already exists in users table.\n";
    }

    // Drop has_paid_for_next_post if it exists (cleanup from old system)
    try {
        $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS has_paid_for_next_post");
        echo "Dropped has_paid_for_next_post column if it existed.\n";
    } catch (Exception $e) {
        // Ignore error if column doesn't exist or if DROP IF EXISTS is not supported
    }

    // Create payments table for tracking transactions
    $sql3 = "
        CREATE TABLE IF NOT EXISTS payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            stripe_payment_id VARCHAR(255) UNIQUE NOT NULL,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'usd',
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            payment_type VARCHAR(50) DEFAULT 'room_posting',
            stripe_session_id VARCHAR(255),
            metadata JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    $pdo->exec($sql3);

    // Add indexes for better performance
    $sql4 = "CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id)";
    $pdo->exec($sql4);

    $sql5 = "CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status)";
    $pdo->exec($sql5);

    $sql6 = "CREATE INDEX IF NOT EXISTS idx_payments_created_at ON payments(created_at)";
    $pdo->exec($sql6);

    echo "✅ Migration completed successfully!\n";
    echo "Replaced has_paid_for_next_post with available_posts (default 3) in users table.\n";
    echo "Created payments table with indexes.\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
?>
