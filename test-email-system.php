<?php
/**
 * Test Email System
 * Tests if the email system is working properly
 */

header('Content-Type: application/json');

try {
    // Ensure logs directory exists
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $testResults = array(
        'timestamp' => date('Y-m-d H:i:s'),
        'mail_function' => function_exists('mail') ? 'available' : 'not_available',
        'logs_dir_exists' => is_dir($logDir),
        'logs_dir_writable' => is_writable($logDir),
        'queue_dir_exists' => is_dir($logDir . '/email-queue'),
        'queue_dir_writable' => is_writable($logDir . '/email-queue'),
        'test_email_sent' => false,
        'test_email_queued' => false,
        'message' => ''
    ];
    
    // Get test email address
    $testEmail = 'sirmike6072@gmail.com';
    
    // Try to send test email
    $subject = "Test Email - SecureVision CCTV System Test";
    $body = buildTestEmailBody();
    
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: test@" . ($_SERVER['SERVER_NAME'] ?? 'securevision'),
        "X-Mailer: SecureVision-Test/1.0"
    ];
    
    // Try direct send
    if (function_exists('mail')) {
        $sent = @mail($testEmail, $subject, $body, implode("\r\n", $headers));
        $testResults['test_email_sent'] = $sent;
        $testResults['message'] .= $sent ? "✓ Direct email sent via mail(). " : "✗ Direct email send failed. ";
    }
    
    // Try queue
    $queueDir = $logDir . '/email-queue';
    if (!is_dir($queueDir)) {
        @mkdir($queueDir, 0755, true);
    }
    
    $queueFile = $queueDir . '/test_' . time() . '.json';
    $queueData = [
        'type' => 'SYSTEM_TEST',
        'email' => $testEmail,
        'subject' => $subject,
        'created' => date('Y-m-d H:i:s'),
        'is_test' => true
    ];
    
    $written = file_put_contents($queueFile, json_encode($queueData, JSON_PRETTY_PRINT));
    $testResults['test_email_queued'] = (bool)$written;
    $testResults['message'] .= $written ? "✓ Test email queued successfully." : "✗ Failed to queue email.";
    
    http_response_code(200);
    echo json_encode($testResults);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function buildTestEmailBody() {
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #07111f, #0f1d33); color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .success-box { background: rgba(34, 197, 85, 0.1); border-left: 5px solid #22c55e; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Email - System Working</h1>
            <p>SecureVision CCTV Notification Test</p>
        </div>
        <div class="content">
            <div class="success-box">
                <h2>✓ Email System Test Successful</h2>
                <p>Your SecureVision CCTV email notification system is working correctly!</p>
                <p>You will receive alert notifications when thresholds are exceeded.</p>
                <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
}
?>


