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
        SELECT id, title, content, image, created_at
        FROM blog_posts
        WHERE status = 'published'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'posts' => $posts]);
} catch (Exception $e) {
    error_log("Get blog posts error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve blog posts']);
}
?>