<?php include 'includes/header.php'; ?>
<style>
  .live-monitoring-shell {
    position: relative;
    min-height: calc(100vh - 120px);
    padding: 32px 24px 24px;
    background: radial-gradient(circle at top left, rgba(59,130,246,0.16), transparent 24%),
                radial-gradient(circle at bottom right, rgba(16,185,129,0.14), transparent 18%),
                linear-gradient(180deg, #060b14 0%, #07121f 45%, #0f1f34 100%);
    overflow: hidden;
  }
  .live-monitoring-shell::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 120px 120px;
    opacity: 0.65;
    pointer-events: none;
  }
  .live-monitoring-shell::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 15% 20%, rgba(59,130,246,0.16), transparent 16%),
                radial-gradient(circle at 85% 12%, rgba(16,185,129,0.14), transparent 12%),
                radial-gradient(circle at 70% 78%, rgba(148,163,184,0.10), transparent 14%);
    pointer-events: none;
  }
  .live-monitoring-shell > .grid,
  .live-monitoring-shell > .card,
  .live-monitoring-shell .card {
    position: relative;
    z-index: 1;
  }
  #camera-panel {
    background: rgba(4, 11, 19, 0.92);
    border: 1px solid rgba(96,165,250,0.14);
  }
  .video-placeholder,
  .viewer-panel,
  .camera-card,
  .alert-config,
  .alert-history-container {
    background: rgba(8, 16, 29, 0.88);
    border: 1px solid rgba(255,255,255,0.08);
  }
  .video-empty {
    background: rgba(15, 24, 42, 0.95);
    color: rgba(203,213,225,0.85);
  }
  .camera-card .thumb {
    background: linear-gradient(135deg, rgba(59,130,246,0.20), rgba(16,185,129,0.12));
  }
  .people-count-panel {
    background: rgba(12, 20, 34, 0.92);
    border: 1px solid rgba(96,165,250,0.12);
  }
