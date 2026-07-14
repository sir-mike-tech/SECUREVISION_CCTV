<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = array(
  array('title' => 'Dashboard', 'file' => 'dashboard.php'),
  array('title' => 'Live Monitoring', 'file' => 'live-monitoring.php'),
  array('title' => 'Camera Management', 'file' => 'camera-management.php'),
  array('title' => 'Playback', 'file' => 'playback.php'),
  array('title' => 'Motion Detection', 'file' => 'motion-detection.php'),
  array('title' => 'Alert Management', 'file' => 'alert-management.php'),
  array('title' => 'Alert History', 'file' => 'alert-history.php'),
  array('title' => 'Email Diagnostics', 'file' => 'email-diagnostics.php'),
  array('title' => 'User Management', 'file' => 'user-management.php'),
  array('title' => 'Reports', 'file' => 'reports.php'),
  array('title' => 'Settings', 'file' => 'settings.php'),
  array('title' => 'Activity Logs', 'file' => 'activity-logs.php'),
  array('title' => 'Profile', 'file' => 'profile.php')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureVision CCTV Monitoring System</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar">
    <div class="brand">
      <h2>SecureVision</h2>
      <p>Command Center</p>
    </div>
    <nav class="nav-links">
      <?php foreach ($navItems as $item): ?>
        <a class="<?php echo ($currentPage === $item['file'] ? 'active' : ''); ?>" href="<?php echo $item['file']; ?>"><?php echo $item['title']; ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main class="main-content">
    <header class="topbar">
      <h1>Smart CCTV Monitoring System</h1>
      <div class="topbar-actions">
        <span><?= date('l, F j, Y') ?></span>
        <a class="button small" href="profile.php">Profile</a>
      </div>
    </header>
    <div class="page-body">
