<?php
/**
 * Process Marketplace Item Update
 * File: marketplace_update_process.php
 * Handles the actual update logic (separated from the edit page)
 */

ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once __DIR__ . '/config.php';
    $pdo = getDBConnection();
    
    // Check authentication
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Unauthorized: Please log in"]);
        exit;
    }
    
    // Get item ID
    $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    
    if ($itemId === 0) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Invalid item ID"]);
        exit;
    }
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id, status FROM marketplace_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Item not found"]);
        exit;
    }
    
    if ($item['user_id'] != $userId) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "You don't have permission to edit this item"]);
        exit;
    }
    
    // Validate required fields
    $required = ['title', 'category', 'description', 'city'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            ob_end_clean();
            echo json_encode(["success" => false, "message" => "Missing required field: $field"]);
            exit;
        }
    }
    
    // Sanitize inputs
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $isFree = !empty($_POST['is_free']) ? 1 : 0;
    $price = $isFree ? 0 : (float)($_POST['price'] ?? 0);
    $city = trim($_POST['city']);
    
    // Map city to state
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
    $hasPhone = !empty($_POST['contact_phone']);
    $hasEmail = !empty($_POST['contact_email']);
    $allowChat = !empty($_POST['allow_chat']) ? 1 : 0;
    
    if (!$hasPhone && !$hasEmail && !$allowChat) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "At least one contact method is required"]);
        exit;
    }
    
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
    
    // Validate email format if provided
    if ($hasEmail && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update marketplace item
    $updateStmt = $pdo->prepare("
        UPDATE marketplace_items 
        SET 
            title = ?, 
            category = ?, 
            description = ?, 
            price = ?, 
            is_free = ?,
            city = ?, 
            state_code = ?, 
            contact_method = ?, 
            contact_phone = ?, 
            contact_email = ?, 
            allow_chat = ?, 
            updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    
    $updateStmt->execute([
        $title, 
        $category, 
        $description, 
        $price, 
        $isFree,
        $city, 
        $stateCode, 
        $contactMethod, 
        $contactPhone, 
        $contactEmail, 
        $allowChat, 
        $itemId, 
        $userId
    ]);
    
    // Handle photo removals
    if (!empty($_POST['remove_photos']) && is_array($_POST['remove_photos'])) {
        $photosToRemove = array_map('intval', $_POST['remove_photos']);
        
        foreach ($photosToRemove as $photoId) {
            // Get photo path
            $photoStmt = $pdo->prepare("SELECT photo_path FROM marketplace_photos WHERE id = ? AND item_id = ?");
            $photoStmt->execute([$photoId, $itemId]);
            $photoPath = $photoStmt->fetchColumn();
            
            if ($photoPath) {
                $fullPath = __DIR__ . '/' . $photoPath;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            
            // Delete from database
            $deletePhotoStmt = $pdo->prepare("DELETE FROM marketplace_photos WHERE id = ? AND item_id = ?");
            $deletePhotoStmt->execute([$photoId, $itemId]);
        }
        
        // Reorder remaining photos
        $reorderStmt = $pdo->prepare("
            SELECT id FROM marketplace_photos 
            WHERE item_id = ? 
            ORDER BY photo_order ASC
        ");
        $reorderStmt->execute([$itemId]);
        $remainingPhotos = $reorderStmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($remainingPhotos as $index => $photoId) {
            $updateOrderStmt = $pdo->prepare("UPDATE marketplace_photos SET photo_order = ? WHERE id = ?");
            $updateOrderStmt->execute([$index + 1, $photoId]);
        }
    }
    
    // Handle new photo uploads
    if (!empty($_FILES['new_photos']['name'][0])) {
        // Count existing photos
        $photoCountStmt = $pdo->prepare("SELECT COUNT(*) FROM marketplace_photos WHERE item_id = ?");
        $photoCountStmt->execute([$itemId]);
        $currentPhotoCount = (int)$photoCountStmt->fetchColumn();
        
        $uploadDir = __DIR__ . "/uploads/marketplace/";
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
        
        if (!is_writable($uploadDir)) {
            throw new Exception("Upload directory is not writable");
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $photoCount = $currentPhotoCount;
        $uploadedCount = 0;
        $fileCount = count($_FILES['new_photos']['name']);
        
        for ($i = 0; $i < $fileCount && $photoCount < 5; $i++) {
            if ($_FILES['new_photos']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $tmpName = $_FILES['new_photos']['tmp_name'][$i];
            $fileName = $_FILES['new_photos']['name'][$i];
            $fileSize = $_FILES['new_photos']['size'][$i];
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            
            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }
            
            if ($fileSize > $maxFileSize) {
                continue;
            }
            
            // Generate unique filename
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uniqueName = 'marketplace_' . $itemId . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $uniqueName;
            $relativePath = 'uploads/marketplace/' . $uniqueName;
            
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $photoOrder = $photoCount + 1;
                $insertPhotoStmt = $pdo->prepare("
                    INSERT INTO marketplace_photos (item_id, photo_path, photo_order, file_size, mime_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertPhotoStmt->execute([$itemId, $relativePath, $photoOrder, $fileSize, $fileType]);
                $photoCount++;
                $uploadedCount++;
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Clear output buffer
    ob_end_clean();
    
    echo json_encode([
        "success" => true,
        "message" => "Listing updated successfully!",
        "item_id" => $itemId
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error in marketplace_update_process.php: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Database error occurred"]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in marketplace_update_process.php: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

exit;
?>