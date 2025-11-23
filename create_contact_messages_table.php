<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Create contact_messages table
    $sql = "
        CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `subject` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `status` enum('unread','read','responded') DEFAULT 'unread',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "✅ Contact messages table created successfully!\n";

} catch (Exception $e) {
    echo "❌ Error creating table: " . $e->getMessage() . "\n";
}
?>
