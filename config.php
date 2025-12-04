<?php
// Database configuration for DigitalOcean Managed MySQL
define('DB_HOST', 'db-mysql-sgp1-25636-do-user-27734141-0.i.db.ondigitalocean.com');
define('DB_PORT', '25060');
define('DB_NAME', 'oznew');
define('DB_USER', 'doadmin');
define('DB_PASS', getenv('DB_PASSWORD'));  // keep secure!

// Create connection
function getDBConnection() {
    try {
        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca-certificate.crt', // DigitalOcean SSL certificate
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];

        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        return $pdo;
    } catch(PDOException $e) {
        error_log("DB Connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Configure session - only start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // leave empty for now, works across domains automatically
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
    session_start();
}
?>
