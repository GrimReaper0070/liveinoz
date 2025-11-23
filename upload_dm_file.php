<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$thread_id = trim($_POST['thread_id'] ?? '');

if (empty($thread_id)) {
    echo json_encode(['success' => false, 'message' => 'Thread ID required']);
    exit;
}

// Verify user is participant in this thread
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT id FROM dm_threads
        WHERE thread_id = ? AND (participant1_id = ? OR participant2_id = ?) AND is_active = TRUE
    ");
    $stmt->execute([$thread_id, $user_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid thread or access denied']);
        exit;
    }
} catch (Exception $e) {
    error_log("Thread verification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Verification error']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];
$file_name = $file['name'];
$file_size = $file['size'];
$file_tmp = $file['tmp_name'];
$file_type = $file['type'];

// Validate file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4'];
$max_size = 15 * 1024 * 1024; // 15MB

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit;
}

if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 15MB)']);
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/dm_files/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
$unique_filename = uniqid('dm_file_', true) . '.' . $file_extension;
$file_path = $upload_dir . $unique_filename;

// Move uploaded file
if (!move_uploaded_file($file_tmp, $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Determine file category
$file_category = 'other';
if (strpos($file_type, 'image/') === 0) {
    $file_category = 'image';
} elseif ($file_type === 'application/pdf') {
    $file_category = 'pdf';
} elseif ($file_type === 'video/mp4') {
    $file_category = 'video';
}

// Calculate expiration time (24 hours from now)
$expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

try {
    // Insert file record
    $stmt = $pdo->prepare("
        INSERT INTO file_attachments (message_id, dm_message_id, file_path, original_name, file_size, mime_type, uploaded_by, expires_at)
        VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$file_path, $file_name, $file_size, $file_type, $user_id, $expires_at]);
    $file_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'file_id' => $file_id,
        'file_path' => $file_path,
        'file_name' => $file_name,
        'file_size' => $file_size,
        'file_type' => $file_type,
        'file_category' => $file_category,
        'expires_at' => $expires_at
    ]);

} catch (Exception $e) {
    // Clean up uploaded file if database insert fails
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    error_log("File upload database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save file information']);
}
?>
