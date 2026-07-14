<?php 
include 'includes/header.php';

// Sample activity log data - in production, this would come from database
$activities = array(
  array('time' => '2026-07-12 06:42:15', 'user' => 'James Carter', 'role' => 'Administrator', 'action' => 'User Login', 'details' => 'Successful login from admin console', 'status' => 'success', 'icon' => '🔓'),
  array('time' => '2026-07-12 07:11:32', 'user' => 'Rina Shah', 'role' => 'Security Officer', 'action' => 'Camera Added', 'details' => 'Lobby North added to perimeter group', 'status' => 'info', 'icon' => '📹'),
  array('time' => '2026-07-12 08:00:48', 'user' => 'Owen Price', 'role' => 'Viewer', 'action' => 'Recording Downloaded', 'details' => 'Playback clip exported locally (142 MB)', 'status' => 'success', 'icon' => '⬇️'),
  array('time' => '2026-07-12 08:45:22', 'user' => 'System', 'role' => 'System', 'action' => 'Motion Detected', 'details' => 'Movement detected in Parking Lot camera', 'status' => 'warning', 'icon' => '⚠️'),
  array('time' => '2026-07-12 09:15:10', 'user' => 'James Carter', 'role' => 'Administrator', 'action' => 'Alert Sent', 'details' => 'Email alert sent to security@company.com', 'status' => 'success', 'icon' => '✉️'),
  array('time' => '2026-07-12 10:22:45', 'user' => 'Rina Shah', 'role' => 'Security Officer', 'action' => 'Recording Started', 'details' => 'Manual recording initiated for Reception camera', 'status' => 'info', 'icon' => '⏺️'),
  array('time' => '2026-07-12 11:30:18', 'user' => 'Owen Price', 'role' => 'Viewer', 'action' => 'View Logs', 'details' => 'Accessed activity logs for last 24 hours', 'status' => 'info', 'icon' => '👁️'),
  array('time' => '2026-07-12 12:05:55', 'user' => 'System', 'role' => 'System', 'action' => 'AI Detection', 'details' => '3 people detected in Lobby North (confidence: 95%)', 'status' => 'info', 'icon' => '🤖'),
);

