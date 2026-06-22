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
    <div class="css-scanner-container">
        <div class="css-scanner-box">
            <div class="css-scanner-line"></div>
            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        </div>
        <p>Awaiting File Analysis...</p>
    </div>
</div>

<div class="supported-by-section">
    <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.5rem;">Supported By</p>
    <div class="logo-grid small-logos">
        <img src="https://cdn.simpleicons.org/google/6b7280" alt="Google" title="Google">
        <img src="https://cdn.simpleicons.org/github/6b7280" alt="GitHub" title="GitHub">
        <img src="https://cdn.simpleicons.org/vercel/6b7280" alt="Vercel" title="Vercel">
        <img src="https://cdn.simpleicons.org/php/6b7280" alt="PHP" title="PHP">
        <img src="https://cdn.simpleicons.org/cloudflare/6b7280" alt="Cloudflare" title="Cloudflare">
    </div>
</div>

<?php include 'footer.php'; ?>
