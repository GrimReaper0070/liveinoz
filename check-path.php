<?php
echo "<h1>Path Information</h1>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check if nav.html exists
$navPath = 'nav.html';
if (file_exists($navPath)) {
    echo "<p class='success'>nav.html exists</p>";
} else {
    echo "<p class='error'>nav.html does not exist</p>";
}

// Check if nav-loader.js exists
$loaderPath = 'nav-loader.js';
if (file_exists($loaderPath)) {
    echo "<p class='success'>nav-loader.js exists</p>";
} else {
    echo "<p class='error'>nav-loader.js does not exist</p>";
}

echo "<style>
.success { color: green; }
.error { color: red; }
</style>";
?>