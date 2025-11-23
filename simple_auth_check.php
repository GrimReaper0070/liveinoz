<?php
require_once 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "Authenticated: YES\n";
    echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n";
} else {
    echo "Authenticated: NO\n";
}
?>