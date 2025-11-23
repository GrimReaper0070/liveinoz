<?php
// This script generates a secure password hash that you can use in your database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        echo "<h1>Password Hash Generated</h1>";
        echo "<p><strong>Plain text password:</strong> " . htmlspecialchars($password) . "</p>";
        echo "<p><strong>Hashed password:</strong> " . htmlspecialchars($hashedPassword) . "</p>";
        echo "<p>You can use this hash to update your database directly:</p>";
        echo "<pre>UPDATE users SET password = '" . $hashedPassword . "' WHERE email = 'admin@example.com';</pre>";
        echo "<hr>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Password Hash</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #1a1a2e; color: white; }
        .container { max-width: 600px; margin: 0 auto; }
        input, button { padding: 10px; font-size: 16px; margin: 5px 0; }
        button { background: #00eeff; color: #000; border: none; cursor: pointer; }
        pre { background: #333; padding: 15px; border-radius: 5px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Secure Password Hash</h1>
        <p>Enter a password below to generate a secure hash for your admin user:</p>
        
        <form method="POST">
            <input type="password" name="password" placeholder="Enter password" required style="width: 100%;">
            <button type="submit">Generate Hash</button>
        </form>
        
        <h2>Instructions:</h2>
        <ol>
            <li>Enter a strong password (at least 8 characters with numbers and symbols)</li>
            <li>Click "Generate Hash"</li>
            <li>Copy the hashed password</li>
            <li>Use it to update your admin user in the database</li>
        </ol>
    </div>
</body>
</html>