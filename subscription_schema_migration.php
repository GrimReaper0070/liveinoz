<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Adding Subscription Schema to Database...</h1>";

    // Add subscription fields to users table
    echo "<h2>Updating users table...</h2>";

    // Add plan_type column
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS plan_type ENUM('free', 'basic', 'premium') DEFAULT 'free'
    ");
    echo "<p>✅ Added plan_type column to users table.</p>";

    // Add plan_expires_at column
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS plan_expires_at TIMESTAMP NULL
    ");
    echo "<p>✅ Added plan_expires_at column to users table.</p>";

    // Add active_listings_limit column
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS active_listings_limit INT DEFAULT 1
    ");
    echo "<p>✅ Added active_listings_limit column to users table.</p>";

    // Add boost_credits column
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS boost_credits INT DEFAULT 1
    ");
    echo "<p>✅ Added boost_credits column to users table.</p>";

    // Update existing users to have proper plan settings
    $pdo->exec("
        UPDATE users
        SET active_listings_limit = CASE
            WHEN plan_type = 'free' THEN 1
            WHEN plan_type = 'basic' THEN 3
            WHEN plan_type = 'premium' THEN 10
            ELSE 1
        END,
        boost_credits = CASE
            WHEN plan_type = 'premium' THEN 0
            ELSE 1
        END
        WHERE plan_expires_at IS NULL OR plan_expires_at > NOW()
    ");
    echo "<p>✅ Updated existing users with plan-based limits.</p>";

    echo "<h2>Updating rooms table...</h2>";

    // Add expiration and boost fields to rooms table
    $pdo->exec("
        ALTER TABLE rooms
        ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL
    ");
    echo "<p>✅ Added expires_at column to rooms table.</p>";

    $pdo->exec("
        ALTER TABLE rooms
        ADD COLUMN IF NOT EXISTS is_boosted BOOLEAN DEFAULT FALSE
    ");
    echo "<p>✅ Added is_boosted column to rooms table.</p>";

    $pdo->exec("
        ALTER TABLE rooms
        ADD COLUMN IF NOT EXISTS boost_expires_at TIMESTAMP NULL
    ");
    echo "<p>✅ Added boost_expires_at column to rooms table.</p>";

    $pdo->exec("
        ALTER TABLE rooms
        ADD COLUMN IF NOT EXISTS boost_cost DECIMAL(10,2) NULL
    ");
    echo "<p>✅ Added boost_cost column to rooms table.</p>";

    // Set default expiration for existing rooms (7 days from now for approved rooms)
    $pdo->exec("
        UPDATE rooms
        SET expires_at = DATE_ADD(created_at, INTERVAL 7 DAY)
        WHERE expires_at IS NULL AND is_approved = 1
    ");
    echo "<p>✅ Set default 7-day expiration for existing approved rooms.</p>";

    echo "<h2>Creating subscriptions table...</h2>";

    // Create subscriptions table for payment history
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_type ENUM('basic', 'premium') NOT NULL,
            stripe_subscription_id VARCHAR(255) UNIQUE,
            status ENUM('active', 'canceled', 'past_due', 'incomplete') DEFAULT 'active',
            current_period_start TIMESTAMP NULL,
            current_period_end TIMESTAMP NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_status (user_id, status),
            INDEX idx_stripe_id (stripe_subscription_id)
        )
    ");
    echo "<p>✅ Created subscriptions table.</p>";

    echo "<h2>Creating boosts table...</h2>";

    // Create boosts table for tracking boost history
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS boosts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            user_id INT NOT NULL,
            city VARCHAR(100) NOT NULL,
            cost DECIMAL(10,2) NOT NULL,
            stripe_payment_id VARCHAR(255),
            activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            status ENUM('active', 'expired', 'canceled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_city_expires (city, expires_at),
            INDEX idx_status_active (status, expires_at)
        )
    ");
    echo "<p>✅ Created boosts table.</p>";

    echo "<h2>Updating payments table...</h2>";

    // Add payment_type to existing payments table if it doesn't exist
    $pdo->exec("
        ALTER TABLE payments
        ADD COLUMN IF NOT EXISTS payment_type ENUM('room_posting', 'subscription', 'boost') DEFAULT 'room_posting'
    ");
    echo "<p>✅ Added payment_type column to payments table.</p>";

    echo "<h1>✅ Database Migration Complete!</h1>";
    echo "<p>Subscription and boost schema has been added successfully.</p>";
    echo "<p>• Users table: Added plan_type, plan_expires_at, active_listings_limit, boost_credits</p>";
    echo "<p>• Rooms table: Added expires_at, is_boosted, boost_expires_at, boost_cost</p>";
    echo "<p>• New tables: subscriptions, boosts</p>";
    echo "<p>• Updated payments table with payment_type</p>";

    // Show current user plan distribution
    echo "<h2>Current User Plan Distribution:</h2>";
    $stmt = $pdo->prepare("SELECT plan_type, COUNT(*) as count FROM users GROUP BY plan_type");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Plan Type</th><th>User Count</th></tr>";
    foreach ($plans as $plan) {
        echo "<tr><td>{$plan['plan_type']}</td><td>{$plan['count']}</td></tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<h1>❌ Database Migration Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
