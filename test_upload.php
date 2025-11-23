<?php
// Test script to check upload functionality

echo "<h1>Upload Test Results</h1>";

// Check GD extension
echo "<h2>GD Extension:</h2>";
if (extension_loaded('gd')) {
    echo "✅ GD extension is loaded<br>";
    $gd_info = gd_info();
    echo "GD Version: " . $gd_info['GD Version'] . "<br>";
} else {
    echo "❌ GD extension is NOT loaded<br>";
}

// Check upload directory
echo "<h2>Upload Directory:</h2>";
$uploadDir = 'uploads/profile_pictures/';

if (!is_dir($uploadDir)) {
    echo "Directory doesn't exist, attempting to create...<br>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Directory created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
} else {
    echo "✅ Directory exists<br>";
}

if (is_writable($uploadDir)) {
    echo "✅ Directory is writable<br>";
} else {
    echo "❌ Directory is NOT writable<br>";
}

// Test file creation
$testFile = $uploadDir . 'test.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✅ Can write files to directory<br>";
    unlink($testFile); // Clean up
} else {
    echo "❌ Cannot write files to directory<br>";
}

// Check PHP upload settings
echo "<h2>PHP Upload Settings:</h2>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'default') . "<br>";

// Check if tmp dir is writable
$tmpDir = ini_get('upload_tmp_dir');
if ($tmpDir && is_writable($tmpDir)) {
    echo "✅ Upload tmp dir is writable<br>";
} elseif ($tmpDir) {
    echo "❌ Upload tmp dir is NOT writable<br>";
} else {
    echo "ℹ️ Using default tmp dir<br>";
}

echo "<h2>Recent Error Logs:</h2>";
$logFile = 'error_log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -10); // Last 10 lines
    echo "<pre>" . implode("", $recentLines) . "</pre>";
} else {
    echo "No error log found<br>";
}
?>
