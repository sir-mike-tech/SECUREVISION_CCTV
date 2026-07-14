<?php include 'includes/header.php'; ?>

<div class="card">
  <h2>Camera Management</h2>
  <div class="muted">Manage cameras, test connectivity, export configuration, and capture snapshots.</div>

  <div class="cm-layout">
    <section class="cm-list card">
      <div class="list-actions">
        <input id="search" class="input" placeholder="Search by name, location, IP">
        <div>
          <button id="btn-add" class="button">Add Camera</button>
          <button id="btn-export" class="button secondary">Export JSON</button>
        </div>
      </div>

      <div class="table-wrap">
        <table id="cameras-table">
          <thead>
            <tr><th><input id="select-all" type="checkbox"></th><th>Name</th><th>Location</th><th>IP / URL</th><th>Model</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <!-- camera rows populated by JS -->
          </tbody>
        </table>
      </div>

      <div class="bulk-actions" style="margin-top:8px">
        <button id="btn-delete" class="button secondary">Delete Selected</button>
        <button id="btn-test-selected" class="button">Test Selected</button>
      </div>
    </section>

    <aside class="cm-side card">
      <h4>Details</h4>
      <div id="details" class="muted">Select a camera to view or edit details.</div>
    </aside>
  </div>
</div>

<!-- Modal: Add / Edit -->
<div id="modal" class="modal" style="display:none">
  <div class="modal-card">
    <h3 id="modal-title">Add Camera</h3>
    <div class="form-row">
      <div class="field"><label>Name</label><input id="f-name" class="input"></div>
      <div class="field"><label>Location</label><input id="f-location" class="input"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>IP / URL</label><input id="f-ip" class="input" placeholder="http://camera.local/stream or rtsp://..."></div>
      <div class="field"><label>Model</label><input id="f-model" class="input"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Serial</label><input id="f-serial" class="input"></div>
      <div class="field"><label>Group</label><input id="f-group" class="input"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Status</label><select id="f-status" class="select"><option>Enabled</option><option>Disabled</option></select></div>
      <div class="field"><label>Recording</label><input id="f-schedule" class="input" placeholder="24/7 or schedule"></div>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
      <button id="modal-cancel" class="button secondary">Cancel</button>
      <button id="modal-test" class="button">Test Connection</button>
      <button id="modal-save" class="button">Save Camera</button>
    </div>
  </div>
</div>

<style>
  /* Page-level style overrides */
</style>

