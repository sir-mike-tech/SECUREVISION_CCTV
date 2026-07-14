<?php 
include 'includes/header.php';

// Check PHP mail function
$mailFunctionExists = function_exists('mail');
$logsDir = __DIR__ . '/logs';
$logsExist = is_dir($logsDir);
$alertsLogExists = $logsExist && file_exists($logsDir . '/alerts.log');
$emailLogExists = $logsExist && file_exists($logsDir . '/email-notifications.log');
$queueDir = $logsExist ? $logsDir . '/email-queue' : null;
$queueExists = $queueDir && is_dir($queueDir);

// Count queue items
$queueCount = 0;
if ($queueExists) {
    $queueFiles = glob($queueDir . '/*.json');
    $queueCount = is_array($queueFiles) ? count($queueFiles) : 0;
}

// Get PHP info
$phpVersion = phpversion();
$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
$osInfo = php_uname();
?>

<div class="grid grid-1">
  <div class="card">
    <h2>📧 Email & Alert System Diagnostics</h2>
    <div class="muted">Monitor and test your email notification system, queue status, and server configuration</div>
  </div>
</div>

<div class="grid grid-2">
  <!-- Email System Status -->
  <div class="card">
    <h3>✉️ Email System Status</h3>
    
    <div style="display: flex; flex-direction: column; gap: 12px;">
      <!-- PHP Mail Function -->
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 4px solid <?php echo $mailFunctionExists ? '#34d399' : '#ef4444'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
          <span style="font-weight: 600; font-size: 0.95rem;">PHP mail() Function</span>
          <span style="color: <?php echo $mailFunctionExists ? '#34d399' : '#ef4444'; ?>; font-weight: bold; font-size: 0.9rem;">
            <?php echo $mailFunctionExists ? '✓ ACTIVE' : '✗ DISABLED'; ?>
          </span>
        </div>
        <small style="color: #cbd5e1; display: block;">
          <?php echo $mailFunctionExists ? 'Direct email sending is available and ready.' : 'Mail function disabled - using queue system for deferred sending.'; ?>
        </small>
      </div>
      
      <!-- Logs Directory -->
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 4px solid <?php echo $logsExist ? '#34d399' : '#ef4444'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
          <span style="font-weight: 600; font-size: 0.95rem;">📁 Logs Directory</span>
          <span style="color: <?php echo $logsExist ? '#34d399' : '#ef4444'; ?>; font-weight: bold; font-size: 0.9rem;">
            <?php echo $logsExist ? '✓ EXISTS' : '✗ MISSING'; ?>
          </span>
        </div>
        <small style="color: #cbd5e1; display: block;">
          <?php echo $logsExist ? htmlspecialchars($logsDir) : 'Logs directory not found - create it for proper logging.'; ?>
        </small>
      </div>
      
      <!-- Alerts Log -->
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 4px solid <?php echo $alertsLogExists ? '#34d399' : '#fbbf24'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
          <span style="font-weight: 600; font-size: 0.95rem;">📋 Alerts Log</span>
          <span style="color: <?php echo $alertsLogExists ? '#34d399' : '#fbbf24'; ?>; font-weight: bold; font-size: 0.9rem;">
            <?php echo $alertsLogExists ? '✓ ACTIVE' : '⊘ INACTIVE'; ?>
          </span>
        </div>
        <small style="color: #cbd5e1; display: block;">
          Tracks all motion detection alerts and events.
        </small>
      </div>
      
      <!-- Email Queue -->
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 4px solid <?php echo $queueCount > 0 ? '#fbbf24' : '#34d399'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
          <span style="font-weight: 600; font-size: 0.95rem;">📤 Email Queue</span>
          <span style="color: <?php echo $queueCount > 0 ? '#fbbf24' : '#34d399'; ?>; font-weight: bold; font-size: 0.9rem;">
            <?php echo $queueCount . ' pending'; ?>
          </span>
        </div>
        <small style="color: #cbd5e1; display: block;">
          <?php echo $queueCount > 0 ? $queueCount . ' emails waiting to be processed.' : 'Queue is empty - system is up to date.'; ?>
        </small>
      </div>
    </div>
    
    <div style="display: flex; gap: 8px; margin-top: 16px;">
      <button class="button" onclick="testEmailFunction()" style="flex: 1;">🧪 Test Email System</button>
      <button class="button secondary" onclick="viewEmailLogs()" style="flex: 1;">📖 View Logs</button>
    </div>
  </div>
  
  <!-- System Configuration -->
  <div class="card">
    <h3>⚙️ Server Configuration</h3>
    
    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
      <div style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="color: #cbd5e1; font-size: 0.8rem; margin-bottom: 2px;">PHP Version</div>
        <strong style="color: #34d399;"><?php echo htmlspecialchars($phpVersion); ?></strong>
      </div>
      
      <div style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="color: #cbd5e1; font-size: 0.8rem; margin-bottom: 2px;">Web Server</div>
        <strong><?php echo htmlspecialchars($serverSoftware); ?></strong>
      </div>
      
      <div style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="color: #cbd5e1; font-size: 0.8rem; margin-bottom: 2px;">Server Hostname</div>
        <strong><?php echo htmlspecialchars($serverName); ?></strong>
      </div>
      
      <div style="padding: 10px 0;">
        <div style="color: #cbd5e1; font-size: 0.8rem; margin-bottom: 2px;">Operating System</div>
        <strong><?php echo htmlspecialchars($osInfo); ?></strong>
      </div>
    </div>
  </div>
</div>