</style>
<div class="live-monitoring-shell">
<div class="grid grid-2">
  <div class="card" id="camera-panel">
    <h3>Live Monitoring Console</h3>
    <div class="viewer-panel">
      <div class="video-placeholder">
        <video id="camera-stream" autoplay playsinline muted style="display:none; width:100%; height:100%; object-fit: cover;"></video>
        <div class="video-empty" id="video-placeholder">Click Open Camera to start monitoring.</div>
      </div>
      <div class="viewer-info">
        <div>
          <div class="label">Selected Camera</div>
          <div class="value" id="active-camera-name">Gate A</div>
        </div>
        <div>
          <div class="label">Status</div>
          <div class="value" id="active-camera-status">Idle</div>
        </div>
      </div>
    </div>
    <div class="camera-grid">
      <div class="camera-card" data-camera="Gate A">
        <div class="thumb"></div>
        <strong>Gate A</strong>
        <div class="muted">Grid 1 • 4K</div>
        <button class="button small" onclick="openCamera('Gate A')" style="margin-top: 10px; display: inline-block;">Open Camera</button>
      </div>
      <div class="camera-card" data-camera="Hallway">
        <div class="thumb"></div>
        <strong>Hallway</strong>
        <div class="muted">Grid 2 • 1080p</div>
        <button class="button small" onclick="openCamera('Hallway')" style="margin-top: 10px; display: inline-block;">Open Camera</button>
      </div>
      <div class="camera-card" data-camera="Parking">
        <div class="thumb"></div>
        <strong>Parking</strong>
        <div class="muted">Grid 3 • 720p</div>
        <button class="button small" onclick="openCamera('Parking')" style="margin-top: 10px; display: inline-block;">Open Camera</button>
      </div>
      <div class="camera-card" data-camera="Reception">
        <div class="thumb"></div>
        <strong>Reception</strong>
        <div class="muted">Grid 4 • 1080p</div>
        <button class="button small" onclick="openCamera('Reception')" style="margin-top: 10px; display: inline-block;">Open Camera</button>
      </div>
    </div>
  </div>
  <div class="card">
    <h3>Controls & Filters</h3>
    <div class="form-row">
      <div class="field">
        <label>Camera Search</label>
        <input id="camera-search" class="input" placeholder="Search camera">
      </div>
      <div class="field">
        <label>View Layout</label>
        <select id="layout-select" class="select"><option value="4">4 Cameras</option><option value="9">9 Cameras</option><option value="16">16 Cameras</option><option value="32">32 Cameras</option></select>
      </div>
    </div>
    <div class="form-row">
      <div class="field">
        <label>PTZ Controls</label>
        <div class="grid grid-2" style="margin-top: 6px;">
          <button id="pan-left" class="button secondary">Pan Left</button>
          <button id="pan-right" class="button secondary">Pan Right</button>
          <button id="tilt-up" class="button secondary">Tilt Up</button>
          <button id="tilt-down" class="button secondary">Tilt Down</button>
        </div>
      </div>
      <div class="field">
        <label>Actions</label>
        <div class="grid grid-2" style="margin-top: 6px;">
          <button id="snapshot-btn" class="button secondary">Snapshot</button>
          <button id="record-btn" class="button secondary" onclick="toggleRecording()">Record</button>
          <button id="audio-toggle" class="button secondary">Audio On</button>
          <button id="quality-btn" class="button secondary">Quality</button>
        </div>
        <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span id="record-status" class="muted">Not recording.</span>
          <a id="download-recording" class="button small secondary" style="display:none; text-decoration:none;" download="camera-recording.webm">Download Recording</a>
        </div>
      </div>
    </div>
    <div class="footer-note">Full screen, status indicators, and live timestamps are available on the operator console.</div>
    <div class="people-count-panel">
      <h4>AI Detection Analytics</h4>
      <div class="count-display">
        <span class="count-value" id="people-count">0</span>
        <span class="count-label">people currently detected</span>
      </div>
      <div class="line-crossing-stats">
        <div class="crossing-stat">
          <span class="crossing-label">IN Count:</span>
          <span class="crossing-value" id="in-count">0</span>
        </div>
        <div class="crossing-stat">
          <span class="crossing-label">OUT Count:</span>
          <span class="crossing-value" id="out-count">0</span>
        </div>
      </div>
      <div class="people-status-row">
        <span id="people-status">No occupancy detected.</span>
        <button class="button small" id="refresh-people">Refresh</button>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Alert Configuration & History Panel -->
<div class="grid grid-2">
  <div class="card">
    <h3>⚠️ Alert Configuration</h3>
    <div class="alert-config">
      <div class="field">
        <label>IN Count Threshold</label>
        <input type="number" id="in-threshold" class="input" min="1" max="999" value="50" placeholder="Alert when IN count exceeds...">
        <span class="field-hint">Alert triggers when IN count reaches this value</span>
      </div>
      <div class="field">
        <label>OUT Count Threshold</label>
        <input type="number" id="out-threshold" class="input" min="1" max="999" value="50" placeholder="Alert when OUT count exceeds...">
        <span class="field-hint">Alert triggers when OUT count reaches this value</span>
      </div>
      <div class="field">
        <label>Email Address</label>
        <input type="email" id="alert-email" class="input" value="sirmike6072@gmail.com" placeholder="Notification email">
        <span class="field-hint">Where to send threshold alerts</span>
      </div>
      <div class="form-row">
        <div class="field">
          <label>Enable Alerts</label>
          <div style="margin-top: 8px;">
            <input type="checkbox" id="alerts-enabled" style="width: auto; margin-right: 8px;" checked>
            <span class="muted" style="font-size: 0.9rem;">Active</span>
          </div>
        </div>
        <div class="field">
          <label>Sound Notification</label>
          <div style="margin-top: 8px;">
            <input type="checkbox" id="sound-enabled" style="width: auto; margin-right: 8px;" checked>
            <span class="muted" style="font-size: 0.9rem;">On</span>
          </div>
        </div>
      </div>
      <button class="button" id="save-alerts-btn" style="width: 100%; margin-top: 12px;">💾 Save Alert Settings</button>
      <button class="button secondary" id="test-email-btn" style="width: 100%; margin-top: 8px;">📧 Send Test Email</button>
    </div>
  </div>
  
  <div class="card">
    <h3>📋 Alert History</h3>
    <div class="alert-history-container">
      <div id="alert-history-list" class="alert-history-list">
        <div class="empty-state">No alerts triggered yet. Configure thresholds to start monitoring.</div>
      </div>
    </div>
    <button class="button secondary" id="clear-history-btn" style="width: 100%; margin-top: 12px;">Clear History</button>
  </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.21.0/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
