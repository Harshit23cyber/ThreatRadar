<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safe Test Samples - ThreatRadar Documentation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .docs-content {
            line-height: 1.6;
            color: var(--text-color);
        }
        .docs-content h1 { border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem; }
        .docs-content h2 { margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; }
        .docs-content p { margin-bottom: 1.5rem; }
        .docs-content .code-block { background: #f6f8fa; padding: 1rem; border-radius: 6px; font-family: monospace; overflow-x: auto; margin-bottom: 1.5rem; border: 1px solid var(--border-color); }
        .download-box { border: 1px solid var(--border-color); border-radius: 6px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; background: #ffffff; }
        .download-info h3 { margin: 0 0 0.5rem 0; }
        .download-info p { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="sidebar">
        <h3>Documentation</h3>
        <ul>
            <li><a href="https://github.com/Harshit23cyber/ThreatRadar#readme" target="_blank">Overview</a></li>
            <li><a href="/samples" style="font-weight: bold; color: var(--accent-color);">Test Samples</a></li>
            <li><a href="https://developers.virustotal.com/reference/overview" target="_blank">API Reference</a></li>
        </ul>
        <h3 style="margin-top: 2rem;">Page Contents</h3>
        <ul>
            <li><a href="#introduction">Introduction</a></li>
            <li><a href="#eicar">EICAR Test File</a></li>
            <li><a href="#benign">Benign Test Document</a></li>
        </ul>
    </div>
    
    <div class="content docs-content">
        <h1>Safe Test Samples</h1>
        
        <p id="introduction">Download industry-standard, safe test files to evaluate the ThreatRadar scanner and our VirusTotal integration.</p>
        
        <div class="alert alert-info" style="margin-bottom: 2rem;">
            <strong>Security Notice:</strong> The files below are completely safe and contain no functional malware. The EICAR file is a standard string designed specifically to safely trigger an antivirus detection response for testing purposes.
        </div>

        <h2 id="eicar">EICAR Test File</h2>
        <p>A standard 68-byte text file developed by the European Institute for Computer Antivirus Research. It is completely benign but will safely trigger a "Malicious" detection on VirusTotal to verify that the scanner is working.</p>
        
        <div class="download-box">
            <div class="download-info">
                <h3>eicar.com</h3>
                <p>Standard anti-virus test file</p>
            </div>
            <a href="samples/eicar.com" download class="btn">Download eicar.com</a>
        </div>

        <h2 id="benign">Benign Test Document</h2>
        <p>A standard, harmless text file containing simple readable text. Uploading this will verify that the scanner correctly identifies clean files as "Harmless" or "Undetected".</p>
        
        <div class="download-box">
            <div class="download-info">
                <h3>benign_test.txt</h3>
                <p>Basic readable text document</p>
            </div>
            <a href="samples/benign_test.txt" download class="btn btn-outline">Download benign_test.txt</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
