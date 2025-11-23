<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);  // hide PHP errors from output
require 'config.php';

$pdo = getDBConnection();
$response = ["success" => false, "message" => "Unknown error"];

try {
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Unauthorized: Please log in first."]);
        exit;
    }

    // Check if user is verified and has full access
    $stmt = $pdo->prepare("SELECT is_verified, user_type, plan_type, active_listings_limit, plan_expires_at FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['is_verified'] != 1) {
        echo json_encode(["success" => false, "message" => "Account not verified. Please wait for admin approval."]);
        exit;
    }
    if ($user['user_type'] != 'full_access') {
        echo json_encode(["success" => false, "message" => "Your account type does not allow posting rooms."]);
        exit;
    }

    // Check current active listings count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_rooms
        FROM rooms
        WHERE user_id = ? AND is_approved = 1 AND (expires_at IS NULL OR expires_at > NOW())
    ");
    $stmt->execute([$userId]);
    $activeRooms = $stmt->fetch(PDO::FETCH_ASSOC)['active_rooms'] ?? 0;

    $planType = $user['plan_type'] ?: 'free';
    $listingsLimit = $user['active_listings_limit'] ?: 1;

    // Check if plan is expired
    $planExpired = false;
    if ($user['plan_expires_at'] && strtotime($user['plan_expires_at']) < time()) {
        $planExpired = true;
        $planType = 'free';
        $listingsLimit = 1;
    }

    // Check if user can post more rooms
    if ($activeRooms >= $listingsLimit) {
        $upgradeMessage = $planExpired ?
            "Your plan has expired. Renew your subscription to post more rooms." :
            "You've reached your listing limit. Upgrade your plan to post more rooms.";

        echo json_encode([
            "success" => false,
            "message" => $upgradeMessage,
            "upgrade_required" => true,
            "current_plan" => $planType,
            "active_rooms" => $activeRooms,
            "listings_limit" => $listingsLimit,
            "plan_expired" => $planExpired
        ]);
        exit;
    }

    // Validate required fields
    $required = ['address', 'suburb', 'city', 'rent', 'bond', 'contactName', 'contactNumber', 'description'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "Missing or empty field: $field"]);
            exit;
        }
    }

    // Handle photos
    $uploadedPhotos = [];
    if (!empty($_FILES['photos']['name'][0])) {
        // Dynamic path detection for local/live compatibility
        if (strpos(__DIR__, 'oznewfinal') !== false) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/oznewfinal/uploads/";
        } else {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
        }
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                error_log("Failed to create uploads directory: " . $uploadDir);
                throw new Exception("Failed to create uploads directory");
            }
        }

        // Check if uploads directory is writable
        if (!is_writable($uploadDir)) {
            error_log("Uploads directory is not writable: " . $uploadDir);
            throw new Exception("Uploads directory is not writable");
        }

        $fileCount = count($_FILES['photos']['name']);
        for ($i = 0; $i < $fileCount && $i < 5; $i++) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
                error_log("File upload error for file " . $_FILES['photos']['name'][$i] . ": " . $_FILES['photos']['error'][$i]);
                continue; // Skip files with upload errors
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['photos']['tmp_name'][$i]);
            if (!in_array($fileType, $allowedTypes)) {
                error_log("Invalid file type for file " . $_FILES['photos']['name'][$i] . ": " . $fileType);
                continue; // Skip files with invalid types
            }

            // Validate file size (5MB max)
            if ($_FILES['photos']['size'][$i] > 5 * 1024 * 1024) {
                error_log("File too large: " . $_FILES['photos']['name'][$i]);
                continue; // Skip files that are too large
            }

            $fileName = uniqid() . "_" . basename($_FILES['photos']['name'][$i]);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $targetFile)) {
                // Store relative path in database
                $uploadedPhotos[] = $fileName;
                error_log("Successfully uploaded file: " . $fileName);
            } else {
                error_log("Failed to move uploaded file: " . $_FILES['photos']['name'][$i] . " to " . $targetFile);
            }
        }
    }

    // Debug logging
    error_log("Total uploaded photos: " . count($uploadedPhotos));
    error_log("Uploaded photo filenames: " . implode(", ", $uploadedPhotos));
    error_log("POST data received: " . print_r($_POST, true));
    error_log("FILES data received: " . print_r($_FILES, true));

    // Insert into DB (room starts as unapproved)
    $stmt = $pdo->prepare("
        INSERT INTO rooms
        (user_id, address, suburb, city, rent, bond, contact_name, contact_number, description, photo1, photo2, photo3, photo4, photo5, is_approved)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE)
    ");

    $stmt->execute([
        $userId,
        $_POST['address'],
        $_POST['suburb'],
        $_POST['city'],
        (float)$_POST['rent'],
        (float)$_POST['bond'],
        $_POST['contactName'],
        $_POST['contactNumber'],
        $_POST['description'],
        $uploadedPhotos[0] ?? null,
        $uploadedPhotos[1] ?? null,
        $uploadedPhotos[2] ?? null,
        $uploadedPhotos[3] ?? null,
        $uploadedPhotos[4] ?? null
    ]);

    // Update user's room posts count
    $updateStmt = $pdo->prepare("UPDATE users SET room_posts_count = room_posts_count + 1 WHERE id = ?");
    $updateStmt->execute([$userId]);

    // Get updated stats for response
    $stmt = $pdo->prepare("
        SELECT COUNT(r.id) as active_rooms, u.room_posts_count
        FROM users u
        LEFT JOIN rooms r ON u.id = r.user_id AND r.is_approved = 1 AND (r.expires_at IS NULL OR r.expires_at > NOW())
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare success message with photo info
    $photoCount = count($uploadedPhotos);
    $photoMessage = $photoCount > 0 ? " with $photoCount photo(s)" : " (no photos uploaded)";
    $response = [
        "success" => true,
        "message" => "Room posted successfully$photoMessage! The listing will be reviewed by an administrator before being published.",
        "active_rooms" => $stats['active_rooms'] ?? 0,
        "listings_limit" => $listingsLimit,
        "total_posts" => $stats['room_posts_count'] ?? 0
    ];

} catch (Exception $e) {
    error_log("Post room error: " . $e->getMessage());
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
exit;
