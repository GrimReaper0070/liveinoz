<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT id, name, description, image, whatsapp_link, state_code, city_name
        FROM whatsapp_groups
        WHERE is_active = 1
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'groups' => $groups]);
} catch (Exception $e) {
    error_log("Get WhatsApp groups error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve WhatsApp groups']);
}
?>