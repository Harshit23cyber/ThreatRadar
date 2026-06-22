<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Test Samples - ThreatRadar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="hero" style="padding: 2rem; border-bottom: none;">
    <h1>Safe Test Samples</h1>
    <p>Download industry-standard, safe test files to evaluate the ThreatRadar scanner and our VirusTotal integration.</p>
</div>

<div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
    <div class="alert alert-info">
        <strong>Security Notice:</strong> The files below are completely safe and contain no functional malware. The EICAR file is a standard string designed specifically to safely trigger an antivirus detection response for testing purposes.
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <h3 style="color: var(--danger-color);">EICAR Test File</h3>
            <p>A standard 68-byte text file developed by the European Institute for Computer Antivirus Research. It is completely benign but will safely trigger a "Malicious" detection on VirusTotal to verify that the scanner is working.</p>
            <a href="samples/eicar.com" download class="btn" style="margin-top: 1rem;">Download eicar.com</a>
        </div>
        
        <div class="feature-card">
            <h3 style="color: var(--success-color);">Benign Test Document</h3>
            <p>A standard, harmless text file containing simple readable text. Uploading this will verify that the scanner correctly identifies clean files as "Harmless" or "Undetected".</p>
            <a href="samples/benign_test.txt" download class="btn btn-outline" style="margin-top: 1rem;">Download benign_test.txt</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
