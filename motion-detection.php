<?php include 'includes/header.php'; ?>

<div class="card">
  <h2>🎥 Motion Detection & Recording</h2>
  <div class="muted">
    Real-time AI surveillance module with intelligent motion detection, automatic recording, object recognition with bounding boxes, confidence scores, and comprehensive event logging for professional security monitoring.
  </div>

  <div class="md-layout">
    <section class="md-viewer card">
      <div class="viewer-top">
        <div class="meta">
          <div><strong id="md-camera">📹 Camera: Lobby North</strong></div>
          <div class="muted" id="md-status">● Offline</div>
          <div style="margin-top:8px;font-size:0.9rem;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div><span style="color:#fbbf24;">Motion:</span> <strong id="motion-score">0</strong></div>
            <div><span style="color:#60a5fa;">FPS:</span> <strong id="fps-counter">0</strong></div>
            <div><span style="color:#34d399;">Snapshots:</span> <strong id="snapshot-count">0</strong></div>
            <div><span style="color:#f87171;">Recording:</span> <strong id="recording-status">Off</strong></div>
          </div>
        </div>
        <div class="viewer-actions">
          <select id="camera-select" class="select" style="padding:8px 12px;border-radius:6px;border:1px solid #475569;">
            <option>Lobby North</option>
            <option>Parking Lot</option>
            <option>Reception</option>
            <option>Back Door</option>
          </select>
          <button id="btn-start" class="button">▶ Start</button>
          <button id="btn-stop" class="button secondary" style="display:none;">⏹ Stop</button>
          <button id="btn-capture" class="button secondary" style="display:none;">📸 Snapshot</button>
          <button id="btn-record" class="button secondary" style="display:none;background:#dc2626;">⏺ Record</button>
        </div>
      </div>

      <div class="video-wrap">
        <video id="md-video" autoplay playsinline muted style="width:100%;height:420px;object-fit:cover;background:#000;border-radius:8px;"></video>
        <canvas id="md-canvas" style="position:absolute;left:0;top:0;pointer-events:none;display:none;"></canvas>
        <div id="md-overlay" class="video-overlay" style="display:none;background:rgba(0,0,0,0.8);border-radius:8px;">No camera connected</div>
        <div id="motion-indicator" style="position:absolute;top:10px;right:10px;width:20px;height:20px;border-radius:50%;background:#888;border:2px solid #fff;display:none;"></div>
      </div>

      <div class="settings-panel">
        <div class="settings-section">
          <h5 style="margin:0 0 10px;font-size:0.9rem;color:#cbd5e1;text-transform:uppercase;letter-spacing:0.5px;">Detection Settings</h5>
          <div class="settings-row">
            <div class="setting-group">
              <label>🎯 Sensitivity</label>
              <div style="display:flex;align-items:center;gap:8px;">
                <input id="sensitivity" type="range" min="10" max="100" value="50" style="flex:1;">
                <span id="sens-value" style="min-width:35px;font-weight:600;">50</span>
              </div>
            </div>

            <div class="setting-group">
              <label>🤖 Detection Mode</label>
              <select id="mode" class="select" style="flex:1;padding:6px;font-size:0.9rem;">
                <option value="motion">Motion Detection</option>
                <option value="ai">AI Object Detection</option>
                <option value="hybrid">Hybrid Mode</option>
              </select>
            </div>

            <div class="setting-group">
              <label>📊 AI Confidence</label>
              <div style="display:flex;align-items:center;gap:8px;">
                <input id="ai-confidence" type="range" min="10" max="100" value="60" style="flex:1;">
                <span id="conf-value" style="min-width:45px;font-weight:600;">60%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="settings-section" style="margin-top:12px;border-top:1px solid rgba(255,255,255,0.1);padding-top:12px;">
          <h5 style="margin:0 0 10px;font-size:0.9rem;color:#cbd5e1;text-transform:uppercase;letter-spacing:0.5px;">Recording Settings</h5>
          <div class="settings-row">
            <div class="setting-group">
              <label>⏱️ Auto-Stop Duration (sec)</label>
              <input id="auto-stop-delay" type="number" min="5" max="300" value="10" class="input" style="padding:6px;font-size:0.9rem;">
            </div>

            <div class="setting-group">
              <label>💾 Auto-Capture Motion</label>
              <input id="auto-capture" type="checkbox" style="width:18px;height:18px;cursor:pointer;margin-top:2px;">
            </div>

            <div class="setting-group">
              <label>🔔 Auto-Record on Motion</label>
              <input id="auto-record" type="checkbox" style="width:18px;height:18px;cursor:pointer;margin-top:2px;" checked>
            </div>

            <div class="setting-group">
              <label>🔊 Enable Alerts</label>
              <input id="enable-alerts" type="checkbox" style="width:18px;height:18px;cursor:pointer;margin-top:2px;" checked>
            </div>
          </div>
        </div>

        <div class="settings-row" style="margin-top:12px;gap:8px;">
          <button id="load-model" class="button small" style="flex:1;padding:8px;">Load AI Model</button>
          <button id="export-stats" class="button secondary small" style="flex:1;padding:8px;">Export Report</button>
          <button id="settings-save" class="button secondary small" style="flex:1;padding:8px;">Save Settings</button>
        </div>
      </div>

      <div id="snapshots-gallery" class="snapshots-gallery">
        <h4 style="margin:0 0 10px;display:flex;align-items:center;gap:8px;"><span>📸</span> Recent Snapshots</h4>
        <div id="snapshots" class="snapshots" aria-live="polite">
          <div class="empty-state">No snapshots captured yet</div>
        </div>
      </div>
    </section>

    <aside class="md-sidebar card">
      <h4 style="display:flex;align-items:center;gap:8px;margin:0 0 12px;"><span>📋</span> Event History</h4>
      <div class="log-actions">
        <button id="clear-log" class="button secondary small" style="flex:1;">Clear</button>
        <button id="export-log" class="button small" style="flex:1;">Export CSV</button>
      </div>
      <div id="event-log" class="event-log">
        <div class="empty-state">No events detected</div>
      </div>

      <h4 style="margin-top:16px;display:flex;align-items:center;gap:8px;"><span>🎬</span> Recordings</h4>
      <div class="log-actions">
        <button id="refresh-recordings" class="button secondary small">Refresh</button>
      </div>
      <div id="recordings-list" class="event-log" style="max-height:300px;overflow:auto;">
        <div class="empty-state">No recordings</div>
      </div>

      <h4 style="margin-top:16px;display:flex;align-items:center;gap:8px;"><span>🔍</span> Quick Filters</h4>
      <div class="form-row" style="display:flex;flex-direction:column;gap:8px;">
        <div class="field">
          <label style="font-size:0.85rem;color:#cbd5e1;">Filter by Type</label>
          <select id="filter-type" class="select" style="width:100%;padding:6px;font-size:0.9rem;">
            <option value="">All Events</option>
            <option value="motion">Motion Only</option>
            <option value="person">Person Detected</option>
            <option value="alert">Alerts Only</option>
          </select>
        </div>
        <div class="field">
          <label style="font-size:0.85rem;color:#cbd5e1;">Date Range</label>
          <input id="filter-date" type="date" class="input" style="width:100%;padding:6px;font-size:0.9rem;" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
    </aside>
  </div>
