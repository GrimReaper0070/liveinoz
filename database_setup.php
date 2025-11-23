<?php
require 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h1>Setting up database tables...</h1>";

    // Create users table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            role ENUM('user', 'admin') DEFAULT 'user'
        )
    ");
    echo "<p>Users table created or already exists.</p>";

    // Create rooms table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            address VARCHAR(255) NOT NULL,
            suburb VARCHAR(100) NOT NULL,
            city VARCHAR(100) NOT NULL,
            rent DECIMAL(10,2) NOT NULL,
            bond DECIMAL(10,2) NOT NULL,
            contact_name VARCHAR(100) NOT NULL,
            contact_number VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            photo1 VARCHAR(255),
            photo2 VARCHAR(255),
            photo3 VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>Rooms table created or already exists.</p>";

    // Create jobs table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            company VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            posted_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "<p>Jobs table created or already exists.</p>";

    // Create chat_messages table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            room VARCHAR(100) NOT NULL,
            state VARCHAR(10) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            delivered_at TIMESTAMP NULL,
            status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_room_state (room, state),
            INDEX idx_created_at (created_at),
            INDEX idx_status (status)
        )
    ");
    echo "<p>Chat messages table created or already exists.</p>";

    // Create index
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email_users ON users(email)");
    echo "<p>Index created.</p>";

    // Insert admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO users (first_name, last_name, email, password, role) VALUES 
            ('Admin', 'User', 'admin@example.com', '$2y$10$5QV0JwA3bLwZQvZ9dFyV2uV8p8h0O9n3v2J5r7Y9u4E8t6R1s2O3u', 'admin')
        ");
        echo "<p>Admin user inserted.</p>";
    } else {
        echo "<p>Admin user already exists.</p>";
    }

    echo "<h2>Database setup complete!</h2>";
    echo "<p>You can now use the application.</p>";

} catch (Exception $e) {
    echo "<h1>Database setup failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
