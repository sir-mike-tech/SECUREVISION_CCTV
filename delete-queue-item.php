<?php
/**
 * Delete Queue Item
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['filename'])) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Filename required'));
        exit;
    }
    
    $filename = basename($data['filename']); // Prevent directory traversal
    $queueDir = __DIR__ . '/logs/email-queue';
    $filePath = $queueDir . '/' . $filename;
    
    // Verify file is in queue directory
    $realPath = realpath($filePath);
    $realQueueDir = realpath($queueDir);
    
    if ($realPath === false || strpos($realPath, $realQueueDir) !== 0) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Invalid file path'));
        exit;
    }
    
    if (file_exists($filePath)) {
        @unlink($filePath);
        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => 'Queue item deleted'));
    } else {
        http_response_code(404);
        echo json_encode(array('success' => false, 'error' => 'File not found'));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}
?>


