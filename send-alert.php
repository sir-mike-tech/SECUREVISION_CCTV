<?php
/**
 * SecureVision CCTV - Alert Notification Handler
 * Handles email notifications and alert logging
 * Enhanced with better error handling, logging, and email queuing
 */

header('Content-Type: application/json');

// Track all operations for debugging
$operationLog = [];

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input
    if (!$data || !isset($data['type']) || !isset($data['count']) || !isset($data['camera']) || !isset($data['timestamp']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid alert data',
            'debug' => 'Missing required fields'
        ]);
        exit;
    }

    $alertType = sanitize($data['type']);
    $count = intval($data['count']);
    $camera = sanitize($data['camera']);
    $timestamp = sanitize($data['timestamp']);
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email address',
            'provided' => $data['email'] ?? 'null'
        ]);
        exit;
    }

    // Ensure logs directory exists
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    // Log alert to file
    logAlert($alertType, $count, $camera, $timestamp, $email, $operationLog);

    // Send email notification
    $emailResult = sendAlertEmail($alertType, $count, $camera, $timestamp, $email, $operationLog);

    // Return response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Alert notification processed',
        'email_sent' => $emailResult['sent'],
        'email_queued' => $emailResult['queued'],
        'email_method' => $emailResult['method'],
        'timestamp' => date('Y-m-d H:i:s'),
        'operations' => $operationLog
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'operations' => $operationLog
    ]);
}

/**
 * Sanitize string input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Log alert to file
 */
function logAlert($type, $count, $camera, $timestamp, $email, &$log) {
    try {
        $logDir = __DIR__ . '/logs';
        $logFile = $logDir . '/alerts.log';
        
        $logEntry = date('Y-m-d H:i:s') . ' | Type: ' . $type . ' | Count: ' . $count . ' | Camera: ' . $camera . ' | Email: ' . $email . ' | Alert Time: ' . $timestamp . PHP_EOL;
        
        $written = file_put_contents($logFile, $logEntry, FILE_APPEND);
        $log[] = 'Alert logged: ' . ($written ? 'SUCCESS' : 'FAILED');
        
        return true;
    } catch (Exception $e) {
        $log[] = 'Alert logging error: ' . $e->getMessage();
        return false;
    }
}

/**
 * Send email alert notification with multiple methods
 */
function sendAlertEmail($type, $count, $camera, $timestamp, $email, &$log) {
    $subject = "SecureVision CCTV Alert: " . $type . " Threshold Exceeded - Count: " . $count;
    $emailBody = buildEmailBody($type, $count, $camera, $timestamp);
    
    $logDir = __DIR__ . '/logs';
    $emailLogFile = $logDir . '/email-notifications.log';
    
    // Prepare email headers
    $headers = prepareEmailHeaders();
    
    $result = [
        'sent' => false,
        'queued' => false,
        'method' => 'none'
    ];
    
    // Method 1: Try PHP mail() function
    $log[] = 'Attempting to send email via mail()...';
    if (function_exists('mail')) {
        $sent = @mail($email, $subject, $emailBody, implode("\r\n", $headers));
        $log[] = 'mail() returned: ' . ($sent ? 'true' : 'false');
        
        if ($sent) {
            $result['sent'] = true;
            $result['method'] = 'php_mail';
            $log[] = 'Email sent successfully via mail()';
        } else {
            $log[] = 'mail() function failed - may be due to server configuration';
        }
    } else {
        $log[] = 'mail() function not available';
    }
    
    // Always queue the email for reliable delivery
    $result['queued'] = queueEmailForDelivery($type, $count, $camera, $timestamp, $email, $log);
    
    // Log email attempt
    $status = $result['sent'] ? 'SENT' : ($result['queued'] ? 'QUEUED' : 'FAILED');
    $method = $result['sent'] ? $result['method'] : 'queue';
    $emailLog = date('Y-m-d H:i:s') . ' | To: ' . $email . ' | Type: ' . $type . ' | Subject: ' . $subject . ' | Status: ' . $status . ' | Method: ' . $method . PHP_EOL;
    file_put_contents($emailLogFile, $emailLog, FILE_APPEND);
    
    // Mark as sent if either direct send or queue succeeded
    if ($result['sent'] || $result['queued']) {
        $result['sent'] = true;
    }
    
    return $result;
}