// Get filter parameters
$filterAction = isset($_GET['action']) ? $_GET['action'] : '';
$filterUser = isset($_GET['user']) ? $_GET['user'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Filter activities
$filtered = $activities;
if ($filterAction) {
    $filtered = array_filter($filtered, function($a) use ($filterAction) {
        return stripos($a['action'], $filterAction) !== false;
    });
}
if ($filterUser) {
    $filtered = array_filter($filtered, function($a) use ($filterUser) {
        return stripos($a['user'], $filterUser) !== false;
    });
}
if ($filterStatus) {
    $filtered = array_filter($filtered, function($a) use ($filterStatus) {
        return $a['status'] === $filterStatus;
    });
}
?>

<div class="grid grid-1">
  <div class="card">
    <h2>📊 Activity Logs & Audit Trail</h2>
    <div class="muted">Complete record of all system events, user actions, and security activities</div>
  </div>
</div>

<!-- Statistics Summary -->
<div class="grid grid-4">
  <div class="stat-card">
    <div style="font-size: 1.8rem; margin-bottom: 8px;">📝</div>
    <div style="color: #cbd5e1; font-size: 0.85rem;">Total Activities</div>
    <strong style="font-size: 1.4rem; color: #34d399;"><?php echo count($activities); ?></strong>
  </div>
  
  <div class="stat-card">
    <div style="font-size: 1.8rem; margin-bottom: 8px;">👥</div>
    <div style="color: #cbd5e1; font-size: 0.85rem;">Active Users</div>
    <strong style="font-size: 1.4rem; color: #60a5fa;">3</strong>
  </div>
  
  <div class="stat-card">
    <div style="font-size: 1.8rem; margin-bottom: 8px;">✓</div>
    <div style="color: #cbd5e1; font-size: 0.85rem;">Success Rate</div>
    <strong style="font-size: 1.4rem; color: #34d399;">98%</strong>
  </div>
  
  <div class="stat-card">
    <div style="font-size: 1.8rem; margin-bottom: 8px;">🔔</div>
    <div style="color: #cbd5e1; font-size: 0.85rem;">Alerts Today</div>
    <strong style="font-size: 1.4rem; color: #fbbf24;">12</strong>
  </div>
</div>

<!-- Filters Section -->
<div class="card">
  <h3>🔍 Filter & Search</h3>
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
    <div>
      <label style="font-size: 0.85rem; color: #cbd5e1; display: block; margin-bottom: 6px;">Action Type</label>
      <select id="filterAction" class="input" style="width: 100%;">
        <option value="">All Actions</option>
        <option value="Login">User Login</option>
        <option value="Camera">Camera Added</option>
        <option value="Recording">Recording</option>
        <option value="Motion">Motion Detected</option>
        <option value="Alert">Alert Sent</option>
        <option value="Detection">AI Detection</option>
      </select>
    </div>
    
    <div>
      <label style="font-size: 0.85rem; color: #cbd5e1; display: block; margin-bottom: 6px;">User</label>
      <select id="filterUser" class="input" style="width: 100%;">
        <option value="">All Users</option>
        <option value="James Carter">James Carter</option>
        <option value="Rina Shah">Rina Shah</option>
        <option value="Owen Price">Owen Price</option>
        <option value="System">System Events</option>
      </select>
    </div>
    
    <div>
      <label style="font-size: 0.85rem; color: #cbd5e1; display: block; margin-bottom: 6px;">Status</label>
      <select id="filterStatus" class="input" style="width: 100%;">
        <option value="">All Status</option>
        <option value="success">✓ Success</option>
        <option value="warning">⚠️ Warning</option>
        <option value="info">ℹ️ Info</option>
        <option value="error">✗ Error</option>
      </select>
    </div>
    
    <div>
      <label style="font-size: 0.85rem; color: #cbd5e1; display: block; margin-bottom: 6px;">Date</label>
      <input id="filterDate" type="date" class="input" style="width: 100%;" value="<?php echo $filterDate; ?>">
    </div>
  </div>
  
  <div style="display: flex; gap: 8px;">
    <button class="button" onclick="applyFilters()" style="flex: 1;">🔎 Apply Filters</button>
    <button class="button secondary" onclick="clearFilters()" style="flex: 1;">↺ Reset</button>
    <button class="button secondary" onclick="showReportGenerator()" style="flex: 1;">📊 Generate Report</button>
  </div>
</div>

<!-- Activity Table -->
<div class="card">
  <h3>📋 Activity Timeline</h3>
  <div class="table-wrap" style="max-height: 600px; overflow-y: auto;">
    <table>
      <thead>
        <tr>
          <th style="width: 5%;"></th>
          <th style="width: 15%;">Time</th>
          <th style="width: 12%;">User</th>
          <th style="width: 10%;">Role</th>
          <th style="width: 15%;">Action</th>
          <th style="width: 43%;">Details</th>
        </tr>
      </thead>
      <tbody id="activityTable">
        <?php foreach ($filtered as $activity): ?>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
          <td style="color: <?php 
            echo $activity['status'] === 'success' ? '#34d399' : 
                 ($activity['status'] === 'warning' ? '#fbbf24' : 
                  ($activity['status'] === 'error' ? '#ef4444' : '#60a5fa'));
          ?>; font-size: 1.1rem;">
            <?php echo $activity['icon']; ?>
          </td>
          <td>
            <div style="font-size: 0.85rem; font-family: monospace;">
              <?php echo htmlspecialchars($activity['time']); ?>
            </div>
          </td>
          <td>
            <strong><?php echo htmlspecialchars($activity['user']); ?></strong>
          </td>
          <td>
            <span style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">
              <?php echo htmlspecialchars($activity['role']); ?>
            </span>
          </td>
          <td>
            <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
          </td>
          <td>
            <small style="color: #cbd5e1;">
              <?php echo htmlspecialchars($activity['details']); ?>
            </small>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Statistics Chart -->
<div class="grid grid-2">
  <div class="card">
    <h3>📈 Activity by Type (Last 24h)</h3>
    <div style="height: 250px; display: flex; flex-direction: column; justify-content: flex-end; gap: 8px; padding: 20px 0;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="flex: 1; background: linear-gradient(90deg, #34d399 0%, #34d399 65%); height: 20px; border-radius: 4px;"></div>
        <span style="width: 80px; text-align: right;">User Actions (65%)</span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="flex: 1; background: linear-gradient(90deg, #60a5fa 0%, #60a5fa 25%); height: 20px; border-radius: 4px;"></div>
        <span style="width: 80px; text-align: right;">System Events (25%)</span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="flex: 1; background: linear-gradient(90deg, #fbbf24 0%, #fbbf24 7%); height: 20px; border-radius: 4px;"></div>
        <span style="width: 80px; text-align: right;">Warnings (7%)</span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="flex: 1; background: linear-gradient(90deg, #ef4444 0%, #ef4444 3%); height: 20px; border-radius: 4px;"></div>
        <span style="width: 80px; text-align: right;">Errors (3%)</span>
      </div>
    </div>
  </div>
  
  <div class="card">
    <h3>👥 User Activity Distribution</h3>
    <div style="padding: 20px 0;">
      <div style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
          <strong>James Carter</strong>
          <span style="color: #cbd5e1;">8 actions</span>
        </div>
        <div style="height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
          <div style="width: 60%; height: 100%; background: #34d399;"></div>
        </div>
      </div>
      
      <div style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
          <strong>Rina Shah</strong>
          <span style="color: #cbd5e1;">5 actions</span>
        </div>
        <div style="height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
          <div style="width: 37%; height: 100%; background: #60a5fa;"></div>
        </div>
      </div>
      
      <div style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
          <strong>Owen Price</strong>
          <span style="color: #cbd5e1;">3 actions</span>
        </div>
        <div style="height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
          <div style="width: 22%; height: 100%; background: #fbbf24;"></div>
        </div>
      </div>
      
      <div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
          <strong>System Events</strong>
          <span style="color: #cbd5e1;">2 actions</span>
        </div>
        <div style="height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
          <div style="width: 15%; height: 100%; background: #ef4444;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Report Generation Modal -->
<div id="reportModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
  <div class="card" style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3>📊 Generate Report</h3>
      <button onclick="closeReportModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #cbd5e1;">✕</button>
    </div>
    
    <form onsubmit="generateReport(event)" style="display: flex; flex-direction: column; gap: 16px;">
      <div>
        <label style="display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem;">Report Format</label>
        <select id="reportFormat" class="input" style="width: 100%;" required>
          <option value="csv">📄 CSV (Spreadsheet Compatible)</option>
          <option value="excel">📊 Excel (.XLS)</option>
          <option value="pdf">📋 PDF (Printable)</option>
        </select>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div>
          <label style="display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem;">From Date</label>
          <input id="reportDateFrom" type="date" class="input" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" required>
        </div>
        <div>
          <label style="display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem;">To Date</label>
          <input id="reportDateTo" type="date" class="input" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
      </div>
      
      <div>
        <label style="display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.9rem;">Filter by Action (Optional)</label>
        <select id="reportActionFilter" class="input" style="width: 100%;">
          <option value="">All Actions</option>
          <option value="Login">User Login</option>
          <option value="Camera">Camera Added</option>
          <option value="Recording">Recording</option>
          <option value="Motion">Motion Detected</option>
          <option value="Alert">Alert Sent</option>
          <option value="Detection">AI Detection</option>
        </select>
      </div>
      
      <div style="display: flex; gap: 8px;">
        <button type="submit" class="button" style="flex: 1;">📥 Generate & Download</button>
        <button type="button" class="button secondary" onclick="closeReportModal()" style="flex: 1;">Cancel</button>
      </div>
    </form>
  </div>
</div>

<style>
  .grid {
    display: grid;
    gap: 16px;
    margin-bottom: 16px;
  }
  
  .grid-1 {
    grid-template-columns: 1fr;
  }
  
  .grid-2 {
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  }
  
  .grid-4 {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }
  
  .stat-card {
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  
  .table-wrap {
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    overflow: hidden;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  thead tr {
    background: rgba(0,0,0,0.3);
    border-bottom: 2px solid rgba(255,255,255,0.1);
  }
  
  th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #cbd5e1;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  td {
    padding: 12px;
    color: #e2e8f0;
    font-size: 0.9rem;
  }
  
  tbody tr:hover {
    background: rgba(59, 130, 246, 0.05);
  }
  
  #reportModal {
    display: none !important;
  }
  
  #reportModal.active {
    display: flex !important;
  }
  
  #reportModal .card {
    animation: slideIn 0.3s ease-out;
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @media (max-width: 1200px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }
  }
  
  @media (max-width: 768px) {
    .grid-4 {
      grid-template-columns: repeat(2, 1fr);
    }
    
    table {
      font-size: 0.8rem;
    }
    
    th, td {
      padding: 8px;
    }
  }
