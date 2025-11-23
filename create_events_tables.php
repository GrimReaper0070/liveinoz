<?php
// create_events_tables.php - Create events and event_photos tables

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();

    // Create events table
    $sql = "
    CREATE TABLE IF NOT EXISTS `events` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `city` varchar(100) NOT NULL,
        `state_code` varchar(5) NOT NULL,
        `event_date` date NOT NULL,
        `event_time` time DEFAULT NULL,
        `contact_method` enum('phone','email','chat','multiple') DEFAULT 'multiple',
        `contact_phone` varchar(50) DEFAULT NULL,
        `contact_email` varchar(255) DEFAULT NULL,
        `allow_chat` tinyint(1) DEFAULT 1,
        `poster_path` varchar(500) DEFAULT NULL,
        `status` enum('pending','approved','rejected','removed') DEFAULT 'pending',
        `is_featured` tinyint(1) DEFAULT 0,
        `view_count` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `approved_at` timestamp NULL DEFAULT NULL,
        `approved_by` int(11) DEFAULT NULL,
        `expires_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `idx_status_city` (`status`,`city`),
        KEY `idx_event_date` (`event_date`),
        KEY `idx_created_at` (`created_at`),
        KEY `approved_by` (`approved_by`),
        KEY `idx_events_featured` (`is_featured`,`status`,`created_at`),
        CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `events_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "✅ Events table created successfully\n";

    // Create event_photos table
    $sql = "
    CREATE TABLE IF NOT EXISTS `event_photos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_id` int(11) NOT NULL,
        `photo_path` varchar(500) NOT NULL,
        `photo_order` tinyint(4) DEFAULT 1,
        `file_size` int(11) DEFAULT NULL,
        `mime_type` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `event_id` (`event_id`),
        KEY `idx_event_order` (`event_id`,`photo_order`),
        CONSTRAINT `event_photos_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "✅ Event photos table created successfully\n";

    // Insert sample events for testing
    $sampleEvents = [
        [
            'title' => 'Latin Night Fever',
            'description' => 'Hot Latin beats and dancing all night long! Join us for an unforgettable evening of salsa, bachata, and reggaeton.',
            'city' => 'Sydney',
            'state_code' => 'NSW',
            'event_date' => '2025-12-15',
            'event_time' => '22:00:00',
            'contact_method' => 'multiple',
            'contact_phone' => '+61 400 123 456',
            'contact_email' => 'events@sydneyvenue.com',
            'allow_chat' => 1,
            'poster_path' => 'images/poster1.png',
            'status' => 'approved'
        ],
        [
            'title' => 'Melbourne BBQ Meetup',
            'description' => 'Summer BBQ with authentic Argentine asado! Bring your mates and enjoy traditional grilled meats, cold beers, and good company.',
            'city' => 'Melbourne',
            'state_code' => 'VIC',
            'event_date' => '2025-12-20',
            'event_time' => '18:00:00',
            'contact_method' => 'chat',
            'contact_phone' => null,
            'contact_email' => null,
            'allow_chat' => 1,
            'poster_path' => 'images/poster2.png',
            'status' => 'approved'
        ],
        [
            'title' => 'Brisbane Latin Festival',
            'description' => 'Three days of Latin culture! Music, dance, food, and celebrations from across Latin America.',
            'city' => 'Brisbane',
            'state_code' => 'QLD',
            'event_date' => '2025-12-25',
            'event_time' => '20:00:00',
            'contact_method' => 'email',
            'contact_phone' => null,
            'contact_email' => 'info@brisbanefestival.com',
            'allow_chat' => 1,
            'poster_path' => 'images/poster3.png',
            'status' => 'approved'
        ],
        [
            'title' => 'Perth Tango Night',
            'description' => 'Elegant tango dancing under the stars. All levels welcome - from beginners to advanced dancers.',
            'city' => 'Perth',
            'state_code' => 'WA',
            'event_date' => '2025-12-10',
            'event_time' => '19:30:00',
            'contact_method' => 'phone',
            'contact_phone' => '+61 400 987 654',
            'contact_email' => null,
            'allow_chat' => 1,
            'poster_path' => 'images/poster4.png',
            'status' => 'approved'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO events
        (user_id, title, description, city, state_code, event_date, event_time,
         contact_method, contact_phone, contact_email, allow_chat, poster_path, status, created_at)
        VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    foreach ($sampleEvents as $event) {
        $stmt->execute([
            $event['title'],
            $event['description'],
            $event['city'],
            $event['state_code'],
            $event['event_date'],
            $event['event_time'],
            $event['contact_method'],
            $event['contact_phone'],
            $event['contact_email'],
            $event['allow_chat'],
            $event['poster_path'],
            $event['status']
        ]);
    }

    echo "✅ Sample events inserted successfully\n";

    echo "🎉 Events system setup complete!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
