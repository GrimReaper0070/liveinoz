<?php
require_once 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && 
    isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo json_encode(['is_admin' => true]);
} else {
    echo json_encode(['is_admin' => false]);
}
?>