<?php
/**
 * SecureVision CCTV - Motion Detection Snapshot API
 * Handles saving captured motion detection snapshots
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$snapshotsDir = __DIR__ . '/logs/motion-snapshots';
$logsDir = __DIR__ . '/logs';

// Ensure directories exist
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}

if (!is_dir($snapshotsDir)) {
    @mkdir($snapshotsDir, 0755, true);
}

@chmod($snapshotsDir, 0755);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : null;

try {
    if ($method === 'POST' && $action === 'save') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Invalid JSON input'));
            exit;
        }

        $imageData = isset($input['image']) ? $input['image'] : null;
        $camera = sanitize_input(isset($input['camera']) ? $input['camera'] : 'Unknown');
        $motionType = sanitize_input(isset($input['type']) ? $input['type'] : 'motion');
        $confidence = isset($input['confidence']) ? floatval($input['confidence']) : 0;

        if (!$imageData || strpos($imageData, 'data:image/') !== 0) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Invalid image data'));
            exit;
        }

        // Extract base64 data
        $parts = explode(',', $imageData);
        if (count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Invalid image format'));
            exit;
        }

        $base64 = $parts[1];
        $binaryData = base64_decode($base64, true);

        if ($binaryData === false) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Failed to decode image'));
            exit;
        }

        // Generate filename with timestamp
        $timestamp = microtime(true);
        $dateFolder = date('Y-m-d');
        $cameraSafe = preg_replace('/[^a-z0-9-]/i', '_', $camera);
        $dateDir = $snapshotsDir . '/' . $dateFolder;

        if (!is_dir($dateDir)) {
            @mkdir($dateDir, 0755, true);
        }

        $filename = $dateDir . '/' . $cameraSafe . '_' . str_replace('.', '-', $timestamp) . '.png';

        if (@file_put_contents($filename, $binaryData) === false) {
            http_response_code(500);
            echo json_encode(array('success' => false, 'error' => 'Failed to save snapshot'));
            exit;
        }

        // Log motion event
        $logFile = __DIR__ . '/logs/motion-events.log';
        $logEntry = json_encode(array(
            'timestamp' => date('c'),
            'camera' => $camera,
            'type' => $motionType,
            'confidence' => $confidence,
            'file' => basename($filename)
        )) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);

        echo json_encode(array(
            'success' => true,
            'message' => 'Snapshot saved',
            'file' => basename($filename),
            'camera' => $camera,
            'type' => $motionType,
            'confidence' => round($confidence, 3)
        ));
        exit;
    }
    elseif ($method === 'GET' && $action === 'list') {
        $camera = sanitize_input(isset($_GET['camera']) ? $_GET['camera'] : '');
        $date = sanitize_input(isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
        $limit = intval(isset($_GET['limit']) ? $_GET['limit'] : 50);

        $dateDir = $snapshotsDir . '/' . $date;
        $files = array();

        if (is_dir($dateDir)) {
            $items = @scandir($dateDir);
            if ($items === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Unable to read directory']);
                exit;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || is_dir($dateDir . '/' . $item)) continue;

                if ($camera && strpos($item, $camera) === false) continue;

                $files[] = array(
                    'filename' => $item,
                    'path' => 'logs/motion-snapshots/' . $date . '/' . $item,
                    'size' => filesize($dateDir . '/' . $item),
                    'date' => date('Y-m-d H:i:s', filemtime($dateDir . '/' . $item))
                );
            }
        }

        // Sort by date descending
        usort($files, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        $files = array_slice($files, 0, $limit);

        echo json_encode(array(
            'success' => true,
            'date' => $date,
            'camera' => $camera,
            'count' => count($files),
            'data' => $files
        ));
        exit;
    }
    elseif ($method === 'GET' && $action === 'delete') {
        $file = sanitize_input(isset($_GET['file']) ? $_GET['file'] : '');
        $date = sanitize_input(isset($_GET['date']) ? $_GET['date'] : '');

        if (!$file || !$date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing file or date']);
            exit;
        }

        $path = $snapshotsDir . '/' . $date . '/' . basename($file);

        if (file_exists($path) && @unlink($path)) {
            echo json_encode(array('success' => true, 'message' => 'Snapshot deleted'));
        } else {
            http_response_code(500);
            echo json_encode(array('success' => false, 'error' => 'Failed to delete snapshot'));
        }
        exit;
    }
    else {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Invalid action'));
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Server error: ' . $e->getMessage()));
    exit;
}

function sanitize_input($input) {
    $val = isset($input) ? $input : '';
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}
?>


