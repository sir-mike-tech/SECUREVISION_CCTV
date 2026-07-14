<?php include 'includes/header.php'; ?>
<div class="grid grid-2">
  <div class="card">
    <h3>System Settings</h3>
    <div class="form-row">
      <div class="field"><label>Company Name</label><input class="input" value="SecureVision Systems"></div>
      <div class="field"><label>Logo Upload</label><input class="input" placeholder="Upload logo"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Theme</label><select class="select"><option>Dark</option><option>Light</option></select></div>
      <div class="field"><label>Time Zone</label><input class="input" value="UTC+3"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Recording Duration</label><input class="input" value="24 Hours"></div>
      <div class="field"><label>Storage Settings</label><input class="input" value="NAS / Cloud"></div>
    </div>
    <div style="margin-top: 14px;"><button class="button">Save Settings</button></div>
  </div>
  <div class="card">
    <h3>Notification & Backup Preferences</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Email notifications</span><span class="badge success">Enabled</span></div>
      <div class="timeline-item"><span>SMS alerts</span><span class="badge warning">On demand</span></div>
      <div class="timeline-item"><span>Backup schedule</span><span class="badge info">Nightly</span></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>

