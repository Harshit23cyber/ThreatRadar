<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['malware_file'])) {
    $file = $_FILES['malware_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed with error code: " . $file['error'];
    } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
        $error = "File is too large. Maximum size is " . (MAX_UPLOAD_SIZE / 1024 / 1024) . "MB.";
    } elseif (empty(VIRUSTOTAL_API_KEY) || VIRUSTOTAL_API_KEY === 'YOUR_VIRUSTOTAL_API_KEY_HERE') {
        $error = "VirusTotal API Key is not configured. Please update config.php.";
    } else {
        // Upload to VirusTotal
        $cfile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        $post_data = array('file' => $cfile);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/api/v3/files');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-apikey: ' . VIRUSTOTAL_API_KEY
        ));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $json = json_decode($response, true);
            if (isset($json['data']['id'])) {
                $analysis_id = $json['data']['id'];
                header("Location: results.php?id=" . urlencode($analysis_id));
                exit;
            } else {
                $error = "Failed to parse API response.";
            }
        } else {
            $error = "VirusTotal API error (HTTP $http_code): " . htmlspecialchars($response);
        }
    }
}

include 'header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin: 2rem auto; max-width: 800px;">
        <strong>Error:</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="hero">
    <h1>Threat Intelligence File Scanner</h1>
    <p>Upload suspicious files to scan them against over 70 antivirus engines and URL/domain blocklisting services via the VirusTotal API.</p>
    
    <div class="upload-card">
        <form action="index.php" method="post" enctype="multipart/form-data">
            <label for="malware_file">Select a file to securely scan</label>
            <input type="file" name="malware_file" id="malware_file" required>
            <button type="submit" class="btn">Analyze File</button>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">
                By uploading, you agree to the <a href="TERMS_OF_SERVICE.md">Terms of Service</a>. Max size: <?php echo MAX_UPLOAD_SIZE / 1024 / 1024; ?>MB.
            </p>
        </form>
    </div>
</div>

<div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
    <div class="features-grid">
        <div class="feature-card">
            <h3>Comprehensive Scanning</h3>
            <p>Utilizing the VirusTotal v3 API, your file is checked against dozens of top-tier antivirus engines simultaneously.</p>
        </div>
        <div class="feature-card">
            <h3>Immediate Feedback</h3>
            <p>As soon as you upload, an Analysis ID is returned and the system polls the results automatically until completion.</p>
        </div>
        <div class="feature-card">
            <h3>Community Safe</h3>
            <p>Uploaded files help the broader security community. Use our safe test samples to evaluate the system without risk.</p>
        </div>
    </div>
</div>

<div class="supported-by-section">
    <h2>Supported By</h2>
    <div class="logo-grid">
        <div class="logo-item"><img src="logos/cyberguard.png" alt="CyberGuard"></div>
        <div class="logo-item"><img src="logos/securenet.png" alt="SecureNet"></div>
        <div class="logo-item"><img src="logos/openthreat.png" alt="OpenThreat"></div>
    </div>
</div>

<?php include 'footer.php'; ?>