</div>

<style>
  .md-layout {
    display: flex;
    gap: 16px;
    align-items: flex-start;
  }
  .md-viewer {
    flex: 2;
    position: relative;
  }
  .md-sidebar {
    flex: 1;
    min-width: 280px;
    max-height: 90vh;
    overflow-y: auto;
  }
  .viewer-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
  }
  .meta {
    flex: 1;
  }
  .meta strong {
    font-size: 1.05rem;
  }
  .viewer-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .video-wrap {
    position: relative;
    margin-bottom: 12px;
    border-radius: 8px;
    overflow: hidden;
  }
  #md-canvas {
    width: 100%;
    height: 420px;
  }
  .video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    z-index: 10;
  }
  #motion-indicator {
    animation: pulse 0.6s infinite;
  }
  @keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 85, 0.7); }
    50% { box-shadow: 0 0 0 8px rgba(34, 197, 85, 0); }
  }
  .settings-panel {
    background: rgba(0, 0, 0, 0.2);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 12px;
  }
  .settings-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 10px;
  }
  .settings-row:last-child {
    margin-bottom: 0;
  }
  .settings-section {
    margin-bottom: 0;
  }
  .setting-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .setting-group label {
    font-size: 0.85rem;
    color: #cbd5e1;
    font-weight: 600;
  }
  .setting-group input[type="checkbox"] {
    cursor: pointer;
  }
  .snapshots-gallery {
    margin-top: 12px;
  }
  .snapshots-gallery h4 {
    margin: 0 0 8px;
  }
  .snapshots {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 8px;
    padding: 8px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    min-height: 120px;
  }
  .snapshots img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
    transition: transform 150ms ease, border-color 150ms ease;
  }
  .snapshots img:hover {
    transform: scale(1.05);
    border-color: #3b82f6;
  }
  .snapshots .empty-state {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    color: #8ea0bd;
  }
  .event-log {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 8px;
    background: rgba(0, 0, 0, 0.1);
  }
  .event-item {
    padding: 10px;
    margin-bottom: 8px;
    border-left: 3px solid #3b82f6;
    border-radius: 4px;
    background: rgba(59, 130, 246, 0.08);
    font-size: 0.9rem;
  }
  .event-item.person {
    border-left-color: #86efac;
    background: rgba(34, 197, 85, 0.08);
  }
  .event-time {
    color: #8ea0bd;
    font-size: 0.8rem;
    margin-top: 4px;
  }
  .log-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
  }
  .log-actions .button {
    flex: 1;
  }
  .gallery-thumb {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    margin-bottom: 6px;
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    cursor: pointer;
    transition: background 150ms ease;
  }
  .gallery-thumb:hover {
    background: rgba(59, 130, 246, 0.15);
  }
  .gallery-thumb img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
  }
  .gallery-info {
    flex: 1;
    min-width: 0;
  }
  .gallery-info small {
    display: block;
    color: #8ea0bd;
    font-size: 0.75rem;
    margin-top: 2px;
  }
  @media (max-width: 1100px) {
    .settings-row {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 900px) {
    .md-layout {
      flex-direction: column;
    }
    .md-viewer {
      order: 1;
    }
    .md-sidebar {
      order: 2;
      max-height: none;
    }
    .settings-row {
      grid-template-columns: 1fr;
    }
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.21.0/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
<script>
  (function(){
    // DOM Elements
    var video = document.getElementById('md-video');
    var canvas = document.getElementById('md-canvas');
    var ctx = canvas.getContext('2d');
    var startBtn = document.getElementById('btn-start');
    var stopBtn = document.getElementById('btn-stop');
    var captureBtn = document.getElementById('btn-capture');
    var recordBtn = document.getElementById('btn-record');
    var sens = document.getElementById('sensitivity');
    var sensVal = document.getElementById('sens-value');
    var eventLog = document.getElementById('event-log');
    var recordingsList = document.getElementById('recordings-list');
    var clearLog = document.getElementById('clear-log');
    var exportLog = document.getElementById('export-log');
    var modeSel = document.getElementById('mode');
    var loadModelBtn = document.getElementById('load-model');
    var autoStopDelay = document.getElementById('auto-stop-delay');
    var autoRecordCheckbox = document.getElementById('auto-record');
    var autoCapture = document.getElementById('auto-capture');
    var enableAlerts = document.getElementById('enable-alerts');
    var recordingStatus = document.getElementById('recording-status');
    var mdStatus = document.getElementById('md-status');
    var cameraSelect = document.getElementById('camera-select');
    var model = null;

    // State Variables
    var running = false;
    var recording = false;
    var prevFrame = null;
    var loopId = null;
    var events = [];
    var recordings = [];
    var recordingStartTime = null;
    var lastMotionTime = null;
    var fpsCounter = 0;
    var fpsClock = null;
    
    // MediaRecorder variables
    var mediaRecorder = null;
    var recordedChunks = [];
    var recordingSessionId = null;
    var chunkCounter = 0;

    // Initialize
    function init(){
      sens.addEventListener('input', function(){ sensVal.textContent = sens.value; });
      clearLog.addEventListener('click', clearEventLog);
      exportLog.addEventListener('click', exportEventsCSV);
      loadModelBtn.addEventListener('click', loadAIModel);
      startBtn.addEventListener('click', handleStartMonitoring);
      stopBtn.addEventListener('click', handleStopMonitoring);
      captureBtn.addEventListener('click', captureSnapshot);
      recordBtn.addEventListener('click', toggleRecording);
      document.getElementById('settings-save').addEventListener('click', saveSettings);
      document.getElementById('refresh-recordings').addEventListener('click', refreshRecordings);
      document.getElementById('filter-type').addEventListener('change', filterEvents);
    }

    function setStatus(text, color){ 
      mdStatus.textContent = '● ' + text;
      mdStatus.style.color = color || '#94a3b8';
    }

    function addEvent(type, score, imgData, confidence){
      var time = new Date();
      var timeStr = time.toLocaleTimeString();
      var el = document.createElement('div');
      el.className = 'event-item';
      if(type.includes('Person')) el.classList.add('person');
      
      var content = '<strong>' + type + '</strong><div class="muted" style="font-size:0.8rem;margin-top:2px;">' + timeStr;
      if(confidence) content += ' (' + Math.round(confidence*100) + '%)';
      content += '</div>';
      
      if(imgData){
        content += '<div style="margin-top:6px;"><img src="' + imgData + '" style="max-width:100%;max-height:80px;border-radius:4px;border:1px solid rgba(255,255,255,0.1);"></div>';
      }
      
      el.innerHTML = content;
      eventLog.insertBefore(el, eventLog.firstChild);
      events.push({type:type, time:time.toISOString(), score:score||0, confidence:confidence||0});
      
      // Play alert sound if enabled
      if(enableAlerts.checked) playAlert();
    }

    function playAlert(){
      var audio = new Audio('data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==');
      audio.play().catch(function(e){console.log('Audio not available');});
    }

    function clearEventLog(){ 
      eventLog.innerHTML = '<div class="empty-state">No events detected</div>';
      events = []; 
    }

    function filterEvents(){
      var type = document.getElementById('filter-type').value;
      var items = eventLog.querySelectorAll('.event-item');
      items.forEach(function(item){
        if(!type || item.textContent.includes(type)){
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    }

    function exportEventsCSV(){
      if(!events.length) return alert('No events to export');
      var csv = 'Time,Type,Score,Confidence\n' + events.map(function(e){ 
        return [e.time, e.type, e.score, Math.round(e.confidence*100)+'%'].join(','); 
      }).join('\n');
      var a = document.createElement('a');
      a.href = 'data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
      a.download = 'motion-events-' + new Date().toISOString().slice(0,10) + '.csv';
      document.body.appendChild(a);
      a.click();
      a.remove();
    }

    function loadAIModel(){
      if(model) return alert('Model already loaded');
      setStatus('Loading AI model...', '#fbbf24');
      loadModelBtn.disabled = true;
      cocoSsd.load().then(function(m){ 
        model = m; 
        setStatus('AI model ready', '#34d399');
        loadModelBtn.textContent = '✓ AI Model Loaded';
      }).catch(function(e){ 
        setStatus('Model load failed', '#f87171'); 
        console.error(e); 
        loadModelBtn.disabled = false;
      });
    }

    function captureSnapshot(){
      if(!video.srcObject || !running) return;
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      var imgData = canvas.toDataURL('image/png');
      
      var img = new Image();
      img.src = imgData;
      img.style.cursor = 'pointer';
      img.title = 'Click to download';
      img.onclick = function(){ 
        var a = document.createElement('a');
        a.href = imgData;
        a.download = 'snapshot-' + Date.now() + '.png';
        a.click();
      };
      document.getElementById('snapshots').appendChild(img);
      document.getElementById('snapshot-count').textContent = parseInt(document.getElementById('snapshot-count').textContent) + 1;
    }

    function toggleRecording(){
      if(!recording && !running) return alert('Start monitoring first');
      recording = !recording;
      if(recording){
        recordingStartTime = Date.now();
        recordingSessionId = 'rec_' + Date.now();
        recordedChunks = [];
        chunkCounter = 0;
        recordBtn.textContent = '⏹ Stop Recording';
        recordBtn.style.background = '#ef4444';
        recordingStatus.textContent = 'Recording';
        recordingStatus.parentElement.style.color = '#f87171';
        addEvent('🎬 Recording Started', 0, null);
        startBackendRecording();
      } else {
        recordBtn.textContent = '⏺ Record';
        recordBtn.style.background = '#dc2626';
        recordingStatus.textContent = 'Off';
        recordingStatus.parentElement.style.color = '#9ca3af';
        var duration = ((Date.now() - recordingStartTime) / 1000).toFixed(1);
        addEvent('⏹ Recording Stopped (' + duration + 's)', 0, null);
        finalizeRecording(duration);
        recordings.push({start: recordingStartTime, duration: duration, size: Math.random() * 50, sessionId: recordingSessionId});
        updateRecordingsList();
      }
    }

    function startBackendRecording(){
      var camera = cameraSelect.value || 'Lobby North';
      var formData = new FormData();
      formData.append('action', 'start');
      formData.append('camera', camera);
      
      fetch('recording-api.php', {method: 'POST', body: formData})
        .then(function(r){return r.json();})
        .then(function(d){
          if(d.success){
            recordingSessionId = d.session_id;
            console.log('Backend recording started:', recordingSessionId);
          }
        })
        .catch(function(e){console.error('Recording API error:', e);});
    }

    function saveRecordingChunk(){
      if(!recording || !recordingSessionId) return;
      
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      
      canvas.toBlob(function(blob){
        var formData = new FormData();
        formData.append('action', 'save');
        formData.append('session_id', recordingSessionId);
        formData.append('camera', cameraSelect.value || 'Lobby North');
        formData.append('chunk', chunkCounter);
        formData.append('blob', blob);
        
        fetch('recording-api.php', {method: 'POST', body: formData})
          .then(function(r){return r.json();})
          .then(function(d){
            if(d.success) chunkCounter++;
          })
          .catch(function(e){console.error('Chunk save error:', e);});
      }, 'image/webp');
    }

    function finalizeRecording(duration){
      if(!recordingSessionId) return;
      
      var formData = new FormData();
      formData.append('action', 'finalize');
      formData.append('session_id', recordingSessionId);
      formData.append('camera', cameraSelect.value || 'Lobby North');
      formData.append('duration', duration);
      
      fetch('recording-api.php', {method: 'POST', body: formData})
        .then(function(r){return r.json();})
        .then(function(d){
          if(d.success){
            console.log('Recording finalized:', d.filename);
            addEvent('✅ Recording saved: ' + d.filename, 0, null);
          }
        })
        .catch(function(e){console.error('Finalize error:', e);});
    }

    function updateRecordingsList(){
      if(!recordings.length){
        recordingsList.innerHTML = '<div class="empty-state">No recordings</div>';
        return;
      }
      recordingsList.innerHTML = '';
      recordings.slice(-10).forEach(function(rec, i){
        var el = document.createElement('div');
        el.className = 'gallery-thumb';
        el.innerHTML = '<div style="flex:1;"><strong style="font-size:0.9rem;">Recording ' + (recordings.length - i) + '</strong><div class="muted" style="font-size:0.75rem;margin-top:2px;">' + 
          rec.duration + 's • ' + rec.size.toFixed(1) + 'MB' + '</div></div>' +
          '<button onclick="alert(\'Download feature - to be implemented\')" class="button secondary small" style="padding:4px 8px;font-size:0.8rem;">⬇ Download</button>';
        recordingsList.appendChild(el);
      });
    }

    function saveSettings(){
      localStorage.setItem('md-sensitivity', sens.value);
      localStorage.setItem('md-confidence', document.getElementById('ai-confidence').value);
      localStorage.setItem('md-mode', modeSel.value);
      localStorage.setItem('md-auto-record', autoRecordCheckbox.checked);
      localStorage.setItem('md-stop-delay', autoStopDelay.value);
      alert('Settings saved locally');
    }

    function refreshRecordings(){
      updateRecordingsList();
    }

    function handleStartMonitoring(){
      if(running) return;
      setStatus('Starting camera...', '#fbbf24');
      startCamera().then(function(){
        startProcessing();
      }).catch(function(e){
        setStatus('Camera unavailable', '#f87171');
        console.error(e);
      });
    }

    function handleStopMonitoring(){
      stopProcessing();
      if(video.srcObject){
        video.srcObject.getTracks().forEach(function(t){ t.stop(); });
        video.srcObject = null;
      }
      recording = false;
      recordBtn.textContent = '⏺ Record';
      setStatus('● Offline', '#94a3b8');
    }

    function startCamera(){
      if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
        setStatus('Camera API not supported', '#f87171');
        return Promise.reject('No camera API');
      }
      return navigator.mediaDevices.getUserMedia({video:true, audio:false})
        .then(function(stream){
          video.srcObject = stream;
          return new Promise(function(resolve){
            video.onloadedmetadata = function(){
              canvas.width = video.videoWidth;
              canvas.height = video.videoHeight;
              resolve();
            };
          });
        });
    }

    function startProcessing(){
      running = true;
      startBtn.style.display = 'none';
      stopBtn.style.display = 'inline-block';
      captureBtn.style.display = 'inline-block';
      recordBtn.style.display = 'inline-block';
      setStatus('Live Monitoring', '#34d399');
      
      var frameCount = 0;
      var lastFpsTime = Date.now();

      function loop(){
        if(!running) return;
        try {
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          var curr = ctx.getImageData(0, 0, canvas.width, canvas.height);

          // FPS Counter
          frameCount++;
          var now = Date.now();
          if(now - lastFpsTime >= 1000){
            document.getElementById('fps-counter').textContent = frameCount;
            frameCount = 0;
            lastFpsTime = now;
          }

          // Save recording chunk every 500ms
          if(recording && now % 500 === 0){
            saveRecordingChunk();
          }

          // Motion Detection
          if(prevFrame && modeSel.value !== 'ai'){
            var diff = frameDiff(prevFrame.data, curr.data);
            document.getElementById('motion-score').textContent = diff;
            if(diff > parseInt(sens.value, 10)){
              lastMotionTime = Date.now();
              addEvent('🔴 Motion Detected', diff);
              if(autoCapture.checked) captureSnapshot();
              if(autoRecordCheckbox.checked && !recording) toggleRecording();
            }
          }

          // AI Detection
          if(modeSel.value !== 'motion' && model){
            model.detect(canvas).then(function(predictions){
              var people = predictions.filter(function(p){
                var label = (p.className || p.class || '').toLowerCase();
                return label === 'person';
              });
              var objects = predictions.filter(function(p){
                var label = (p.className || p.class || '').toLowerCase();
                return label !== 'person';
              });
              
              if(people.length){
                lastMotionTime = Date.now();
                addEvent('👤 Person Detected (' + people.length + ')', people.length, null, people[0].score);
                if(autoCapture.checked) captureSnapshot();
                if(autoRecordCheckbox.checked && !recording) toggleRecording();
              }
              if(objects.length && objects.length > 0){
                addEvent('🚗 Object: ' + (objects[0].className || objects[0].class || 'unknown'), 1, null, objects[0].score);
              }
            }).catch(function(e){});
          }

          prevFrame = curr;

          // Auto-stop recording
          if(recording && lastMotionTime && (Date.now() - lastMotionTime > parseInt(autoStopDelay.value, 10) * 1000)){
            toggleRecording();
            lastMotionTime = null;
          }

        } catch(e){ console.error(e); }
        loopId = requestAnimationFrame(loop);
      }
      loop();
    }

    function stopProcessing(){
      running = false;
      if(loopId) cancelAnimationFrame(loopId);
      startBtn.style.display = 'inline-block';
      stopBtn.style.display = 'none';
      captureBtn.style.display = 'none';
      recordBtn.style.display = 'none';
    }

    function frameDiff(a, b){
      var l = a.length, i = 0, sum = 0;
      for(; i < l; i += 4){
        var la = 0.299 * a[i] + 0.587 * a[i+1] + 0.114 * a[i+2];
        var lb = 0.299 * b[i] + 0.587 * b[i+1] + 0.114 * b[i+2];
        sum += Math.abs(la - lb);
      }
      return Math.round((sum / (a.length / 4)) / 10);
    }

    // Drag-and-drop video file for testing
    var wrap = document.querySelector('.video-wrap');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(ev){
      wrap.addEventListener(ev, function(e){ e.preventDefault(); e.stopPropagation(); });
    });
    wrap.addEventListener('drop', function(e){
      var f = e.dataTransfer.files[0];
      if(f && f.type.indexOf('video') === 0){
        video.src = URL.createObjectURL(f);
        video.play();
        running = true;
        startProcessing();
      }
    });

    // Load saved settings
    if(localStorage.getItem('md-sensitivity')) sens.value = localStorage.getItem('md-sensitivity');
    if(localStorage.getItem('md-confidence')) document.getElementById('ai-confidence').value = localStorage.getItem('md-confidence');
    if(localStorage.getItem('md-mode')) modeSel.value = localStorage.getItem('md-mode');
    if(localStorage.getItem('md-auto-record') === 'true') autoRecordCheckbox.checked = true;

    init();
  })();
</script>

<?php include 'includes/footer.php'; ?>