<script>
  var currentStream = null;
  var recording = false;
  var audioEnabled = false;
  var mediaRecorder = null;
  var recordedChunks = [];
  var detectionInterval = null;
  var model = null;
  var canvas = null;
  var ctx = null;
  var trackedObjects = {};
  var nextTrackingId = 1;
  var inCount = 0;
  var outCount = 0;
  var lineYPosition = 0.5; // Virtual line at 50% of video height

  // Tracking state for each person
  var trackingMap = {}; // Maps bounding box to tracking info
  var personHistory = {}; // Historical positions for line crossing

  // Alert System Variables
  var alertsEnabled = true;
  var soundEnabled = true;
  var inThreshold = 50;
  var outThreshold = 50;
  var alertEmail = 'sirmike6072@gmail.com';
  var alertHistory = [];
  var alertTriggeredStates = {}; // Prevent duplicate alerts for same threshold

  function stopDetection() {
    if (detectionInterval) {
      clearInterval(detectionInterval);
      detectionInterval = null;
    }
  }

  function stopStream() {
    stopDetection();
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
    }
    if (currentStream) {
      currentStream.getTracks().forEach(function(track) {
        track.stop();
      });
      currentStream = null;
    }
  }

  function setStatus(text) {
    document.getElementById('active-camera-status').textContent = text;
  }

  function updatePeopleCount(value) {
    var count = typeof value === 'number' ? value : 0;
    document.getElementById('people-count').textContent = count;
    document.getElementById('people-status').textContent = count === 0 ? 'No occupancy detected.' : 'Detected ' + count + ' person' + (count === 1 ? '' : 's') + ' in view.';
  }

  function updateCrossingCounts() {
    document.getElementById('in-count').textContent = inCount;
    document.getElementById('out-count').textContent = outCount;
    
    // Check thresholds for alerts
    checkThresholdAlerts();
  }

  function checkThresholdAlerts() {
    if (!alertsEnabled) return;

    // Check IN threshold
    if (inCount >= inThreshold && !alertTriggeredStates['in']) {
      triggerAlert('IN', inCount, inThreshold);
      alertTriggeredStates['in'] = true;
    } else if (inCount < inThreshold) {
      alertTriggeredStates['in'] = false;
    }

    // Check OUT threshold
    if (outCount >= outThreshold && !alertTriggeredStates['out']) {
      triggerAlert('OUT', outCount, outThreshold);
      alertTriggeredStates['out'] = true;
    } else if (outCount < outThreshold) {
      alertTriggeredStates['out'] = false;
    }
  }

  function triggerAlert(type, currentCount, threshold) {
    var message = type + ' count reached ' + currentCount + ' (threshold: ' + threshold + ')';
    var camera = document.getElementById('active-camera-name').textContent;
    var timestamp = new Date().toLocaleString();
    
    // Add to alert history
    alertHistory.unshift({
      type: type,
      count: currentCount,
      camera: camera,
      timestamp: timestamp,
      message: message
    });
    
    // Keep only last 50 alerts
    if (alertHistory.length > 50) {
      alertHistory.pop();
    }

    // Update UI
    updateAlertHistoryDisplay();
    
    // Show toast notification
    showToast(message, 'alert');
    
    // Play sound if enabled
    if (soundEnabled) {
      playAlertSound();
    }
    
    // Send email notification
    sendEmailAlert(type, currentCount, camera, timestamp);
  }

  function sendEmailAlert(type, count, camera, timestamp) {
    fetch('send-alert.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type: type,
        count: count,
        camera: camera,
        timestamp: timestamp,
        email: alertEmail
      })
    }).catch(function(error) {
      console.log('Email notification queued (backend may process asynchronously)', error);
    });
  }

  function playAlertSound() {
    // Create a simple alert tone using Web Audio API
    var audioContext = new (window.AudioContext || window.webkitAudioContext)();
    var oscillator = audioContext.createOscillator();
    var gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.3);
  }

  function showToast(message, type) {
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    
    var icon = type === 'alert' ? '⚠️' : '✓';
    toast.innerHTML = '<span class="toast-icon">' + icon + '</span><span class="toast-message">' + message + '</span>';
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
      toast.classList.add('fade-out');
      setTimeout(function() {
        container.removeChild(toast);
      }, 300);
    }, 5000);
  }

  function updateAlertHistoryDisplay() {
    var historyList = document.getElementById('alert-history-list');
    
    if (alertHistory.length === 0) {
      historyList.innerHTML = '<div class="empty-state">No alerts triggered yet. Configure thresholds to start monitoring.</div>';
      return;
    }
    
    historyList.innerHTML = '';
    alertHistory.forEach(function(alert, index) {
      var alertItem = document.createElement('div');
      alertItem.className = 'alert-item alert-' + alert.type.toLowerCase();
      alertItem.innerHTML = '<div class="alert-header">' +
        '<span class="alert-type">' + alert.type + ' Alert</span>' +
        '<span class="alert-time">' + alert.timestamp + '</span>' +
        '</div>' +
        '<div class="alert-content">' +
        '<div class="alert-detail">Camera: ' + alert.camera + '</div>' +
        '<div class="alert-detail">Count: ' + alert.count + '</div>' +
        '<div class="alert-message">' + alert.message + '</div>' +
        '</div>';
      historyList.appendChild(alertItem);
    });
  }

  function saveAlertSettings() {
    inThreshold = parseInt(document.getElementById('in-threshold').value, 10) || 50;
    outThreshold = parseInt(document.getElementById('out-threshold').value, 10) || 50;
    alertEmail = document.getElementById('alert-email').value || 'sirmike6072@gmail.com';
    alertsEnabled = document.getElementById('alerts-enabled').checked;
    soundEnabled = document.getElementById('sound-enabled').checked;
    
    // Reset triggered states when thresholds change
    alertTriggeredStates = {};
    
    showToast('Alert settings saved successfully!', 'success');
  }

  function clearAlertHistory() {
    if (confirm('Clear all alert history? This cannot be undone.')) {
      alertHistory = [];
      updateAlertHistoryDisplay();
      showToast('Alert history cleared.', 'success');
    }
  }

  function sendTestEmail() {
    var testBtn = document.getElementById('test-email-btn');
    var originalText = testBtn.textContent;
    
    testBtn.disabled = true;
    testBtn.textContent = '⏳ Sending...';
    
    var camera = document.getElementById('active-camera-name').textContent || 'Test Camera';
    var now = new Date();
    
    fetch('send-alert.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type: 'TEST',
        count: 42,
        camera: camera,
        timestamp: now.toLocaleString(),
        email: document.getElementById('alert-email').value || 'sirmike6072@gmail.com'
      })
    })
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      if (data.success && (data.email_sent || data.email_queued)) {
        showToast('✅ Test email sent successfully to: ' + (document.getElementById('alert-email').value || 'sirmike6072@gmail.com'), 'success');
        console.log('Test email response:', data);
        
        // Add test alert to history
        alertHistory.unshift({
          type: 'TEST',
          count: 42,
          camera: camera,
          timestamp: now.toLocaleString(),
          message: 'Test notification sent'
        });
        updateAlertHistoryDisplay();
      } else {
        showToast('⚠️ Test email queued but may not be sent immediately. Check email settings.', 'alert');
        console.log('Test email response:', data);
      }
      
      testBtn.disabled = false;
      testBtn.textContent = originalText;
    })
    .catch(function(error) {
      showToast('❌ Error sending test email. Check console.', 'alert');
      console.error('Test email error:', error);
      testBtn.disabled = false;
      testBtn.textContent = originalText;
    });
  }

  function getTrackingKey(bbox) {
    return bbox.x + '_' + bbox.y + '_' + bbox.width + '_' + bbox.height;
  }

  function findNearestTrack(currentBbox, maxDistance) {
    var nearest = null;
    var minDistance = maxDistance;

    Object.keys(trackingMap).forEach(function(key) {
      var track = trackingMap[key];
      var prevBbox = track.lastBbox;
      var dx = (currentBbox.x + currentBbox.width / 2) - (prevBbox.x + prevBbox.width / 2);
      var dy = (currentBbox.y + currentBbox.height / 2) - (prevBbox.y + prevBbox.height / 2);
      var distance = Math.sqrt(dx * dx + dy * dy);

      if (distance < minDistance) {
        minDistance = distance;
        nearest = track;
      }
    });

    return nearest;
  }

  function checkLineCrossing(trackId, centerY, videoHeight) {
    var linePixelPosition = lineYPosition * videoHeight;

    if (!personHistory[trackId]) {
      personHistory[trackId] = { lastY: centerY, crossedLine: false };
      return;
    }

    var lastY = personHistory[trackId].lastY;
    var crossedLine = personHistory[trackId].crossedLine;

    // Check if person crossed the line
    if (lastY < linePixelPosition && centerY >= linePixelPosition && !crossedLine) {
      inCount++;
      personHistory[trackId].crossedLine = true;
      updateCrossingCounts();
    } else if (lastY > linePixelPosition && centerY <= linePixelPosition && !crossedLine) {
      outCount++;
      personHistory[trackId].crossedLine = true;
      updateCrossingCounts();
    }

    // Reset crossing flag when person moves back across
    if ((centerY < linePixelPosition && crossedLine && lastY >= linePixelPosition) ||
        (centerY > linePixelPosition && crossedLine && lastY <= linePixelPosition)) {
      personHistory[trackId].crossedLine = false;
    }

    personHistory[trackId].lastY = centerY;
  }

  function drawDetections(video, predictions) {
    if (!canvas || !ctx) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw virtual line
    var lineY = lineYPosition * canvas.height;
    ctx.strokeStyle = '#FF0000';
    ctx.lineWidth = 3;
    ctx.setLineDash([10, 5]);
    ctx.beginPath();
    ctx.moveTo(0, lineY);
    ctx.lineTo(canvas.width, lineY);
    ctx.stroke();
    ctx.setLineDash([]);

    // Draw line label
    ctx.fillStyle = '#FF0000';
    ctx.font = 'bold 12px Arial';
    ctx.fillText('Virtual Boundary Line', 10, lineY - 5);

    // Draw timestamp
    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 14px Arial';
    var now = new Date();
    var timestamp = now.toLocaleString();
    ctx.fillText(timestamp, 10, 25);

    // Draw detection info
    var personCount = 0;
    var currentPersonTrackIds = {};
    var processedTrackIdsInFrame = {};

    predictions.forEach(function(prediction) {
      var label = (prediction.className || prediction.class || '').toLowerCase();
      if (label !== 'person' || prediction.score < 0.6) {
        return;
      }

      var bbox = prediction.bbox;
      var x = bbox[0];
      var y = bbox[1];
      var width = bbox[2];
      var height = bbox[3];
      var centerY = y + height / 2;

      // Find or create tracking info
      var maxDistance = Math.max(width, height) * 1.5;
      var track = findNearestTrack({ x: x, y: y, width: width, height: height }, maxDistance);

      var trackId;
      if (track) {
        trackId = track.id;
        delete trackingMap[getTrackingKey(track.lastBbox)];
      } else {
        trackId = nextTrackingId++;
      }

      if (!currentPersonTrackIds[trackId]) {
        currentPersonTrackIds[trackId] = true;
        personCount++;
      }

      // Store tracking info
      trackingMap[getTrackingKey({ x: x, y: y, width: width, height: height })] = {
        id: trackId,
        lastBbox: { x: x, y: y, width: width, height: height },
        score: prediction.score
      };

      // Check line crossing once per track per frame
      if (!processedTrackIdsInFrame[trackId]) {
        checkLineCrossing(trackId, centerY, canvas.height);
        processedTrackIdsInFrame[trackId] = true;
      }

      // Draw bounding box (green)
      ctx.strokeStyle = '#00FF00';
      ctx.lineWidth = 2;
      ctx.strokeRect(x, y, width, height);

      // Draw tracking ID
      ctx.fillStyle = '#00FF00';
      ctx.font = 'bold 16px Arial';
      ctx.fillText('ID: ' + trackId, x + 5, y - 5);

      // Draw confidence score
      ctx.fillStyle = '#FFFFFF';
      ctx.font = 'bold 12px Arial';
      var confidence = (prediction.score * 100).toFixed(1) + '%';
      ctx.fillText(confidence, x + 5, y + height + 20);
    });

    updatePeopleCount(personCount);
  }

  function openCamera(cameraName) {
    document.getElementById('active-camera-name').textContent = cameraName;
    setStatus('Requesting camera...');
    document.getElementById('video-placeholder').style.display = 'none';

    var video = document.getElementById('camera-stream');
    var cards = document.querySelectorAll('.camera-card');
    cards.forEach(function(card) {
      card.classList.toggle('active', card.dataset.camera === cameraName);
    });

    stopStream();
    recording = false;
    document.getElementById('record-btn').textContent = 'Record';
    document.getElementById('record-status').textContent = 'Not recording.';
    document.getElementById('download-recording').style.display = 'none';

    // Reset tracking and counts
    trackedObjects = {};
    trackingMap = {};
    personHistory = {};
    nextTrackingId = 1;
    inCount = 0;
    outCount = 0;
    updateCrossingCounts();

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setStatus('Camera API not supported.');
      document.getElementById('video-placeholder').style.display = 'block';
      document.getElementById('video-placeholder').textContent = 'Your browser cannot access the webcam.';
      updatePeopleCount(0);
      return;
    }

    navigator.mediaDevices.getUserMedia({ video: true, audio: audioEnabled })
      .then(function(stream) {
        currentStream = stream;
        video.srcObject = stream;
        video.style.display = 'block';

        // Ensure canvas is positioned over video
        if (canvas) {
          canvas.style.display = 'block';
        }

        setStatus('Live • Monitoring ' + cameraName);
        if (model) {
          startDetection(video);
        }
      })
      .catch(function(error) {
        setStatus('Permission denied or unavailable.');
        document.getElementById('video-placeholder').style.display = 'block';
        document.getElementById('video-placeholder').textContent = 'Unable to start webcam feed. Allow camera access.';
        console.error(error);
      });
  }

  function applySearch() {
    var query = document.getElementById('camera-search').value.toLowerCase();
    var cards = document.querySelectorAll('.camera-card');
    cards.forEach(function(card) {
      var name = card.querySelector('strong').textContent.toLowerCase();
      card.style.display = name.indexOf(query) === -1 ? 'none' : 'block';
    });
  }

  function applyLayout() {
    var value = parseInt(document.getElementById('layout-select').value, 10);
    var wrapper = document.querySelector('.camera-grid');
    wrapper.style.gridTemplateColumns = value > 2 ? 'repeat(3, minmax(0,1fr))' : 'repeat(2, minmax(0,1fr))';
    setStatus('View layout set to ' + value + ' cameras (simulation).');
  }

  function handlePTZ(command) {
    setStatus(command + ' command sent to ' + document.getElementById('active-camera-name').textContent + '.');
  }

  function takeSnapshot() {
    var video = document.getElementById('camera-stream');
    if (!currentStream || video.style.display === 'none') {
      setStatus('No live feed to snapshot.');
      return;
    }
    var snapshotCanvas = document.createElement('canvas');
    snapshotCanvas.width = video.videoWidth;
    snapshotCanvas.height = video.videoHeight;
    var snapshotCtx = snapshotCanvas.getContext('2d');
    snapshotCtx.drawImage(video, 0, 0, snapshotCanvas.width, snapshotCanvas.height);
    if (canvas) {
      snapshotCtx.drawImage(canvas, 0, 0);
    }
    var img = document.createElement('img');
    img.src = snapshotCanvas.toDataURL('image/png');
    img.style.maxWidth = '100%';
    img.style.marginTop = '12px';
    document.querySelector('.viewer-panel').appendChild(img);
    setStatus('Snapshot captured with AI overlays.');
  }

  function startRecording() {
    console.log('startRecording called');
    var video = document.getElementById('camera-stream');
    if (!currentStream || video.style.display === 'none') {
      setStatus('Open a camera first to record.');
      recording = false;
      document.getElementById('record-btn').textContent = 'Record';
      return;
    }

    if (typeof MediaRecorder === 'undefined') {
      setStatus('Recording not supported by this browser.');
      recording = false;
      document.getElementById('record-btn').textContent = 'Record';
      return;
    }

    recordedChunks = [];
    try {
      mediaRecorder = new MediaRecorder(currentStream, { mimeType: 'video/webm; codecs=vp9' });
    } catch (e) {
      try {
        mediaRecorder = new MediaRecorder(currentStream);
      } catch (error) {
        setStatus('Recording unavailable: ' + error.message);
        recording = false;
        document.getElementById('record-btn').textContent = 'Record';
        return;
      }
    }

    mediaRecorder.ondataavailable = function(event) {
      if (event.data && event.data.size > 0) {
        recordedChunks.push(event.data);
      }
    };

    mediaRecorder.onstop = function() {
      console.log('mediaRecorder stopped, chunks:', recordedChunks.length);
      if (recordedChunks.length === 0) {
        setStatus('Recording stopped with no captured data.');
        return;
      }
      var blob = new Blob(recordedChunks, { type: 'video/webm' });
      var url = URL.createObjectURL(blob);
      var downloadLink = document.getElementById('download-recording');
      downloadLink.href = url;
      downloadLink.style.display = 'inline-block';
      downloadLink.download = document.getElementById('active-camera-name').textContent.replace(/\s+/g, '_') + '_recording.webm';
      downloadLink.textContent = 'Download Recording';
      setStatus('Recording stopped. Download ready.');
      document.getElementById('record-status').textContent = 'Recording stopped. Download available.';
    };

    mediaRecorder.onerror = function(event) {
      console.log('mediaRecorder error event', event);
      console.error('Recorder error:', event);
      setStatus('Recording error occurred.');
      recording = false;
      document.getElementById('record-btn').textContent = 'Record';
    };

    mediaRecorder.start();
    setStatus('Recording started.');
    document.getElementById('record-status').textContent = 'Recording in progress...';
  }

  function stopRecording() {
    console.log('stopRecording called, mediaRecorder state:', mediaRecorder && mediaRecorder.state);
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
    }
  }

  function toggleRecording() {
    console.log('toggleRecording called, currentStream:', !!currentStream, 'recording:', recording);
    if (!currentStream) {
      setStatus('Open a camera first to record.');
      return;
    }
    recording = !recording;
    document.getElementById('record-btn').textContent = recording ? 'Stop' : 'Record';
    if (recording) {
      startRecording();
    } else {
      stopRecording();
    }
  }

  function toggleAudio() {
    audioEnabled = !audioEnabled;
    var video = document.getElementById('camera-stream');
    video.muted = !audioEnabled;
    document.getElementById('audio-toggle').textContent = audioEnabled ? 'Audio Off' : 'Audio On';
    setStatus(audioEnabled ? 'Audio enabled.' : 'Audio muted.');
  }

  function changeQuality() {
    setStatus('Quality setting changed.');
  }

  function loadModel() {
    if (!window.cocoSsd) {
      setStatus('Person detection library unavailable.');
      return;
    }
    cocoSsd.load().then(function(loadedModel) {
      model = loadedModel;
      setStatus('AI Detection Ready • Open camera to start monitoring with analytics.');
      var video = document.getElementById('camera-stream');
      if (currentStream && video && video.srcObject) {
        startDetection(video);
      }
    }).catch(function(error) {
      setStatus('Failed to load detection model.');
      console.error(error);
    });
  }

  function detectPeople(video) {
    if (!model || video.readyState < 3) {
      return;
    }
    model.detect(video).then(function(predictions) {
      drawDetections(video, predictions);
    }).catch(function(error) {
      console.error('Detection error:', error);
    });
  }

  function startDetection(video) {
    stopDetection();
    detectionInterval = setInterval(function() {
      detectPeople(video);
    }, 500); // Run detection every 500ms for smooth tracking
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Create overlay canvas for AI visualizations
    var viewerPanel = document.querySelector('.viewer-panel');
    canvas = document.createElement('canvas');
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.display = 'none';
    canvas.style.cursor = 'pointer';
    canvas.style.zIndex = '10';
    viewerPanel.style.position = 'relative';
    viewerPanel.appendChild(canvas);

    ctx = canvas.getContext('2d');

    loadModel();
    var cards = document.querySelectorAll('.camera-card');
    cards.forEach(function(card) {
      card.addEventListener('click', function(event) {
        if (event.target.tagName.toLowerCase() !== 'button') {
          openCamera(card.dataset.camera);
        }
      });
    });

    document.getElementById('camera-search').addEventListener('input', applySearch);
    document.getElementById('layout-select').addEventListener('change', applyLayout);
    document.getElementById('pan-left').addEventListener('click', function() { handlePTZ('Pan Left'); });
    document.getElementById('pan-right').addEventListener('click', function() { handlePTZ('Pan Right'); });
    document.getElementById('tilt-up').addEventListener('click', function() { handlePTZ('Tilt Up'); });
    document.getElementById('tilt-down').addEventListener('click', function() { handlePTZ('Tilt Down'); });
    document.getElementById('snapshot-btn').addEventListener('click', takeSnapshot);
    document.getElementById('record-btn').addEventListener('click', toggleRecording);
    document.getElementById('audio-toggle').addEventListener('click', toggleAudio);
    document.getElementById('quality-btn').addEventListener('click', changeQuality);
    document.getElementById('refresh-people').addEventListener('click', refreshPeopleCount);
    
    // Alert System Event Listeners
    document.getElementById('save-alerts-btn').addEventListener('click', saveAlertSettings);
    document.getElementById('clear-history-btn').addEventListener('click', clearAlertHistory);
    document.getElementById('test-email-btn').addEventListener('click', sendTestEmail);
  });

  function refreshPeopleCount() {
    var video = document.getElementById('camera-stream');
    if (!model || !currentStream) {
      setStatus('Detection not ready yet.');
      return;
    }
    detectPeople(video);
    setStatus('People count refreshed.');
  }
</script>
<?php include 'includes/footer.php'; ?>

