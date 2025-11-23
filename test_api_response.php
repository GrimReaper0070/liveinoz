<?php
require_once 'config.php';

// Start session and set user_id to bypass auth
session_start();
$_SESSION['user_id'] = 2; // Use admin user

// Simulate API call for NSW Sydney
$_GET['state'] = 'NSW';
$_GET['city'] = 'Sydney';

ob_start();
include 'chat_rooms.php';
$content = ob_get_clean();

echo "API Response:\n";
echo $content;
?>
