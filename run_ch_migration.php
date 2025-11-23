<?php
require 'config.php';

echo "<h1>ch System Database Migration</h1>";
echo "<p>Running comprehensive ch system migration...</p>";

try {
    $pdo = getDBConnection();

    // Read the migration SQL file
    $migrationSQL = file_get_contents('ch_system_migration.sql');

    if (!$migrationSQL) {
        throw new Exception("Could not read migration file");
    }

    // Split the SQL into individual statements, handling multi-line statements
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $migrationSQL);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }

        $currentStatement .= $line . ' ';

        // Check if statement ends with semicolon
        if (substr($line, -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }

    // Add any remaining statement
    if (!empty($currentStatement)) {
        $statements[] = trim($currentStatement);
    }

    $successCount = 0;
    $errorCount = 0;

    echo "<h2>Executing Migration Statements:</h2>";
    echo "<ul>";

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }

        try {
            $pdo->exec($statement);
            echo "<li style='color: green;'>✓ " . substr($statement, 0, 50) . "...</li>";
            $successCount++;
        } catch (Exception $e) {
            // Check if it's an acceptable error (like table already exists)
            if (strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<li style='color: orange;'>⚠ " . substr($statement, 0, 50) . "... (Already exists)</li>";
                $successCount++;
            } else {
                echo "<li style='color: red;'>✗ " . substr($statement, 0, 50) . "... Error: " . $e->getMessage() . "</li>";
                $errorCount++;
            }
        }
    }

    echo "</ul>";

    echo "<h2>Migration Results:</h2>";
    echo "<p>Successful statements: <strong>$successCount</strong></p>";
    echo "<p>Failed statements: <strong>$errorCount</strong></p>";

    if ($errorCount === 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Migration completed successfully!</p>";

        // Verify the new tables were created
        echo "<h3>Verifying New Tables:</h3>";
        $tables = ['states', 'cities', 'ch_rooms', 'dm_threads', 'dm_requests', 'dm_messages', 'file_attachments', 'reports', 'user_blocks', 'bad_words', 'user_preferences'];

        echo "<ul>";
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<li>$table: {$result['count']} records</li>";
            } catch (Exception $e) {
                echo "<li style='color: red;'>$table: Error - " . $e->getMessage() . "</li>";
            }
        }
        echo "</ul>";

    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Migration completed with errors. Please check the output above.</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Migration Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