<!-- Pending Email Queue Section -->
<div class="grid grid-1">
  <div class="card">
    <h3>📬 Pending Email Queue</h3>
    
    <div style="max-height: 400px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px;">
      <?php
      if ($queueExists && $queueCount > 0) {
          echo '<div style="display: flex; flex-direction: column; gap: 10px;">';
          
          $queueFiles = glob($queueDir . '/*.json');
          if (is_array($queueFiles)) {
              foreach ($queueFiles as $file) {
                  $content = json_decode(file_get_contents($file), true);
                  if (!is_array($content)) {
                      continue;
                  }
                  
                  $filename = basename($file);
                  $alertType = htmlspecialchars(isset($content['type']) ? $content['type'] : 'Unknown');
                  $recipientEmail = htmlspecialchars(isset($content['email']) ? $content['email'] : 'Unknown');
                  $createdTime = htmlspecialchars(isset($content['created']) ? $content['created'] : 'Unknown');
                  $subject = htmlspecialchars(isset($content['subject']) ? $content['subject'] : 'Motion Alert');
                  
                  echo '<div style="padding: 12px; background: rgba(34, 197, 85, 0.08); border: 1px solid #34d399; border-radius: 6px;">';
                  echo '  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">';
                  echo '    <div style="flex: 1;">';
                  echo '      <div style="font-weight: 600; margin-bottom: 4px;">🔔 ' . $alertType . ' Alert</div>';
                  echo '      <small style="color: #cbd5e1; display: block; margin-bottom: 4px;">To: ' . $recipientEmail . '</small>';
                  echo '      <small style="color: #94a3b8;">Subject: ' . $subject . '</small><br>';
                  echo '      <small style="color: #94a3b8;">Queued: ' . $createdTime . '</small>';
                  echo '    </div>';
                  echo '    <button class="button small secondary" onclick="deleteQueueItem(\'' . htmlspecialchars(addslashes($filename)) . '\')" style="white-space: nowrap;">Delete</button>';
                  echo '  </div>';
                  echo '</div>';
              }
          }
          echo '</div>';
      } else {
          echo '<div class="empty-state" style="padding: 40px 20px; text-align: center;">';
          echo '  <div style="font-size: 2rem; margin-bottom: 8px;">✓</div>';
          echo '  <strong>Queue Empty</strong>';
          echo '  <div style="color: #cbd5e1; margin-top: 4px;">All pending emails have been sent. System is up to date.</div>';
          echo '</div>';
      }
      ?>
    </div>
    
    <?php if ($queueCount > 0): ?>
    <div style="margin-top: 12px; display: flex; gap: 8px;">
      <button class="button secondary" onclick="processPendingQueue()" style="flex: 1;">⚡ Process Queue</button>
      <button class="button secondary" onclick="clearAllQueue()" style="flex: 1; background: #dc2626;">🗑️ Clear All</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Email Alerts Configuration -->
<div class="grid grid-1">
  <div class="card">
    <h3>🔔 Alert Configuration</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px;">
        <div style="font-weight: 600; margin-bottom: 8px;">Motion Alerts</div>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;">
          <span>Send on motion detection</span>
        </label>
      </div>
      
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px;">
        <div style="font-weight: 600; margin-bottom: 8px;">Person Detected</div>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;">
          <span>Send when person detected</span>
        </label>
      </div>
      
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px;">
        <div style="font-weight: 600; margin-bottom: 8px;">System Errors</div>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;">
          <span>Send error notifications</span>
        </label>
      </div>
      
      <div style="padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px;">
        <div style="font-weight: 600; margin-bottom: 8px;">Daily Digest</div>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" style="width: 18px; height: 18px; cursor: pointer;">
          <span>Send daily summary</span>
        </label>
      </div>
    </div>
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
  
  .empty-state {
    color: #cbd5e1;
    text-align: center;
  }
  
  @media (max-width: 768px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }
  }
</style>

<script>
  function testEmailFunction() {
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = '⏳ Testing...';
    
    fetch('test-email-system.php', { 
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ Email test successful!\n\nTest email sent to: ' + (data.email || 'admin'));
        } else {
          alert('✗ Email test failed:\n\n' + (data.message || 'Unknown error'));
        }
        location.reload();
      })
      .catch(error => {
        alert('Error testing email system:\n' + error.message);
        btn.disabled = false;
        btn.textContent = '🧪 Test Email System';
      });
  }

  function deleteQueueItem(filename) {
    if (confirm('Delete this queued email?\n\nFilename: ' + filename)) {
      fetch('delete-queue-item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ filename: filename })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ Queue item deleted');
          location.reload();
        } else {
          alert('✗ Error: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => console.error('Error:', error));
    }
  }

  function processPendingQueue() {
    if (confirm('Process all pending emails in queue?\n\nThis will attempt to send all waiting emails.')) {
      const btn = event.target;
      btn.disabled = true;
      btn.textContent = '⏳ Processing...';
      
      fetch('send-alert.php?action=process_queue', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
          alert('Queue processed:\n\n' + JSON.stringify(data, null, 2));
          location.reload();
        })
        .catch(error => {
          alert('Error: ' + error.message);
          btn.disabled = false;
          btn.textContent = '⚡ Process Queue';
        });
    }
  }

  function clearAllQueue() {
    if (confirm('Are you sure? This will delete ALL queued emails!\n\nThis action cannot be undone.')) {
      const btn = event.target;
      btn.disabled = true;
      btn.textContent = '⏳ Clearing...';
      
      fetch('delete-queue-item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'clear_all' })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ Queue cleared: ' + (data.deleted || 0) + ' emails deleted');
          location.reload();
        }
      })
      .catch(error => console.error('Error:', error));
    }
  }

  function viewEmailLogs() {
    window.open('activity-logs.php?filter=email', '_blank');
  }
</script>

<?php include 'includes/footer.php'; ?>


