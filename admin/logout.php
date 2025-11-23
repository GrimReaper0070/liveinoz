<?php
require_once '../config.php';

// Destroy all session data
session_start();
session_unset();
session_destroy();

// Redirect to admin login page
header('Location: login.php');
exit();
?>