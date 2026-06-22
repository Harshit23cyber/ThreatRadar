<?php
require_once 'config.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$error = '';
$analysis = null;
$file_details = null;
$status = 'queued';

if (empty($id)) {
    $error = "No Analysis ID provided.";
} elseif (empty(VIRUSTOTAL_API_KEY) || VIRUSTOTAL_API_KEY === 'YOUR_VIRUSTOTAL_API_KEY_HERE') {
    $error = "VirusTotal API Key is not configured.";
} else {
    // 1. Fetch Analysis
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.virustotal.com/api/v3/analyses/" . urlencode($id));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . VIRUSTOTAL_API_KEY));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $json = json_decode($response, true);
        if (isset($json['data']['attributes'])) {
            $analysis = $json['data']['attributes'];
            $status = $analysis['status']; 
            
            // 2. Fetch Deep File Details if completed
            if ($status === 'completed') {
                $sha256 = $json['meta']['file_info']['sha256'] ?? '';
                if ($sha256) {
                    $ch2 = curl_init();
                    curl_setopt($ch2, CURLOPT_URL, "https://www.virustotal.com/api/v3/files/" . urlencode($sha256));
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch2, CURLOPT_HTTPHEADER, array('x-apikey: ' . VIRUSTOTAL_API_KEY));
                    $res2 = curl_exec($ch2);
                    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                    curl_close($ch2);
                    if ($code2 == 200) {
                        $fjson = json_decode($res2, true);
                        $file_details = $fjson['data']['attributes'] ?? null;
                    }
                }
            }
        } else {
            $error = "Invalid API response structure.";
        }
    } else {
        $error = "VirusTotal API error (HTTP $http_code): " . htmlspecialchars($response);
    }
}

