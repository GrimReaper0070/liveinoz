<?php
require_once 'config.php';

// Set your desired admin email and password here
$adminEmail = 'admin@example.com';
$newPassword = 'AdminPass123!'; // Change this to your preferred secure password

try {
    $pdo = getDBConnection();
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the admin user's password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashedPassword, $adminEmail]);
    
    if ($result) {
        echo "<h1>Admin Password Updated Successfully!</h1>";
        echo "<p>Email: " . htmlspecialchars($adminEmail) . "</p>";
        echo "<p>Password has been updated. Please remember to change this to your own secure password.</p>";
        echo "<p><strong>Current password set to:</strong> " . htmlspecialchars($newPassword) . "</p>";
        echo "<p><a href='login.html'>Go to Login Page</a></p>";
    } else {
        echo "<h1>Error Updating Password</h1>";
        echo "<p>Failed to update admin password. Please check if the admin user exists.</p>";
    }
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>