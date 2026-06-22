<?php
// router.php

// Serve static files as-is (e.g. style.css)
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|com|txt|md)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// Basic routing logic
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($path === '/' || $path === '/index.php' || $path === '/index') {
    require 'index.php';
} elseif ($path === '/results' || $path === '/results.php') {
    require 'results.php';
} elseif ($path === '/samples' || $path === '/samples.php') {
    require 'samples.php';
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist on ThreatRadar.</p>";
}
?>
