<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Projects - ThreatRadar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<h1>ThreatRadar Ecosystem Projects</h1>
<p>ThreatRadar is actively expanding. Below is a list of our pending and ongoing projects designed to improve open-source threat intelligence.</p>

<div class="project-list" style="margin-top: 30px;">
    <div class="alert alert-info">
        <h3>ThreatRadar API v2 <span style="font-size: 0.7em; background-color: #0366d6; color: white; padding: 2px 6px; border-radius: 12px; margin-left: 10px; vertical-align: middle;">In Development</span></h3>
        <p style="margin-top: 10px;">A new RESTful API allowing developers to programmatically submit samples to our backend and retrieve cached analysis data in real-time.</p>
        <p style="margin-top: 10px;"><a href="#" style="font-weight: bold;">View Repository &raquo;</a></p>
    </div>

    <div class="alert alert-info">
        <h3>Malware Sandbox Integration <span style="font-size: 0.7em; background-color: #ffdce0; color: #86181d; padding: 2px 6px; border-radius: 12px; margin-left: 10px; vertical-align: middle;">Planning</span></h3>
        <p style="margin-top: 10px;">Integrating open-source Cuckoo Sandbox to provide dynamic behavioral analysis alongside our static VirusTotal scans.</p>
        <p style="margin-top: 10px;"><a href="#" style="font-weight: bold;">View Roadmap &raquo;</a></p>
    </div>

    <div class="alert alert-info">
        <h3>ThreatRadar Browser Extension <span style="font-size: 0.7em; background-color: #dbedff; color: #005cc5; padding: 2px 6px; border-radius: 12px; margin-left: 10px; vertical-align: middle;">Alpha</span></h3>
        <p style="margin-top: 10px;">A Chrome/Firefox extension that automatically checks downloaded files against the ThreatRadar database before they execute.</p>
        <p style="margin-top: 10px;"><a href="#" style="font-weight: bold;">Download Alpha Build &raquo;</a></p>
    </div>
</div>

<?php include 'footer.php'; ?>
