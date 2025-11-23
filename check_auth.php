<?php
// Debug version of check_auth.php

header('Access-Control-Allow-Origin: http://localhost:5500');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log session data for debugging
error_log("=== SESSION DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("Session status: " . session_status());

// Check each session variable individually
$sessionChecks = [
    'user_id' => isset($_SESSION['user_id']),
    'logged_in' => isset($_SESSION['logged_in']),
    'logged_in_value' => $_SESSION['logged_in'] ?? 'not_set',
    'first_name' => $_SESSION['first_name'] ?? 'not_set',
    'last_name' => $_SESSION['last_name'] ?? 'not_set',
    'email' => $_SESSION['email'] ?? 'not_set',
    'role' => $_SESSION['role'] ?? 'not_set'
];

error_log("Session checks: " . print_r($sessionChecks, true));

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// If logged in, check verification status from database
$verified = false;
if ($loggedIn) {
    try {
        require_once 'config.php';
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT is_verified, user_type FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $verified = $user && $user['is_verified'] == 1;
        $userType = $user ? $user['user_type'] : null;
    } catch (Exception $e) {
        error_log("Verification check error: " . $e->getMessage());
        $verified = false;
    }
}

$authenticated = $loggedIn && $verified;

$response = [
    'loggedIn' => $loggedIn,
    'authenticated' => $authenticated,
    'verified' => $verified,
    'user' => $authenticated ? [
        'id' => $_SESSION['user_id'],
        'name' => ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''),
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'user_type' => $userType
    ] : null,
    'debug' => [
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_checks' => $sessionChecks,
        'php_session' => $_SESSION
    ]
];

error_log("Response: " . json_encode($response));

echo json_encode($response);
?>
