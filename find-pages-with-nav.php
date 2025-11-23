<?php
// This script finds all HTML files that still contain the old navigation
$directory = '.';
$files = glob("*.html");
$pagesWithOldNav = [];

foreach ($files as $file) {
    // Skip the nav.html file itself
    if ($file === 'nav.html') continue;
    
    // Read the file content
    $content = file_get_contents($file);
    
    // Check if the file contains the old navigation pattern
    if (strpos($content, '<nav class="main-menu">') !== false) {
        $pagesWithOldNav[] = $file;
    }
}

echo "Pages that still contain the old navigation:\n";
echo "==========================================\n";
foreach ($pagesWithOldNav as $page) {
    echo "- " . $page . "\n";
}

echo "\nTotal pages to update: " . count($pagesWithOldNav) . "\n";

echo "\nTo update a page:\n";
echo "1. Replace <nav class=\"main-menu\">...</nav> with <div id=\"navbar-placeholder\"></div>\n";
echo "2. Add <script src=\"nav-loader.js\"></script> before the closing </body> tag\n";
?>