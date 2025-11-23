<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Checking users table for profile pictures...\n";

    // Get all users with profile_picture
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, profile_picture FROM users WHERE profile_picture IS NOT NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Users with profile pictures:\n";
    if (count($users) > 0) {
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Email: {$user['email']}, Picture: {$user['profile_picture']}\n";
        }
    } else {
        echo "No users have profile pictures set.\n";
    }

    // Check total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total = $stmt->fetch()['total'];
    echo "\nTotal users: $total\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
