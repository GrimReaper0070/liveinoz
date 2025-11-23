<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'oznew');

// Create connection
function getDBConnection() {
    try {
        // First try to connect to MySQL without specifying database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Now connect to the specific database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        error_log("DB Connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Configure session - only start if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters to work across localhost ports
    session_set_cookie_params([
        'lifetime' => 0,        // Session cookie
        'path' => '/',          // Available site-wide
        'domain' => 'localhost', // Allow across localhost ports
        'secure' => false,      // Allow HTTP (for localhost)
        'httponly' => false,    // Allow JavaScript access if needed
        'samesite' => 'Lax'     // Allow some cross-origin requests
    ]);
    session_start();
}
?>