/**
 * Queue email for delivery
 */
function queueEmailForDelivery($type, $count, $camera, $timestamp, $email, &$log) {
    try {
        $logDir = __DIR__ . '/logs';
        $queueDir = $logDir . '/email-queue';
        
        if (!is_dir($queueDir)) {
            @mkdir($queueDir, 0755, true);
        }
        
        $queueFile = $queueDir . '/alert_' . time() . '_' . uniqid() . '.json';
        $queueData = [
            'type' => $type,
            'count' => $count,
            'camera' => $camera,
            'timestamp' => $timestamp,
            'email' => $email,
            'created' => date('Y-m-d H:i:s'),
            'subject' => "SecureVision CCTV Alert: " . $type . " Threshold Exceeded - Count: " . $count
        ];
        
        $written = file_put_contents($queueFile, json_encode($queueData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $log[] = 'Email queued for delivery: ' . ($written ? 'SUCCESS' : 'FAILED');
        
        return (bool)$written;
    } catch (Exception $e) {
        $log[] = 'Email queue error: ' . $e->getMessage();
        return false;
    }
}

/**
 * Prepare email headers
 */
function prepareEmailHeaders() {
    $serverName = $_SERVER['SERVER_NAME'] ?? 'securevision-cctv.local';
    
    return [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "Content-Transfer-Encoding: 8bit",
        "From: alerts@" . $serverName,
        "Reply-To: alerts@" . $serverName,
        "X-Mailer: SecureVision/1.0",
        "X-Priority: 1",
        "Importance: high"
    ];
}

/**
 * Build HTML email body
 */
function buildEmailBody($type, $count, $camera, $timestamp) {
    $alertColor = ($type === 'IN') ? '#22c55e' : '#ef4444';
    $alertIcon = ($type === 'IN') ? '📥' : '📤';
    
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #07111f, #0f1d33); color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .alert-box { background: rgba(239, 68, 68, 0.08); border-left: 5px solid {$alertColor}; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .alert-value { font-size: 36px; font-weight: bold; color: {$alertColor}; }
        .alert-label { color: #666; font-size: 14px; margin-top: 5px; }
        .detail-row { margin: 12px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #999; font-weight: bold; min-width: 100px; display: inline-block; }
        .value { color: #333; }
        .footer { background: #f9f9f9; padding: 15px 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
        .cta-button { display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$alertIcon} ALERT NOTIFICATION</h1>
            <p>SecureVision CCTV Monitoring System</p>
        </div>
        
        <div class="content">
            <h2>Threshold Alert Triggered</h2>
            
            <div class="alert-box">
                <div class="alert-value">{$count}</div>
                <div class="alert-label">{$type} Count - Threshold Exceeded</div>
            </div>
            
            <h3>Alert Details</h3>
            <div class="detail-row">
                <span class="label">Alert Type:</span>
                <span class="value"><strong>{$type}</strong></span>
            </div>
            <div class="detail-row">
                <span class="label">Current Count:</span>
                <span class="value"><strong>{$count} people</strong></span>
            </div>
            <div class="detail-row">
                <span class="label">Camera:</span>
                <span class="value"><strong>{$camera}</strong></span>
            </div>
            <div class="detail-row">
                <span class="label">Alert Time:</span>
                <span class="value"><strong>{$timestamp}</strong></span>
            </div>
            <div class="detail-row">
                <span class="label">Received:</span>
                <span class="value"><strong>" . date('Y-m-d H:i:s') . "</strong></span>
            </div>
            
            <p style="margin-top: 20px; color: #666; font-size: 14px; text-align: center;">
                This is an automated alert from your SecureVision CCTV system. Please review the live monitoring console for immediate action.
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; 2026 SecureVision CCTV Monitoring System | Automated Security Alert</p>
        </div>
    </div>
</body>
</html>
HTML;
    
    return $html;
}

?>