$is_finished = ($status === 'completed');

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow] . ' (' . $bytes . ' bytes)'; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Results - ThreatRadar</title>
    <link rel="stylesheet" href="style.css">
    <?php if (!$error && !$is_finished): ?>
    <!-- Auto-refresh every 10 seconds if not finished -->
    <meta http-equiv="refresh" content="10">
    <?php endif; ?>
    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).style.display = 'block';
            document.getElementById('btn-' + tabName).classList.add('active');
        }
    </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="sidebar">
        <h3>Actions</h3>
        <ul>
            <li><a href="index.php">&laquo; Back to Upload</a></li>
            <li><a href="results.php?id=<?php echo htmlspecialchars($id); ?>">Refresh Results</a></li>
        </ul>
    </div>
    
    <div class="content">
        <h1>Analysis Results</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php else: ?>
            <p><strong>Analysis ID:</strong> <code><?php echo htmlspecialchars($id); ?></code></p>
            <p><strong>Status:</strong> <?php echo ucfirst($status); ?></p>

            <?php if (!$is_finished): ?>
                <div class="alert alert-info" style="margin-top: 20px;">
                    <p>The file is currently being analyzed by VirusTotal. This page will automatically refresh every 10 seconds until the scan is complete.</p>
                </div>
            <?php else: ?>
                <?php 
                    $stats = $analysis['stats'];
                    $results = $analysis['results'];
                    $is_malicious = $stats['malicious'] > 0 || $stats['suspicious'] > 0;
                ?>
                
                <div class="alert <?php echo $is_malicious ? 'alert-error' : 'alert-info'; ?>" style="margin-top: 20px;">
                    <h2>Detections: <?php echo $stats['malicious']; ?> / <?php echo ($stats['malicious'] + $stats['suspicious'] + $stats['undetected'] + $stats['harmless']); ?></h2>
                    <p>
                        Malicious: <strong><?php echo $stats['malicious']; ?></strong> | 
                        Suspicious: <strong><?php echo $stats['suspicious']; ?></strong> | 
                        Undetected: <strong><?php echo $stats['undetected']; ?></strong> | 
                        Harmless: <strong><?php echo $stats['harmless']; ?></strong>
                    </p>
                </div>

                <div class="tabs-container" style="margin-top: 30px;">
                    <div class="tab-buttons">
                        <button id="btn-detection" class="tab-btn active" onclick="showTab('detection')">Detection</button>
                        <button id="btn-details" class="tab-btn" onclick="showTab('details')">Details</button>
                        <button id="btn-community" class="tab-btn" onclick="showTab('community')">Community</button>
                    </div>

                    <!-- DETECTION TAB -->
                    <div id="tab-detection" class="tab-content" style="display: block;">
                        <h3 style="margin-bottom: 15px;">Scanner Details</h3>
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Engine</th>
                                    <th>Category</th>
                                    <th>Result</th>
                                    <th>Version</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $engine => $details): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($engine); ?></strong></td>
                                        <?php
                                            $cat_class = 'status-timeout';
                                            if ($details['category'] === 'malicious') $cat_class = 'status-malicious';
                                            elseif ($details['category'] === 'suspicious') $cat_class = 'status-suspicious';
                                            elseif ($details['category'] === 'undetected') $cat_class = 'status-undetected';
                                            elseif ($details['category'] === 'harmless') $cat_class = 'status-harmless';
                                        ?>
                                        <td><span class="<?php echo $cat_class; ?>"><?php echo htmlspecialchars(ucfirst($details['category'])); ?></span></td>
                                        <td><?php echo $details['result'] ? '<code>'.htmlspecialchars($details['result']).'</code>' : '<em>None</em>'; ?></td>
                                        <td><span style="color:#586069; font-size: 0.9em;"><?php echo htmlspecialchars($details['engine_version'] ?? 'N/A'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- DETAILS TAB -->
                    <div id="tab-details" class="tab-content" style="display: none;">
                        <?php if ($file_details): ?>
                            <h3>Basic Properties</h3>
                            <div class="details-grid">
                                <div class="detail-label">MD5</div>
                                <div class="detail-value"><code><?php echo htmlspecialchars($file_details['md5'] ?? 'N/A'); ?></code></div>
                                
                                <div class="detail-label">SHA-1</div>
                                <div class="detail-value"><code><?php echo htmlspecialchars($file_details['sha1'] ?? 'N/A'); ?></code></div>
                                
                                <div class="detail-label">SHA-256</div>
                                <div class="detail-value"><code><?php echo htmlspecialchars($file_details['sha256'] ?? 'N/A'); ?></code></div>
                                
                                <div class="detail-label">SSDEEP</div>
                                <div class="detail-value"><code><?php echo htmlspecialchars($file_details['ssdeep'] ?? 'N/A'); ?></code></div>
                                
                                <div class="detail-label">TLSH</div>
                                <div class="detail-value"><code><?php echo htmlspecialchars($file_details['tlsh'] ?? 'N/A'); ?></code></div>
                                
                                <div class="detail-label">File type</div>
                                <div class="detail-value"><?php echo htmlspecialchars($file_details['type_description'] ?? 'N/A'); ?></div>
                                
                                <div class="detail-label">Magic</div>
                                <div class="detail-value"><?php echo htmlspecialchars($file_details['magic'] ?? 'N/A'); ?></div>
                                
                                <div class="detail-label">File size</div>
                                <div class="detail-value"><?php echo isset($file_details['size']) ? formatBytes($file_details['size']) : 'N/A'; ?></div>
                            </div>

                            <h3 style="margin-top: 30px;">History</h3>
                            <div class="details-grid">
                                <div class="detail-label">First Submission</div>
                                <div class="detail-value"><?php echo isset($file_details['first_submission_date']) ? gmdate("Y-m-d H:i:s", $file_details['first_submission_date']) . ' UTC' : 'N/A'; ?></div>
                                
                                <div class="detail-label">Last Submission</div>
                                <div class="detail-value"><?php echo isset($file_details['last_submission_date']) ? gmdate("Y-m-d H:i:s", $file_details['last_submission_date']) . ' UTC' : 'N/A'; ?></div>
                                
                                <div class="detail-label">Last Analysis</div>
                                <div class="detail-value"><?php echo isset($file_details['last_analysis_date']) ? gmdate("Y-m-d H:i:s", $file_details['last_analysis_date']) . ' UTC' : 'N/A'; ?></div>
                            </div>

                            <h3 style="margin-top: 30px;">Names</h3>
                            <ul style="margin-left: 20px; list-style-type: disc;">
                                <?php if (!empty($file_details['names'])): ?>
                                    <?php foreach (array_unique($file_details['names']) as $name): ?>
                                        <li><code><?php echo htmlspecialchars($name); ?></code></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>N/A</li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p>Deep file metadata is not available for this analysis.</p>
                        <?php endif; ?>
                    </div>

                    <!-- COMMUNITY TAB -->
                    <div id="tab-community" class="tab-content" style="display: none;">
                        <h3>Community Comments</h3>
                        <div class="alert alert-info">
                            <p>Community comments and crowd-sourced intelligence are available to registered users on the VirusTotal platform. Fetching deep comments requires an upgraded API quota.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div> <!-- End Content -->
</div> <!-- End Main Container -->

<?php include 'footer.php'; ?>
