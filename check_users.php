<?php
// Enhanced script to check database setup and users
require_once 'config.php';

echo "=== Live in Oz Database Diagnostic Tool ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $pdo = getDBConnection();
    echo "✅ Database connection successful!\n\n";
    
    // Check if database exists
    echo "2. Checking database structure...\n";
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "✅ Connected to database: " . $dbName . "\n";
    
    // Check if tables exist
    $tables = ['users', 'jobs', 'rooms'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
        }
    }
    echo "\n";
    
    // Check users table structure
    echo "3. Checking users table structure...\n";
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Users table columns:\n";
        foreach ($columns as $column) {
            echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking users table structure: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Check if users exist
    echo "4. Checking users data...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "✅ Found " . $result['count'] . " user(s) in the database:\n\n";
        
        // Get all users (without passwords)
        $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, is_active, created_at FROM users ORDER BY id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            echo "📋 User #" . $user['id'] . ":\n";
            echo "   Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
            echo "   Email: " . $user['email'] . "\n";
            echo "   Role: " . $user['role'] . "\n";
            echo "   Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
            echo "   Created: " . $user['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "⚠️  No users found in the database.\n";
        echo "💡 To create your first user:\n";
        echo "   1. Go to signup.html in your browser\n";
        echo "   2. Fill out the registration form\n";
        echo "   3. Or run the database setup SQL to create an admin user\n\n";
    }
    
    // Check jobs table
    echo "5. Checking jobs data...\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM jobs");
        $jobResult = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Found " . $jobResult['count'] . " job(s) in the database\n";
    } catch (PDOException $e) {
        echo "⚠️  Could not check jobs table: " . $e->getMessage() . "\n";
    }
    
    // Check rooms table
    echo "\n6. Checking rooms data...\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms");
        $roomResult = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Found " . $roomResult['count'] . " room(s) in the database\n";
    } catch (PDOException $e) {
        echo "⚠️  Could not check rooms table: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Diagnostic Complete ===\n";
    
    // Summary and recommendations
    echo "\n💡 Next Steps:\n";
    if ($result['count'] == 0) {
        echo "• Create your first user by visiting signup.html\n";
        echo "• Or insert sample data using the provided SQL\n";
    } else {
        echo "• Database setup looks good!\n";
        echo "• You can now test login functionality\n";
        echo "• Try posting jobs and rooms\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n\n";
    echo "🔧 Troubleshooting steps:\n";
    echo "1. Check if XAMPP MySQL service is running\n";
    echo "2. Verify database credentials in config.php:\n";
    echo "   - Host: " . DB_HOST . "\n";
    echo "   - Database: " . DB_NAME . "\n";
    echo "   - User: " . DB_USER . "\n";
    echo "3. Run the database setup SQL script\n";
    echo "4. Check MySQL error logs in XAMPP\n\n";
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
    echo "Please check your PHP configuration and file permissions.\n";
}

// Show current PHP and MySQL versions for debugging
echo "\n📊 System Info:\n";
echo "PHP Version: " . phpversion() . "\n";
try {
    $stmt = $pdo->query("SELECT VERSION()");
    $mysqlVersion = $stmt->fetchColumn();
    echo "MySQL Version: " . $mysqlVersion . "\n";
} catch (Exception $e) {
    echo "MySQL Version: Could not determine\n";
}
?>