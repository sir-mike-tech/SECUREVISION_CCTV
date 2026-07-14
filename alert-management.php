<?php include 'includes/header.php'; ?>

<div class="grid grid-2">
  <div class="card">
    <h3>Alert Queue</h3>
    <p style="color:#cbd5e1; margin-bottom:12px;">Manage incoming alerts, acknowledge, resolve, or escalate them.</p>

    <div style="display:flex; gap:12px; margin-bottom:12px; align-items:center;">
      <div style="flex:1; display:flex; gap:8px;">
        <select id="filterType" class="input">
          <option value="">All Types</option>
          <option value="motion">Motion</option>
          <option value="offline">Offline</option>
          <option value="storage">Storage</option>
          <option value="auth">Authentication</option>
        </select>
        <select id="filterSeverity" class="input">
          <option value="">All Severities</option>
          <option value="critical">Critical</option>
          <option value="warning">Warning</option>
          <option value="info">Info</option>
        </select>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="button" onclick="applyAlertFilters()">Filter</button>
        <button class="button secondary" onclick="clearAlertFilters()">Clear</button>
      </div>
    </div>

    <div class="table-wrap">
      <table id="alertsTable" style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="width:36px;"><input type="checkbox" id="selectAllAlerts" onclick="toggleSelectAll(this)"></th>
            <th>Time</th>
            <th>Source</th>
            <th>Type</th>
            <th>Severity</th>
            <th>Details</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox" class="rowSelect"></td>
            <td>2026-07-12 12:10:22</td>
            <td>Gate A</td>
            <td>Motion</td>
            <td><span class="badge danger">Critical</span></td>
            <td>Multiple people detected</td>
            <td>
              <button class="button small" onclick="acknowledgeAlert(this)">Acknowledge</button>
            </td>
          </tr>
          <tr>
            <td><input type="checkbox" class="rowSelect"></td>
            <td>2026-07-12 11:55:10</td>
            <td>Reception</td>
            <td>Offline</td>
            <td><span class="badge warning">Watch</span></td>
            <td>Camera lost signal (intermittent)</td>
            <td>
              <button class="button small" onclick="escalateAlert(this)">Escalate</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3>Alert Actions</h3>
    <p style="color:#cbd5e1; margin-bottom:12px;">Bulk actions for selected alerts.</p>
    <div class="grid grid-2">
      <button class="button" onclick="bulkAcknowledge()">Acknowledge</button>
      <button class="button" onclick="bulkResolve()">Resolve</button>
      <button class="button secondary" onclick="bulkEscalate()">Escalate</button>
      <button class="button secondary" onclick="bulkDelete()">Delete</button>
    </div>

    <hr style="margin:16px 0; border-color: rgba(255,255,255,0.06);">

    <h4>Recent Activity</h4>
    <div style="font-size:0.9rem; color:#cbd5e1;">
      <div style="margin-bottom:8px;">12 active alerts</div>
      <div style="margin-bottom:8px;">4 critical, 6 warnings, 2 info</div>
      <div>Last alert: 2026-07-12 12:10:22</div>
    </div>
  </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:1000;">
  <div class="card" style="max-width:420px; width:90%;">
    <h3 id="confirmTitle">Confirm Action</h3>
    <p id="confirmText" style="color:#cbd5e1;">Are you sure?</p>
    <div style="display:flex; gap:8px; margin-top:12px;">
      <button class="button" id="confirmOk">Yes</button>
      <button class="button secondary" onclick="closeConfirm()">Cancel</button>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function applyAlertFilters() {
  // placeholder: implement server-side filtering
  alert('Filters applied (client-side placeholder)');
}
function clearAlertFilters() {
  document.getElementById('filterType').value = '';
  document.getElementById('filterSeverity').value = '';
}
function toggleSelectAll(box) {
  var rows = document.querySelectorAll('.rowSelect');
  for (var i=0;i<rows.length;i++) rows[i].checked = box.checked;
}
function acknowledgeAlert(btn) {
  var row = btn.closest('tr');
  row.style.opacity = 0.6;
  btn.textContent = 'Acknowledged';
  btn.disabled = true;
}
function escalateAlert(btn) {
  showConfirm('Escalate Alert', 'Escalate this alert to on-call staff?', function(){
    var row = btn.closest('tr');
    row.querySelector('td:nth-child(6)').innerHTML += ' (Escalated)';
    closeConfirm();
  });
}
function bulkAcknowledge() { document.querySelectorAll('.rowSelect:checked').forEach(function(c){ c.closest('tr').style.opacity=0.6; }); alert('Bulk acknowledged (placeholder)'); }
function bulkResolve() { alert('Bulk resolve (placeholder)'); }
function bulkEscalate() { alert('Bulk escalate (placeholder)'); }
function bulkDelete() { showConfirm('Delete Alerts', 'Delete selected alerts?', function(){ document.querySelectorAll('.rowSelect:checked').forEach(function(c){ c.closest('tr').remove(); }); closeConfirm(); }); }
function showConfirm(title, text, okCb) { document.getElementById('confirmTitle').textContent=title; document.getElementById('confirmText').textContent=text; document.getElementById('confirmOk').onclick = okCb; document.getElementById('confirmModal').style.display='flex'; }
function closeConfirm(){ document.getElementById('confirmModal').style.display='none'; }
</script>