<script>
  (function(){
    // Camera management with backend API persistence
    var cameras = [];
    var cameraMap = {}; // Map camera IDs to array index for quick lookup
    var currentTestingId = null;

    var tableBody = document.querySelector('#cameras-table tbody');
    var search = document.getElementById('search');
    var btnAdd = document.getElementById('btn-add');
    var btnExport = document.getElementById('btn-export');
    var btnDelete = document.getElementById('btn-delete');
    var btnTestSelected = document.getElementById('btn-test-selected');
    var selectAll = document.getElementById('select-all');
    var detailsPanel = document.getElementById('details');

    // Load cameras from backend
    function loadCameras() {
      setStatus('Loading cameras...');
      fetch('camera-api.php?action=list')
        .then(function(r) { 
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(data) {
          if (data && data.success) {
            cameras = data.data || [];
            cameras.forEach(function(cam, idx) { cameraMap[cam.id] = idx; });
            setStatus('');
            render();
          } else {
            setStatus('Error loading cameras: ' + (data?.error || 'Unknown error'));
          }
        })
        .catch(function(err) { 
          setStatus('Failed to load cameras: ' + err.message); 
          console.error('Load error:', err);
        });
    }

    function render(filter) {
      tableBody.innerHTML = '';
      var q = (filter || search.value || '').toLowerCase();
      cameras.forEach(function(cam) {
        if (q && (cam.name + cam.location + cam.ip + cam.model).toLowerCase().indexOf(q) === -1) return;
        var tr = document.createElement('tr');
        var statusClass = cam.testStatus === 'success' ? 'online' : (cam.testStatus === 'failed' ? 'offline' : 'unknown');
        var statusText = cam.testStatus === 'success' ? 'Online' : (cam.testStatus === 'failed' ? 'Offline' : 'Unknown');
        
        tr.innerHTML = '<td><input class="row-select" data-id="' + cam.id + '" type="checkbox"></td>' +
          '<td>' + escapeHtml(cam.name) + '</td><td>' + escapeHtml(cam.location) + '</td><td><code>' + escapeHtml(cam.ip) + '</code></td><td>' + escapeHtml(cam.model || '') + '</td>' +
          '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>' +
          '<td><button class="button small edit" data-id="' + cam.id + '">Edit</button> <button class="button small test" data-id="' + cam.id + '">Test</button> <button class="button small danger del" data-id="' + cam.id + '">Delete</button></td>';
        tableBody.appendChild(tr);
      });
      attachRowHandlers();
    }

    function attachRowHandlers() {
      document.querySelectorAll('.edit').forEach(function(b) {
        b.addEventListener('click', function() {
          var id = parseInt(b.getAttribute('data-id'), 10);
          openModal(id);
        });
      });
      document.querySelectorAll('.del').forEach(function(b) {
        b.addEventListener('click', function() {
          var id = parseInt(b.getAttribute('data-id'), 10);
          var cam = cameras.find(function(c) { return c.id === id; });
          if (confirm('Delete camera "' + cam.name + '"?')) {
            deleteCamera(id);
          }
        });
      });
      document.querySelectorAll('.test').forEach(function(b) {
        b.addEventListener('click', function() {
          var id = parseInt(b.getAttribute('data-id'), 10);
          var cam = cameras.find(function(c) { return c.id === id; });
          if (cam) testConnection(cam);
        });
      });
    }

    function escapeHtml(s) {
      return String(s || '').replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    // Modal logic
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modal-title');
    var fields = {
      name: document.getElementById('f-name'),
      location: document.getElementById('f-location'),
      ip: document.getElementById('f-ip'),
      model: document.getElementById('f-model'),
      serial: document.getElementById('f-serial'),
      group: document.getElementById('f-group'),
      status: document.getElementById('f-status'),
      schedule: document.getElementById('f-schedule')
    };
    var modalSave = document.getElementById('modal-save');
    var modalCancel = document.getElementById('modal-cancel');
    var modalTest = document.getElementById('modal-test');
    var editingCameraId = null;

    btnAdd.addEventListener('click', function() { openModal(null); });
    modalCancel.addEventListener('click', function() { modal.style.display = 'none'; });

    function openModal(id) {
      editingCameraId = id;
      modalTitle.textContent = id === null ? 'Add Camera' : 'Edit Camera';
      var c = id !== null ? cameras.find(function(cam) { return cam.id === id; }) : {
        name: '', location: '', ip: '', model: '', serial: '', group: '', status: 'Enabled', schedule: '24/7'
      };
      for (var k in fields) {
        if (fields[k]) fields[k].value = c[k] || '';
      }
      modal.style.display = 'flex';
    }

    modalSave.addEventListener('click', function() {
      var c = {
        name: fields.name.value.trim(),
        location: fields.location.value.trim(),
        ip: fields.ip.value.trim(),
        model: fields.model.value.trim(),
        serial: fields.serial.value.trim(),
        group: fields.group.value.trim(),
        status: fields.status.value,
        schedule: fields.schedule.value
      };
      if (!c.name || !c.ip) {
        alert('Name and IP/URL are required');
        return;
      }
      
      var method = editingCameraId === null ? 'add' : 'update';
      var payload = Object.assign({}, c);
      if (editingCameraId !== null) payload.id = editingCameraId;

      fetch('camera-api.php?action=' + method, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function(r) { 
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(data) {
          if (data && data.success) {
            modal.style.display = 'none';
            loadCameras();
          } else {
            alert('Error: ' + (data?.error || 'Unknown error'));
          }
        })
        .catch(function(err) { 
          console.error('Save error:', err);
          alert('Failed to save camera: ' + err.message); 
        });
    });

    modalTest.addEventListener('click', function() {
      var ip = fields.ip.value.trim();
      if (!ip) {
        alert('Please enter an IP/URL');
        return;
      }
      testConnection({ id: 'temp', ip: ip });
    });

    function testConnection(cam) {
      if (!cam || !cam.ip) {
        alert('No IP/URL provided');
        return;
      }
      currentTestingId = cam.id;
      setStatus('Testing ' + cam.name + '... (' + cam.ip + ')');
      
      fetch('test-camera.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ip: cam.ip })
      })
        .then(function(r) { 
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(result) {
          if (result && result.success) {
            setStatus('✓ ' + cam.name + ' is ' + (result.code === 'RTSP_OK' ? 'RTSP OK' : 'reachable') + '\n' + result.message + '\nResponse time: ' + result.elapsed + 'ms');
            alert('✓ Connection successful!\n' + result.message + '\nResponse time: ' + result.elapsed + 'ms');
            if (cam.id && cam.id !== 'temp') {
              updateCameraTestStatus(cam.id, 'success');
            }
          } else {
            setStatus('✗ ' + cam.name + ' connection failed\n' + (result?.error || 'Unknown error'));
            alert('✗ Connection failed:\n' + (result?.error || 'Unknown error'));
            if (cam.id && cam.id !== 'temp') {
              updateCameraTestStatus(cam.id, 'failed');
            }
          }
        })
        .catch(function(err) {
          console.error('Test error:', err);
          setStatus('✗ Test error: ' + err.message);
          alert('✗ Test error:\n' + err.message);
          if (cam.id && cam.id !== 'temp') {
            updateCameraTestStatus(cam.id, 'failed');
          }
        });
    }

    function updateCameraTestStatus(cameraId, status) {
      var cam = cameras.find(function(c) { return c.id === cameraId; });
      if (cam) {
        cam.testStatus = status;
        cam.lastTested = new Date().toISOString();
        render();
      }
    }

    function deleteCamera(id) {
      fetch('camera-api.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      })
        .then(function(r) { 
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(data) {
          if (data && data.success) {
            loadCameras();
          } else {
            alert('Error: ' + (data?.error || 'Unknown error'));
          }
        })
        .catch(function(err) { 
          console.error('Delete error:', err);
          alert('Failed to delete: ' + err.message); 
        });
    }

    function setStatus(text) {
      detailsPanel.textContent = text;
    }

    // Bulk operations
    btnDelete.addEventListener('click', function() {
      var selected = Array.from(document.querySelectorAll('.row-select:checked')).map(function(cb) {
        return parseInt(cb.getAttribute('data-id'), 10);
      });
      if (!selected.length) return alert('No cameras selected');
      if (!confirm('Delete ' + selected.length + ' camera(s)?')) return;
      
      fetch('camera-api.php?action=delete-bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: selected })
      })
        .then(function(r) { 
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(data) {
          if (data && data.success) {
            loadCameras();
          } else {
            alert('Error: ' + (data?.error || 'Unknown error'));
          }
        })
        .catch(function(err) { 
          console.error('Bulk delete error:', err);
          alert('Failed to delete: ' + err.message); 
        });
    });

    btnTestSelected.addEventListener('click', function() {
      var ids = Array.from(document.querySelectorAll('.row-select:checked')).map(function(cb) {
        return parseInt(cb.getAttribute('data-id'), 10);
      });
      if (!ids.length) return alert('No cameras selected');
      
      ids.forEach(function(id) {
        var cam = cameras.find(function(c) { return c.id === id; });
        if (cam) {
          setTimeout(function() { testConnection(cam); }, 500);
        }
      });
    });

    selectAll.addEventListener('change', function() {
      document.querySelectorAll('.row-select').forEach(function(cb) {
        cb.checked = selectAll.checked;
      });
    });

    search.addEventListener('input', function() { render(); });
    
    btnExport.addEventListener('click', function() {
      var a = document.createElement('a');
      a.href = 'data:application/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(cameras, null, 2));
      a.download = 'cameras-' + new Date().toISOString().split('T')[0] + '.json';
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

    // Initialize
    loadCameras();
  })();
</script>

<?php include 'includes/footer.php'; ?>

