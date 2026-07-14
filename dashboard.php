<?php include 'includes/header.php'; ?>
<section class="grid grid-4">
  <div class="card metric">
    <div>
      <div class="label">Total Cameras</div>
      <div class="value">48</div>
    </div>
    <span class="badge info">Live</span>
  </div>
  <div class="card metric">
    <div>
      <div class="label">Online Cameras</div>
      <div class="value">41</div>
    </div>
    <span class="badge success">+3 today</span>
  </div>
  <div class="card metric">
    <div>
      <div class="label">Offline Cameras</div>
      <div class="value">7</div>
    </div>
    <span class="badge warning">Needs review</span>
  </div>
  <div class="card metric">
    <div>
      <div class="label">Active Alerts</div>
      <div class="value">12</div>
    </div>
    <span class="badge danger">High</span>
  </div>
</section>
<section class="card" style="margin-top: 18px;">
  <h3>Open Live Monitoring</h3>
  <div class="grid grid-2">
    <div>
      <p class="muted">Launch the camera-based monitoring console to review live feeds, switch layouts, and inspect active alerts.</p>
      <a class="button" href="live-monitoring.php">Open Live Monitoring</a>
    </div>
    <div class="camera-card">
      <div class="thumb"></div>
      <strong>Camera Feed Ready</strong>
      <div class="muted">4K • 24/7 • Click to open</div>
    </div>
  </div>
</section>
<section class="grid grid-2" style="margin-top: 18px;">
  <div class="card">
    <h3>Monitoring Snapshot</h3>
    <div class="grid grid-2">
      <div class="card" style="padding: 14px; background: rgba(255,255,255,0.02);">
        <div class="label">Motion Detection</div>
        <div class="value">Enabled</div>
      </div>
      <div class="card" style="padding: 14px; background: rgba(255,255,255,0.02);">
        <div class="label">Storage Usage</div>
        <div class="value">74%</div>
      </div>
    </div>
    <div class="footer-note">Today’s recordings: 18 clips • Last backup: 06:45</div>
  </div>
  <div class="card">
    <h3>Recent Activities</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Motion detected at East Gate</span><span class="muted">2 min ago</span></div>
      <div class="timeline-item"><span>Camera C-14 was re-enabled</span><span class="muted">12 min ago</span></div>
      <div class="timeline-item"><span>Report exported to PDF</span><span class="muted">37 min ago</span></div>
    </div>
  </div>
</section>
<section class="grid grid-3" style="margin-top: 18px;">
  <div class="card">
    <h3>Live Preview Cards</h3>
    <div class="camera-grid">
      <div class="camera-card">
        <div class="thumb"></div>
        <strong>Lobby North</strong>
        <div class="muted">Streaming • 24/7</div>
      </div>
      <div class="camera-card">
        <div class="thumb"></div>
        <strong>Parking Lot</strong>
        <div class="muted">Motion alert active</div>
      </div>
      <div class="camera-card">
        <div class="thumb"></div>
        <strong>Reception</strong>
        <div class="muted">Offline for 8 min</div>
      </div>
    </div>
  </div>
  <div class="card">
    <h3>Alert Queue</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Camera tampering</span><span class="badge danger">Urgent</span></div>
      <div class="timeline-item"><span>Network fluctuation</span><span class="badge warning">Watch</span></div>
      <div class="timeline-item"><span>Unauthorized login</span><span class="badge danger">Investigate</span></div>
    </div>
  </div>
  <div class="card">
    <h3>System Health</h3>
    <div class="timeline-list">
      <div class="timeline-item"><span>Storage cluster</span><span class="badge success">Healthy</span></div>
      <div class="timeline-item"><span>Video retention</span><span class="badge info">Optimized</span></div>
      <div class="timeline-item"><span>Backup job</span><span class="badge success">Completed</span></div>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>


