<?php
// test_marketplace.php - Place this in oznewfinal1 folder
// Visit: http://localhost/oznewfinal1/test_marketplace.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Marketplace Diagnostic Tool</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#1a1a1a;color:#fff;}h2{color:#00f0ff;border-bottom:2px solid #00f0ff;padding-bottom:5px;}pre{background:#000;padding:10px;border:1px solid #00f0ff;overflow:auto;}.success{color:#00ff00;}.error{color:#ff0000;}</style>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo (version_compare(phpversion(), '7.0.0', '>=')) ? "<span class='success'>✓ PHP 7.0+ (Good)</span>" : "<span class='error'>✗ PHP version too old</span>";
echo "<br><br>";

// Test 2: Check if config.php exists
echo "<h2>2. Config File</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<span class='success'>✓ config.php found</span><br>";
    
    try {
        require_once __DIR__ . '/config.php';
        echo "<span class='success'>✓ config.php loaded successfully</span><br>";
        
        // Test 3: Database Connection
        echo "<h2>3. Database Connection</h2>";
        $pdo = getDBConnection();
        echo "<span class='success'>✓ Database connected successfully</span><br>";
        echo "Database: " . DB_NAME . "<br><br>";
        
        // Test 4: Check Tables
        echo "<h2>4. Database Tables</h2>";
        $tables = ['users', 'marketplace_items', 'marketplace_photos'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<span class='success'>✓ $table exists</span><br>";
                
                // Count records
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "&nbsp;&nbsp;&nbsp;Records: $count<br>";
            } else {
                echo "<span class='error'>✗ $table does NOT exist</span><br>";
            }
        }
        echo "<br>";
        
        // Test 5: Check marketplace_get.php
        echo "<h2>5. API Files</h2>";
        if (file_exists(__DIR__ . '/marketplace_get.php')) {
            echo "<span class='success'>✓ marketplace_get.php found</span><br>";
        } else {
            echo "<span class='error'>✗ marketplace_get.php NOT found</span><br>";
        }
        
        if (file_exists(__DIR__ . '/marketplace_post.php')) {
            echo "<span class='success'>✓ marketplace_post.php found</span><br>";
        } else {
            echo "<span class='error'>✗ marketplace_post.php NOT found</span><br>";
        }
        echo "<br>";
        
        // Test 6: Try to fetch marketplace items
        echo "<h2>6. Test Marketplace Query</h2>";
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    mi.id,
                    mi.title,
                    mi.price,
                    mi.is_free,
                    mi.city
                FROM marketplace_items mi
                JOIN users u ON mi.user_id = u.id
                WHERE mi.status = 'approved'
                LIMIT 5
            ");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<span class='success'>✓ Query executed successfully</span><br>";
            echo "Found " . count($items) . " approved items<br>";
            
            if (count($items) > 0) {
                echo "<pre>" . print_r($items, true) . "</pre>";
            } else {
                echo "<br><strong>No approved items found. Try adding sample data:</strong><br>";
                echo "<a href='?add_sample=1' style='color:#00f0ff;'>Click here to add sample data</a><br>";
            }
        } catch (PDOException $e) {
            echo "<span class='error'>✗ Query failed: " . $e->getMessage() . "</span><br>";
        }
        echo "<br>";
        
        // Test 7: Check uploads folder
        echo "<h2>7. Uploads Folder</h2>";
        $uploadDir = __DIR__ . '/uploads/marketplace/';
        if (is_dir($uploadDir)) {
            echo "<span class='success'>✓ uploads/marketplace/ exists</span><br>";
            if (is_writable($uploadDir)) {
                echo "<span class='success'>✓ Folder is writable</span><br>";
            } else {
                echo "<span class='error'>✗ Folder is NOT writable (permissions issue)</span><br>";
            }
        } else {
            echo "<span class='error'>✗ uploads/marketplace/ does NOT exist</span><br>";
            echo "Creating folder...<br>";
            if (mkdir($uploadDir, 0777, true)) {
                echo "<span class='success'>✓ Folder created successfully</span><br>";
            } else {
                echo "<span class='error'>✗ Failed to create folder</span><br>";
            }
        }
        
        // Add sample data if requested
        if (isset($_GET['add_sample']) && $_GET['add_sample'] == '1') {
            echo "<h2>Adding Sample Data...</h2>";
            
            // Check if user with id=2 exists, if not create one
            $userCheck = $pdo->query("SELECT id FROM users WHERE id = 2");
            if ($userCheck->rowCount() == 0) {
                echo "Creating sample user...<br>";
                $pdo->exec("INSERT INTO users (id, first_name, last_name, email, password, role, is_verified, is_active, user_type) 
                           VALUES (2, 'John', 'Doe', 'john@example.com', 'dummy', 'user', 1, 1, 'full_access')");
            }
            
            // Add sample items
            $sampleItems = [
                ['Free Couch', 'furniture', 'Available in Sydney CBD. Used but comfy!', 0.00, 1, 'Sydney', 'NSW'],
                ['Mountain Bike', 'vehicles', 'Mountain bike in good condition. Pickup in Melbourne.', 120.00, 0, 'Melbourne', 'VIC'],
                ['Kitchen Set', 'miscellaneous', 'Pots, plates, and cups. Bundle offer.', 30.00, 0, 'Brisbane', 'QLD']
            ];
            
            foreach ($sampleItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO marketplace_items 
                    (user_id, title, category, description, price, is_free, city, state_code, status, created_at)
                    VALUES (2, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())
                ");
                $stmt->execute($item);
            }
            
            echo "<span class='success'>✓ Sample data added! <a href='?' style='color:#00f0ff;'>Refresh page</a></span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error: " . $e->getMessage() . "</span><br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<span class='error'>✗ config.php NOT found in " . __DIR__ . "</span><br>";
}

echo "<br><h2>8. Next Steps</h2>";
echo "<ol>";
echo "<li>If all checks pass, visit: <a href='marketplace.html' style='color:#00f0ff;'>marketplace.html</a></li>";
echo "<li>If errors appear above, fix them first</li>";
echo "<li>Check PHP error log for more details</li>";
echo "</ol>";
?>