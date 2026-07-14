<?php
/**
 * Clear Alert Logs
 */

header('Content-Type: application/json');

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

$logDir = __DIR__ . '/logs';

if (!is_dir($logDir)) {
    http_response_code(200);
    echo json_encode(array('success' => true, 'message' => 'No logs to clear'));
    exit;
}

try {
    $alertLogFile = $logDir . '/alerts.log';
    $emailLogFile = $logDir . '/email-notifications.log';
    
    // Clear alert log
    if (file_exists($alertLogFile)) {
        file_put_contents($alertLogFile, '');
    }
    
    // Clear email log
    if (file_exists($emailLogFile)) {
        file_put_contents($emailLogFile, '');
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'message' => 'Alert logs cleared successfully'));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}
?>


