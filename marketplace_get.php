<?php

// marketplace_get.php (can be in root or api folder)

// Start output buffering to catch any errors
ob_start();

// Disable error display completely - show errors only in log
ini_set('display_errors', 1); // Temporarily enable to see errors
error_reporting(E_ALL);

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Include config from root folder
    require_once __DIR__ . '/config.php';
    
    $pdo = getDBConnection();
    
    // Get filter parameters
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $city = isset($_GET['city']) ? trim($_GET['city']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
    $status = 'approved';
    
    // Build query
    $sql = "SELECT 
                mi.id,
                mi.title,
                mi.category,
                mi.description,
                mi.price,
                mi.is_free,
                mi.city,
                mi.state_code,
                mi.contact_method,
                mi.contact_phone,
                mi.contact_email,
                mi.allow_chat,
                mi.view_count,
                mi.created_at,
                mi.user_id,
                u.first_name,
                u.last_name
            FROM marketplace_items mi
            JOIN users u ON mi.user_id = u.id
            WHERE mi.status = ?";
    
    $params = [$status];
    
    // Add filters
    if (!empty($category)) {
        if ($category === 'free_items') {
            $sql .= " AND mi.is_free = 1";
        } else {
            $sql .= " AND mi.category = ?";
            $params[] = $category;
        }
    }
    
    if (!empty($city)) {
        $sql .= " AND mi.city = ?";
        $params[] = $city;
    }
    
    // Add sorting
    switch ($sort) {
        case 'price_low':
            $sql .= " ORDER BY mi.is_free DESC, mi.price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY mi.is_free ASC, mi.price DESC";
            break;
        case 'popular':
            $sql .= " ORDER BY mi.view_count DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY mi.created_at DESC";
            break;
    }
    
    // Add limit
    $sql .= " LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get photos for each item efficiently
    if (!empty($items)) {
        $itemIds = array_column($items, 'id');
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        
        $photoStmt = $pdo->prepare("
            SELECT item_id, photo_path, photo_order
            FROM marketplace_photos 
            WHERE item_id IN ($placeholders)
            ORDER BY item_id, photo_order ASC
        ");
        $photoStmt->execute($itemIds);
        $allPhotos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group photos by item_id
        $photosByItem = [];
        foreach ($allPhotos as $photo) {
            $photosByItem[$photo['item_id']][] = $photo['photo_path'];
        }
        
        // Assign photos to items
        foreach ($items as &$item) {
            $item['photos'] = isset($photosByItem[$item['id']]) ? $photosByItem[$item['id']] : [];
        }
        unset($item);
    }
    
    // Clear any warnings/errors from output buffer
    ob_end_clean();
    
    // Output clean JSON
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ], JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    // Clear output buffer
    ob_end_clean();
    
    // Log error
    error_log("Marketplace DB error: " . $e->getMessage());
    
    // Return error JSON
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'items' => []
    ]);
    
} catch (Exception $e) {
    // Clear output buffer
    ob_end_clean();
    
    // Log error
    error_log("Marketplace error: " . $e->getMessage());
    
    // Return error JSON
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'items' => []
    ]);
}

exit;
?>