<?php
// marketplace_post.php

// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 1); // Set to 0 in production
error_reporting(E_ALL);

// Set CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: http://localhost:5500');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set JSON header
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Unknown error"];

try {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Include config
    require_once __DIR__ . '/config.php';
    
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Unauthorized: Please log in first."]);
        exit;
    }

    // Check if user is verified
    $stmt = $pdo->prepare("SELECT is_verified, user_type FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "User not found or inactive."]);
        exit;
    }
    
    if ($user['is_verified'] != 1) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Account not verified. Please wait for admin approval."]);
        exit;
    }
    
    if ($user['user_type'] != 'full_access') {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Your account type does not allow posting marketplace items."]);
        exit;
    }

    // Validate required fields
    $required = ['title', 'category', 'description', 'city'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            ob_end_clean();
            echo json_encode(["success" => false, "message" => "Missing or empty field: $field"]);
            exit;
        }
    }

    // Validate photos
    if (empty($_FILES['photos']['name'][0])) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "At least one photo is required."]);
        exit;
    }

    // Validate contact method
    $hasPhone = !empty($_POST['contact_phone']);
    $hasEmail = !empty($_POST['contact_email']);
    $allowChat = !empty($_POST['allow_chat']) ? 1 : 0;

    if (!$hasPhone && !$hasEmail && !$allowChat) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Please provide at least one contact method."]);
        exit;
    }

    // Validate email format if provided
    if ($hasEmail && !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // Sanitize input data
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $isFree = !empty($_POST['is_free']) ? 1 : 0;
    $price = $isFree ? 0 : (float)$_POST['price'];
    $city = trim($_POST['city']);

    // Map city to state code
    $cityStateMap = [
        'Sydney' => 'NSW',
        'Melbourne' => 'VIC',
        'Brisbane' => 'QLD',
        'Perth' => 'WA',
        'Adelaide' => 'SA',
        'Hobart' => 'TAS',
        'Darwin' => 'NT',
        'Canberra' => 'ACT'
    ];
    $stateCode = $cityStateMap[$city] ?? 'NSW';

    // Contact methods
    $contactPhone = $hasPhone ? trim($_POST['contact_phone']) : null;
    $contactEmail = $hasEmail ? trim($_POST['contact_email']) : null;

    // Determine contact method
    if ($hasPhone && $hasEmail) {
        $contactMethod = 'multiple';
    } elseif ($hasPhone) {
        $contactMethod = 'phone';
    } elseif ($hasEmail) {
        $contactMethod = 'email';
    } else {
        $contactMethod = 'chat';
    }

    // Start transaction
    $pdo->beginTransaction();
    
    // Insert marketplace item
    $stmt = $pdo->prepare("
        INSERT INTO marketplace_items 
        (user_id, title, category, description, price, is_free, city, state_code, 
         contact_method, contact_phone, contact_email, allow_chat, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $userId, $title, $category, $description, $price, $isFree,
        $city, $stateCode, $contactMethod, $contactPhone, $contactEmail, $allowChat
    ]);
    
    $itemId = $pdo->lastInsertId();
    
    // Handle photo uploads
    $uploadDir = __DIR__ . "/uploads/marketplace/";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create uploads directory");
        }
    }

    // Check if uploads directory is writable
    if (!is_writable($uploadDir)) {
        throw new Exception("Uploads directory is not writable");
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    $photoCount = 0;
    $uploadErrors = [];
    $fileCount = count($_FILES['photos']['name']);
    
    for ($i = 0; $i < $fileCount && $photoCount < 5; $i++) {
        if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmpName = $_FILES['photos']['tmp_name'][$i];
        $fileName = $_FILES['photos']['name'][$i];
        $fileSize = $_FILES['photos']['size'][$i];
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
        
        if (!in_array($fileType, $allowedTypes)) {
            $uploadErrors[] = "Invalid file type for $fileName";
            continue;
        }
        
        // Validate file size
        if ($fileSize > $maxFileSize) {
            $uploadErrors[] = "File $fileName is too large (max 5MB)";
            continue;
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $uniqueName = 'marketplace_' . $itemId . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueName;
        $relativePath = 'uploads/marketplace/' . $uniqueName;
        
        // Move uploaded file
        if (move_uploaded_file($tmpName, $uploadPath)) {
            // Insert photo record
            $photoOrder = $photoCount + 1;
            $stmt = $pdo->prepare("
                INSERT INTO marketplace_photos (item_id, photo_path, photo_order, file_size, mime_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$itemId, $relativePath, $photoOrder, $fileSize, $fileType]);
            
            $photoCount++;
        } else {
            $uploadErrors[] = "Failed to upload $fileName";
        }
    }
    
    // Check if at least one photo was uploaded
    if ($photoCount === 0) {
        throw new Exception('No photos were successfully uploaded. ' . implode(', ', $uploadErrors));
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Clear output buffer
    ob_end_clean();
    
    $response = [
        "success" => true,
        "message" => "Item posted successfully with $photoCount photo(s)! Your listing will be reviewed by an administrator before being published.",
        "item_id" => $itemId,
        "photo_count" => $photoCount
    ];
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Marketplace posting error: " . $e->getMessage());
    
    ob_end_clean();
    
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
exit;
?>
