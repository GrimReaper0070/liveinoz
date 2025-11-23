<?php
// Simple test for mark as sold
session_start();
require 'config.php';

echo "Testing mark as sold functionality\n\n";

// Simulate logged in user
$_SESSION['user_id'] = 2; // Assuming this user exists

$pdo = getDBConnection();

// Get first approved item for the test user
$stmt = $pdo->query("SELECT id, title, status FROM marketplace_items WHERE user_id = 2 LIMIT 1");
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "ERROR: No items found for user 2. Create some items first.\n";
    exit;
}

echo "Found item ID: {$item['id']}\n";
echo "Title: {$item['title']}\n";
echo "Current status: {$item['status']}\n\n";

// Test the database update directly
echo "Testing direct database update...\n";
$updateStmt = $pdo->prepare("UPDATE marketplace_items SET status = 'sold', updated_at = NOW() WHERE id = ?");
$result = $updateStmt->execute([$item['id']]);

if ($result && $updateStmt->rowCount() > 0) {
    echo "SUCCESS: Database update successful\n";
} else {
    echo "ERROR: Database update failed\n";
}

// Check result
$stmt = $pdo->prepare("SELECT status, updated_at FROM marketplace_items WHERE id = ?");
$stmt->execute([$item['id']]);
$updated = $stmt->fetch(PDO::FETCH_ASSOC);

echo "New status: {$updated['status']}\n";
echo "Updated at: {$updated['updated_at']}\n";
?>
