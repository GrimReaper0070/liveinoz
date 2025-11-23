<?php
// Prevent any HTML output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Global error handler to catch any PHP errors
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errstr in $errfile:$errline");
    echo json_encode(["success" => false, "message" => "PHP Error: $errstr (line $errline)"]);
    exit;
}

// Global exception handler
function handleException($exception) {
    error_log("PHP Exception: " . $exception->getMessage());
    echo json_encode(["success" => false, "message" => "Exception: " . $exception->getMessage()]);
    exit;
}

set_error_handler("handleError");
set_exception_handler("handleException");

require 'config.php';

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database connection error"]);
    exit;
}

// Check if user is logged in and verified (consistent with other endpoints)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

// Check if user is active (allow deletion of existing jobs even if not verified)
$userId = $_SESSION['user_id'];
try {
    $userCheckStmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $userCheckStmt->execute([$userId]);
    $user = $userCheckStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['is_active'] != 1) {
        echo json_encode(["success" => false, "message" => "Account inactive"]);
        exit;
    }
} catch (PDOException $e) {
    error_log("User check error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error checking user status"]);
    exit;
}

$jobId = $_POST['id'] ?? null;

// Validate job ID
if (!$jobId || !is_numeric($jobId)) {
    echo json_encode(["success" => false, "message" => "Valid job ID required"]);
    exit;
}

try {
    error_log("Delete job attempt: Job ID: $jobId, User ID: $userId");

    // Check if the job exists and belongs to the user
    $checkStmt = $pdo->prepare("SELECT id, posted_by FROM jobs WHERE id = ? AND posted_by = ?");
    $checkStmt->execute([$jobId, $userId]);
    $jobData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    error_log("Check result: " . ($jobData ? "Found job, posted_by: {$jobData['posted_by']}" : "Job not found"));

    if (!$jobData) {
        echo json_encode(["success" => false, "message" => "Job not found or you don't have permission to delete it"]);
        exit;
    }

    // Delete the job
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND posted_by = ?");
    $result = $stmt->execute([$jobId, $userId]);
    $deleteAffected = $stmt->rowCount();

    error_log("Delete job execute result: " . ($result ? "true" : "false") . ", affected: $deleteAffected");

    if (!$result) {
        error_log("Delete job failed: execute returned false");
        echo json_encode(["success" => false, "message" => "Failed to delete job - database error"]);
        exit;
    }

    if ($deleteAffected === 0) {
        error_log("Delete job failed: no rows affected");
        echo json_encode(["success" => false, "message" => "Failed to delete job - no matching job found"]);
        exit;
    }

    error_log("Delete job successful: Job ID: $jobId, User ID: $userId, affected: $deleteAffected");

    echo json_encode(["success" => true, "message" => "Job deleted successfully"]);

} catch (PDOException $e) {
    error_log("Delete job error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error deleting job. Please try again."]);
}
?>
