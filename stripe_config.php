<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

$pdo = getDBConnection();



// Webhook secret for verifying webhook signatures
define('STRIPE_WEBHOOK_SECRET', 'whsec_42eb7c8a46ba3d478b56a9b92f74b2ac103c70639ec93eaa5795e8653e326273');

// JWT secret for API authentication
define('JWT_SECRET', '4bac29868065a5aec7e18b346c303494e0ce29d872e2cb2dc7d6a008e56ecaa7fb3e223a54be24a8156266bfeee673bbd198257ec3f8512e54c873fddf7755e5');

// Frontend URL for success/cancel redirects
define('FRONTEND_URL', 'http://localhost/oznewfinal');
?>
