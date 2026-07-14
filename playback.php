<?php include 'includes/header.php'; ?>
<div class="card">
  <h2>Playback Console</h2>
  <div class="muted">Review recorded footage, export clips, and capture snapshots.</div>

  <div class="playback-layout">
    <section class="viewer card">
      <div class="viewer-top">
        <div class="meta">
          <div><strong id="camera-name">Camera: Lobby North</strong></div>
          <div class="muted" id="recording-info">Recorded: 2026-07-08 • 14:15 — 14:35</div>
        </div>
        <div class="actions">
          <label class="file-upload">
            Load file
            <input id="file-input" type="file" accept="video/*" style="display:none">
          </label>
          <button id="export-clip" class="button">Export Clip</button>
        </div>
      </div>

      <div class="video-wrap">
        <video id="playback-video" controls preload="metadata" style="width:100%; max-height:520px; background:#000"></video>
        <div class="video-overlay" aria-hidden="true" id="overlay-msg" style="display:none">No video loaded</div>
      </div>

      <div class="controls-row">
        <div class="left-controls">
          <button id="btn-play" class="button">Play</button>
          <button id="btn-pause" class="button secondary">Pause</button>
          <button id="btn-stop" class="button secondary">Stop</button>
          <button id="btn-snapshot" class="button">Snapshot</button>
        </div>
        <div class="center-controls">
          <input id="seek" type="range" min="0" max="100" value="0" class="seek-range">
          <div class="time-code"><span id="cur-time">00:00</span> / <span id="dur">00:00</span></div>
        </div>
        <div class="right-controls">
          <label>Speed
            <select id="speed-select" class="select"><option>0.5</option><option selected>1</option><option>1.5</option><option>2</option></select>
          </label>
          <button id="download-btn" class="button">Download</button>
        </div>
      </div>

      <div id="snapshots" class="snapshots"></div>
    </section>

    <aside class="sidebar card">
      <h4>Timeline & Clips</h4>
      <div id="timeline" class="timeline-list">
        <div class="timeline-item" data-src="samples/lobby-20260708.mp4">
          <div class="t-left">14:15 • Lobby North</div>
          <div class="t-right"><button class="button small load-clip">Load</button></div>
        </div>
        <div class="timeline-item" data-src="samples/parking-20260708.mp4">
          <div class="t-left">14:22 • Parking Lot</div>
          <div class="t-right"><button class="button small load-clip">Load</button></div>
        </div>
        <div class="timeline-item" data-src="samples/reception-20260708.mp4">
          <div class="t-left">14:30 • Reception</div>
          <div class="t-right"><button class="button small load-clip">Load</button></div>
        </div>
      </div>

      <h4 style="margin-top:14px">Quick Filters</h4>
      <div class="form-row">
        <div class="field"><label>Camera</label><select id="filter-camera" class="select"><option>Lobby North</option><option>Parking Lot</option><option>Reception</option></select></div>
        <div class="field"><label>Date</label><input id="filter-date" type="date" class="input" value="2026-07-08"></div>
      </div>

      <div style="margin-top:12px;">
        <button id="btn-search" class="button">Search Recordings</button>
        <div class="muted" style="margin-top:8px">Use timeline to quickly access clips.</div>
      </div>
    </aside>
  </div>
</div>

<style>
  .playback-layout{display:flex;gap:18px;align-items:flex-start}
  .viewer{flex:2}
  .sidebar{flex:1;min-width:260px}
  .viewer-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .meta .muted{font-size:13px;color:#777}
  .file-upload{background:#f3f4f6;padding:6px 10px;border-radius:4px;cursor:pointer}
  .controls-row{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
  .seek-range{width:420px}
  .snapshots img{max-width:160px;margin:8px;border:1px solid #ddd}
  .timeline-list{display:flex;flex-direction:column;gap:8px}
  .timeline-item{display:flex;justify-content:space-between;align-items:center;padding:8px;border:1px solid #eee;border-radius:4px}
  @media(max-width:900px){.playback-layout{flex-direction:column}.seek-range{width:100%}}
</style>

<script>
  (function(){
    var video = document.getElementById('playback-video');
    var play = document.getElementById('btn-play');
    var pause = document.getElementById('btn-pause');
    var stop = document.getElementById('btn-stop');
    var snapshot = document.getElementById('btn-snapshot');
    var seek = document.getElementById('seek');
    var curTime = document.getElementById('cur-time');
    var dur = document.getElementById('dur');
    var speed = document.getElementById('speed-select');
    var downloadBtn = document.getElementById('download-btn');
    var fileInput = document.getElementById('file-input');
    var overlay = document.getElementById('overlay-msg');

    function formatTime(s){
      if (isNaN(s)) return '00:00';
      var m = Math.floor(s/60), sec = Math.floor(s%60);
      return (m<10? '0'+m: m) + ':' + (sec<10? '0'+sec: sec);
    }

    play.addEventListener('click', function(){ if(video.src) video.play(); });
    pause.addEventListener('click', function(){ if(video.src) video.pause(); });
    stop.addEventListener('click', function(){ if(video.src){ video.pause(); video.currentTime = 0; }});

    snapshot.addEventListener('click', function(){
      if(!video.src) return;
      var c = document.createElement('canvas'); c.width = video.videoWidth; c.height = video.videoHeight;
      c.getContext('2d').drawImage(video,0,0,c.width,c.height);
      var img = new Image(); img.src = c.toDataURL('image/png'); document.getElementById('snapshots').appendChild(img);
    });

    video.addEventListener('loadedmetadata', function(){
      seek.max = Math.floor(video.duration);
      dur.textContent = formatTime(video.duration);
      overlay.style.display = 'none';
    });

    video.addEventListener('timeupdate', function(){
      seek.value = Math.floor(video.currentTime);
      curTime.textContent = formatTime(video.currentTime);
    });

    seek.addEventListener('input', function(){ video.currentTime = seek.value; });
    speed.addEventListener('change', function(){ video.playbackRate = parseFloat(speed.value); });

    downloadBtn.addEventListener('click', function(){
      if(!video.src) return alert('No video to download');
      var a = document.createElement('a'); a.href = video.src; a.download = 'recording.mp4'; document.body.appendChild(a); a.click(); a.remove();
    });

    document.querySelectorAll('.load-clip').forEach(function(btn){
      btn.addEventListener('click', function(e){
        var src = e.target.closest('.timeline-item').getAttribute('data-src');
        if(src){ video.src = src; video.load(); overlay.style.display='none'; document.getElementById('camera-name').textContent = 'Camera: ' + e.target.closest('.timeline-item').querySelector('.t-left').textContent.split('•')[1].trim(); }
      });
    });

    fileInput.addEventListener('change', function(e){
      var f = e.target.files[0]; if(!f) return; var url = URL.createObjectURL(f); video.src = url; video.load(); overlay.style.display='none';
    });

    // Drag & drop support
    var wrap = document.querySelector('.video-wrap');
    ['dragenter','dragover','dragleave','drop'].forEach(function(ev){ wrap.addEventListener(ev,function(e){e.preventDefault(); e.stopPropagation();});});
    wrap.addEventListener('drop', function(e){ var f = e.dataTransfer.files[0]; if(f && f.type.indexOf('video')===0){ video.src = URL.createObjectURL(f); video.load(); }});

    // Initial overlay
    if(!video.src) overlay.style.display='flex';

  })();
</script>

<?php include 'includes/footer.php'; ?>

