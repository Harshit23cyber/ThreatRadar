<?php
// Configuration for ThreatRadar

// VIRUSTOTAL API KEY
// Replace 'YOUR_VIRUSTOTAL_API_KEY_HERE' with your actual free API key.
define('VIRUSTOTAL_API_KEY', '1118bf2f4e7e47e8297ce08226461b82a05bc47c60d3dbe069851c92efb1718c');

// Define maximum upload size (e.g., 32MB as per VirusTotal free API standard endpoint, though VT supports larger with special endpoints)
define('MAX_UPLOAD_SIZE', 32 * 1024 * 1024);

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
