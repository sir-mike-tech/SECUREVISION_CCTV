<?php include 'includes/header.php'; ?>
<div class="grid grid-2">
  <div class="card">
    <h3>Profile Overview</h3>
    <div class="brand" style="margin-bottom: 12px;">
      <h2>James Carter</h2>
      <p>Administrator • SecureVision</p>
    </div>
    <div class="form-row">
      <div class="field"><label>Name</label><input class="input" value="James Carter"></div>
      <div class="field"><label>Email</label><input class="input" value="james@securevision.com"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Phone</label><input class="input" value="+1 555 0124"></div>
      <div class="field"><label>Change Password</label><input class="input" type="password" value="********"></div>
    </div>
    <div style="margin-top: 14px;"><button class="button">Update Profile</button></div>
  </div>
  <div class="card">
    <h3>Account Security</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Two-factor authentication</span><span class="badge success">Enabled</span></div>
      <div class="timeline-item"><span>Last password change</span><span class="muted">3 days ago</span></div>
      <div class="timeline-item"><span>Account role</span><span class="badge info">Administrator</span></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>