</style>

<script>
  function applyFilters() {
    const action = document.getElementById('filterAction').value;
    const user = document.getElementById('filterUser').value;
    const status = document.getElementById('filterStatus').value;
    const date = document.getElementById('filterDate').value;
    
    const params = new URLSearchParams();
    if (action) params.append('action', action);
    if (user) params.append('user', user);
    if (status) params.append('status', status);
    if (date) params.append('date', date);
    
    window.location = '?' + params.toString();
  }
  
  function clearFilters() {
    window.location = '?';
  }
  
  function showReportGenerator() {
    document.getElementById('reportModal').classList.add('active');
  }
  
  function closeReportModal() {
    document.getElementById('reportModal').classList.remove('active');
  }
  
  function generateReport(event) {
    event.preventDefault();
    
    const format = document.getElementById('reportFormat').value;
    const dateFrom = document.getElementById('reportDateFrom').value;
    const dateTo = document.getElementById('reportDateTo').value;
    const actionFilter = document.getElementById('reportActionFilter').value;
    
    const btn = event.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = '⏳ Generating...';
    
    const formData = new FormData();
    formData.append('format', format);
    formData.append('date_from', dateFrom);
    formData.append('date_to', dateTo);
    formData.append('action_filter', actionFilter);
    
    fetch('generate-report.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) throw new Error('Report generation failed');
      
      // Determine filename and content type based on format
      let filename = 'activity-report-' + new Date().toISOString().slice(0, 10);
      let contentType = 'text/plain';
      
      if (format === 'pdf') {
        filename += '.html';
        contentType = 'text/html';
      } else if (format === 'excel') {
        filename += '.xls';
        contentType = 'application/vnd.ms-excel';
      } else {
        filename += '.csv';
        contentType = 'text/csv';
      }
      
      return response.blob().then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        
        closeReportModal();
        btn.disabled = false;
        btn.textContent = '📥 Generate & Download';
        alert('✓ Report generated and downloaded successfully!');
      });
    })
    .catch(error => {
      alert('✗ Error generating report: ' + error.message);
      btn.disabled = false;
      btn.textContent = '📥 Generate & Download';
    });
  }
  
  function exportLogs() {
    const action = document.getElementById('filterAction').value;
    const user = document.getElementById('filterUser').value;
    const status = document.getElementById('filterStatus').value;
    
    let csv = 'Time,User,Role,Action,Details\n';
    
    const rows = document.querySelectorAll('#activityTable tr');
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 6) {
        const time = cells[1].textContent.trim();
        const userName = cells[2].textContent.trim();
        const role = cells[3].textContent.trim();
        const actionText = cells[4].textContent.trim();
        const details = cells[5].textContent.trim();
        csv += `"${time}","${userName}","${role}","${actionText}","${details}"\n`;
      }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'activity-logs-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
  }
  
  // Close modal when clicking outside of it
  document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeReportModal();
    }
  });
  
  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeReportModal();
    }
  });
</script>

<?php include 'includes/footer.php'; ?>

