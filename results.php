<?php
require_once 'config.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$error = '';
$analysis = null;
$status = 'queued';

if (empty($id)) {
    $error = "No Analysis ID provided.";
} elseif (empty(VIRUSTOTAL_API_KEY) || VIRUSTOTAL_API_KEY === 'YOUR_VIRUSTOTAL_API_KEY_HERE') {
    $error = "VirusTotal API Key is not configured.";
} else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.virustotal.com/api/v3/analyses/" . urlencode($id));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-apikey: ' . VIRUSTOTAL_API_KEY
    ));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $json = json_decode($response, true);
        if (isset($json['data']['attributes'])) {
            $analysis = $json['data']['attributes'];
            $status = $analysis['status']; // queued, in-progress, completed
        } else {
            $error = "Invalid API response structure.";
        }
    } else {
        $error = "VirusTotal API error (HTTP $http_code): " . htmlspecialchars($response);
    }
}

$is_finished = ($status === 'completed');
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
</head>
<body>

<div class="top-nav">
    <div class="nav-container">
        <div class="logo">
            <a href="index.php"><strong>ThreatRadar</strong></a>
        </div>
        <div class="nav-links">
            <a href="index.php">Upload</a>
            <a href="#">Documentation</a>
        </div>
    </div>
</div>

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
                    
                    // Determine overall threat level
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

                <h3 style="margin-top: 30px;">Scanner Details</h3>
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
            <?php endif; ?>
        <?php endif; ?>
    </div> <!-- End Content -->
</div> <!-- End Main Container -->

<?php include 'footer.php'; ?>
