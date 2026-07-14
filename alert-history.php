<?php include 'includes/header.php'; ?>

<div class="grid grid-1">
  <div class="card">
    <h3>📋 Alert History Viewer</h3>
    <p class="muted">View all triggered alerts from the monitoring system</p>
  </div>
</div>

<div class="grid grid-2">
  <div class="card">
    <h3>Alert Log</h3>
    <div class="alert-viewer-container">
      <?php
        $logFile = __DIR__ . '/logs/alerts.log';
        
        if (file_exists($logFile)) {
          $alerts = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
          
          if (count($alerts) > 0) {
            echo '<div class="alert-log-list">';
            foreach ($alerts as $alert) {
              // Parse log entry
              $parts = explode(' | ', $alert);
              $type = isset($parts[1]) ? trim(str_replace('Type: ', '', $parts[1])) : 'Unknown';
              
              $alertClass = (strpos($type, 'IN') !== false) ? 'alert-in' : 'alert-out';
              
              echo '<div class="alert-log-item ' . htmlspecialchars($alertClass) . '">';
              echo '<small style="color: var(--muted);">' . htmlspecialchars($alert) . '</small>';
              echo '</div>';
            }
            echo '</div>';
          } else {
            echo '<div class="empty-state">No alerts have been triggered yet.</div>';
          }
        } else {
          echo '<div class="empty-state">No alert log file found. Alerts will appear here once triggered.</div>';
        }
      ?>
    </div>
    <button class="button secondary" onclick="clearAlertLog()" style="width: 100%; margin-top: 12px;">🗑️ Clear Log</button>
  </div>
  
  <div class="card">
    <h3>Email Notifications</h3>
    <div class="email-log-container">
      <?php
        $emailLogFile = __DIR__ . '/logs/email-notifications.log';
        
        if (file_exists($emailLogFile)) {
          $emails = array_reverse(file($emailLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
          
          if (count($emails) > 0) {
            echo '<div class="email-log-list">';
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($emails as $email) {
              if (strpos($email, 'SENT') !== false) {
                $successCount++;
                $statusClass = 'success';
                $statusIcon = '✓';
              } else {
                $failedCount++;
                $statusClass = 'failed';
                $statusIcon = '✗';
              }
              
              echo '<div class="email-log-item email-' . htmlspecialchars($statusClass) . '">';
              echo '<span class="email-status-icon">' . $statusIcon . '</span>';
              echo '<small style="color: #cbd5e1;">' . htmlspecialchars($email) . '</small>';
              echo '</div>';
            }
            echo '</div>';
            
            echo '<div class="email-stats" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.08);">';
            echo '<div style="display: flex; gap: 20px; font-size: 0.9rem;">';
            echo '<div><span style="color: #86efac; font-weight: bold;">' . $successCount . '</span> <span class="muted">Sent</span></div>';
            echo '<div><span style="color: #fda4af; font-weight: bold;">' . $failedCount . '</span> <span class="muted">Failed</span></div>';
            echo '</div>';
            echo '</div>';
          } else {
            echo '<div class="empty-state">No email notifications sent yet.</div>';
          }
        } else {
          echo '<div class="empty-state">Email notification log will appear here.</div>';
        }
      ?>
    </div>
  </div>
</div>

<script>
  function clearAlertLog() {
    if (confirm('Clear all alert logs? This cannot be undone.')) {
      fetch('clear-alert-log.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Alert log cleared successfully');
            location.reload();
          } else {
            alert('Error clearing log: ' + data.error);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error clearing log');
        });
    }
  }
</script>

<style>
  .alert-log-list, .email-log-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 450px;
    overflow-y: auto;
    padding: 8px 0;
  }
  
  .alert-log-item, .email-log-item {
    padding: 10px 12px;
    border-radius: 8px;
    background: rgba(59, 130, 246, 0.08);
    border-left: 3px solid #3b82f6;
    display: flex;
    align-items: center;
    gap: 8px;
    word-break: break-word;
  }
  
  .alert-log-item.alert-in {
    background: rgba(34, 197, 85, 0.08);
    border-left-color: #86efac;
  }
  
  .alert-log-item.alert-out {
    background: rgba(239, 68, 68, 0.08);
    border-left-color: #fda4af;
  }
  
  .email-log-item {
    border-left: 3px solid #3b82f6;
  }
  
  .email-log-item.email-success {
    background: rgba(34, 197, 85, 0.08);
    border-left-color: #86efac;
  }
  
  .email-log-item.email-failed {
    background: rgba(239, 68, 68, 0.08);
    border-left-color: #fda4af;
  }
  
  .email-status-icon {
    font-weight: bold;
    min-width: 20px;
    text-align: center;
  }
  
  .email-log-item.email-success .email-status-icon {
    color: #86efac;
  }
  
  .email-log-item.email-failed .email-status-icon {
    color: #fda4af;
  }
  
  .email-stats {
    font-size: 0.9rem;
  }
  
  .grid-1 {
    grid-template-columns: 1fr;
  }
</style>

<?php include 'includes/footer.php'; ?>


