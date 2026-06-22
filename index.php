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

<h1>Threat Intelligence File Scanner</h1>
<p>Upload a suspicious file below to scan it against over 70 antivirus scanners and URL/domain blocklisting services via the VirusTotal API.</p>

<?php if ($error): ?>
    <div class="alert alert-error">
        <strong>Error:</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="upload-form">
    <form action="index.php" method="post" enctype="multipart/form-data">
        <label for="malware_file">Select file to scan:</label>
        <input type="file" name="malware_file" id="malware_file" required>
        <p style="font-size: 0.9em; color: #586069; margin-bottom: 15px;">By uploading, you agree to the Terms of Service. Max size: <?php echo MAX_UPLOAD_SIZE / 1024 / 1024; ?>MB.</p>
        <button type="submit" class="btn">Scan File</button>
    </form>
</div>

<h2 style="margin-top: 40px;">How it works</h2>
<p>ThreatRadar utilizes the comprehensive <a href="https://developers.virustotal.com/reference/overview">VirusTotal v3 API</a>. When you upload a file:</p>
<ol style="margin-left: 20px; margin-top: 10px;">
    <li>The file is transmitted securely to VirusTotal.</li>
    <li>An Analysis ID is returned immediately.</li>
    <li>We poll the API until the scan finishes.</li>
    <li>Results are presented in an easy-to-read format, displaying malicious, suspicious, and harmless verdicts.</li>
</ol>

<?php include 'footer.php'; ?>
