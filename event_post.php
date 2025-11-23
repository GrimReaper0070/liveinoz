<?php
// event_post.php - Handle event posting

// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

$response = ["success" => false, "message" => "Unknown error"];

try {
    require_once __DIR__ . '/config.php';

    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Unauthorized: Please log in first."]);
        exit;
    }

    // Check user verification and type
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
        echo json_encode(["success" => false, "message" => "Your account type does not allow posting events."]);
        exit;
    }

    // Validate required fields
    $required = ['title', 'description', 'city', 'event_date'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            ob_end_clean();
            echo json_encode(["success" => false, "message" => "Missing or empty field: $field"]);
            exit;
        }
    }

    // Validate poster upload
    if (empty($_FILES['poster']['name'])) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Event poster is required."]);
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

    // Validate email if provided
    if ($hasEmail && !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // Sanitize input
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $city = trim($_POST['city']);
    $eventDate = $_POST['event_date'];
    $eventTime = !empty($_POST['event_time']) ? $_POST['event_time'] : null;

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

    // Handle poster upload
    $uploadDir = __DIR__ . "/uploads/events/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!is_writable($uploadDir)) {
        throw new Exception("Upload directory is not writable");
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    $tmpName = $_FILES['poster']['tmp_name'];
    $fileName = $_FILES['poster']['name'];
    $fileSize = $_FILES['poster']['size'];

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($fileType, $allowedTypes)) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Invalid file type. Only JPEG, PNG, and WebP are allowed."]);
        exit;
    }

    if ($fileSize > $maxFileSize) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "File too large. Maximum size is 5MB."]);
        exit;
    }

    // Generate unique filename
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $uniqueName = 'event_' . uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueName;
    $relativePath = 'uploads/events/' . $uniqueName;

    if (!move_uploaded_file($tmpName, $uploadPath)) {
        throw new Exception("Failed to upload poster");
    }

    // Insert event
    $stmt = $pdo->prepare("
        INSERT INTO events
        (user_id, title, description, city, state_code, event_date, event_time,
         contact_method, contact_phone, contact_email, allow_chat, poster_path, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $userId, $title, $description, $city, $stateCode, $eventDate, $eventTime,
        $contactMethod, $contactPhone, $contactEmail, $allowChat, $relativePath
    ]);

    $eventId = $pdo->lastInsertId();

    // Commit transaction
    $pdo->commit();

    ob_end_clean();

    $response = [
        "success" => true,
        "message" => "Event posted successfully! Your event will be reviewed by an administrator before being published.",
        "event_id" => $eventId
    ];

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Event posting error: " . $e->getMessage());

    ob_end_clean();

    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
exit;
?>
