<?php
$og = $og ?? [];

$og_title = $og['title'] ?? 'STN-Labz';
$og_desc  = $og['desc'] ?? 'Sovereign Agnostic Cyber Security';
$og_url   = $og['url'] ?? URLROOT;
$og_image = $og['image'] ?? theme::assetUrl('icons/icon.png');
$og_type = 'article' ?? '';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>
        STN-Labz
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name"description" content="STN-Labz (Sentinel Threat Network) develops distributed security intelligence that detects, classifies, and publishes digital threats in near real-time. The platform merges lightweight Sentinel agents, tamper-resistant storage, and an API-first delivery model.">
    <meta name="keywords" content="stn,sentinel,threat,network,security,intelligence,digital,sentinel,api,php,c,db">

    <meta name="author" content="STN-Labz">
    <meta name="copyright" content="STN-Labz">
    <meta name="application-name" content="STN-Labz">

    <!-- For Facebook -->
    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta property="og:type" content="<?= $og_type ?>">

    <!-- For Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta name="twitter:url" content="<?= htmlspecialchars($og_url) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta name="twitter:type" content="<?= $og_type ?>">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Shadow Witch CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars(theme::assetUrl('css/site.css'), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Site Icon -->
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars(theme::assetUrl('icons/icon.png'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="container">
<?php
include __DIR__ . '/nav.php';
?>
