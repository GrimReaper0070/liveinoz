<?php
header('Content-Type: application/json');
session_start();
require 'config.php';

$pdo = getDBConnection();

try {
    $stmt = $pdo->query("SELECT j.*, u.first_name, u.last_name FROM jobs j JOIN users u ON j.posted_by = u.id ORDER BY j.created_at DESC");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($jobs)) {
        echo json_encode(['success' => true, 'jobs' => [], 'message' => 'No jobs found']);
    } else {
        echo json_encode(['success' => true, 'jobs' => $jobs]);
    }
} catch (PDOException $e) {
    error_log("Fetch jobs error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching jobs. Please try again.']);
}
?>