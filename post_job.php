<?php
header('Content-Type: application/json');
session_start();
require 'config.php';

$pdo = getDBConnection();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user. Please login.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if user is verified and has full access
$stmt = $pdo->prepare("SELECT is_verified, user_type FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['is_verified'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Account not verified. Please wait for admin approval.']);
    exit;
}
if ($user['user_type'] != 'full_access') {
    echo json_encode(['success' => false, 'message' => 'Your account type does not allow posting jobs.']);
    exit;
}

// Validate required fields
$required = ['jobTitle', 'company', 'jobLocation', 'contactEmail', 'contactPhone', 'jobDescription'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Validate email format
if (!filter_var($_POST['contactEmail'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Sanitize input data
$jobTitle = trim($_POST['jobTitle']);
$company = trim($_POST['company']);
$jobLocation = trim($_POST['jobLocation']);
$contactEmail = trim($_POST['contactEmail']);
$contactPhone = trim($_POST['contactPhone']);
$jobDescription = trim($_POST['jobDescription']);

try {
    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, location, email, phone, description, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $jobTitle,
        $company,
        $jobLocation,
        $contactEmail,
        $contactPhone,
        $jobDescription,
        $userId
    ]);

    echo json_encode(['success' => true, 'message' => 'Job posted successfully!']);
} catch (PDOException $e) {
    error_log("Job posting error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error posting job. Please try again.']);
}
?>
