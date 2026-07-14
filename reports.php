<?php include 'includes/header.php'; ?>
<div class="grid grid-2">
  <div class="card">
    <h3>Report Generator</h3>
    <div class="form-row">
      <div class="field"><label>Report Type</label><select class="select"><option>Camera Status Report</option><option>Incident Report</option><option>Motion Detection Report</option><option>Login Activity Report</option><option>Daily Monitoring Report</option><option>Monthly Report</option></select></div>
      <div class="field"><label>Period</label><input class="input" value="Last 7 Days"></div>
    </div>
    <div class="grid grid-2" style="margin-top: 12px;">
      <button class="button">Generate</button>
      <button class="button secondary">Export PDF</button>
      <button class="button secondary">Export Excel</button>
      <button class="button secondary">Print</button>
    </div>
  </div>
  <div class="card">
    <h3>Recent Reports</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Daily Monitoring Report</span><span class="badge success">Ready</span></div>
      <div class="timeline-item"><span>Motion Detection Report</span><span class="badge info">Shared</span></div>
      <div class="timeline-item"><span>Monthly Report</span><span class="badge warning">Pending</span></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>

