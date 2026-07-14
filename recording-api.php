<?php
/**
 * Recording API - Handle video recording storage and management
 * Supports WebM, MP4, and base64 encoded video streams
 * PHP 5.3+ Compatible
 */

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array('error' => 'Unauthorized'));
    exit;
}

// Get user role
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'viewer';

// Recording storage directory
$recordingsDir = __DIR__ . '/recordings/';
if (!is_dir($recordingsDir)) {
    mkdir($recordingsDir, 0755, true);
}

// Camera subdirectories
$camerasDir = $recordingsDir . 'cameras/';
if (!is_dir($camerasDir)) {
    mkdir($camerasDir, 0755, true);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function handleStartRecording() {
    global $recordingsDir, $camerasDir;
    
    $camera = isset($_POST['camera']) ? sanitizeInput($_POST['camera']) : 'unknown';
    $cameraDir = $camerasDir . $camera . '/';
    
    if (!is_dir($cameraDir)) {
        mkdir($cameraDir, 0755, true);
    }
    
    $sessionId = uniqid('rec_', true);
    $sessionFile = $cameraDir . $sessionId . '.json';
    
    $metadata = array(
        'session_id' => $sessionId,
        'camera' => $camera,
        'started_at' => date('Y-m-d H:i:s'),
        'started_timestamp' => time(),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'system',
        'duration' => 0,
        'file_size' => 0,
        'filename' => $sessionId . '.webm',
        'status' => 'recording',
        'chunks' => 0
    );
    
    if (file_put_contents($sessionFile, json_encode($metadata)) === false) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to create recording session'));
        exit;
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'session_id' => $sessionId));
}

function handleSaveRecording() {
    global $recordingsDir, $camerasDir;
    
    $sessionId = isset($_POST['session_id']) ? sanitizeInput($_POST['session_id']) : '';
    $camera = isset($_POST['camera']) ? sanitizeInput($_POST['camera']) : '';
    $chunkNumber = isset($_POST['chunk']) ? intval($_POST['chunk']) : 0;
    
    if (!$sessionId || !$camera) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing session_id or camera'));
        exit;
    }
    
    $cameraDir = $camerasDir . $camera . '/';
    $chunkFile = $cameraDir . $sessionId . '_chunk_' . $chunkNumber . '.webm';
    
    $chunkData = file_get_contents('php://input');
    if ($chunkData === false || strlen($chunkData) === 0) {
        http_response_code(400);
        echo json_encode(array('error' => 'No chunk data provided'));
        exit;
    }
    
    if (file_put_contents($chunkFile, $chunkData) === false) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to save chunk'));
        exit;
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'chunk' => $chunkNumber, 'size' => strlen($chunkData)));
}

function handleFinalizeRecording() {
    global $recordingsDir, $camerasDir;
    
    $sessionId = isset($_POST['session_id']) ? sanitizeInput($_POST['session_id']) : '';
    $camera = isset($_POST['camera']) ? sanitizeInput($_POST['camera']) : '';
    $duration = isset($_POST['duration']) ? floatval($_POST['duration']) : 0;
    
    if (!$sessionId || !$camera) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing session_id or camera'));
        exit;
    }
    
    $cameraDir = $camerasDir . $camera . '/';
    $sessionFile = $cameraDir . $sessionId . '.json';
    $outputFile = $cameraDir . $sessionId . '.webm';
    
    if (!file_exists($sessionFile)) {
        http_response_code(404);
        echo json_encode(array('error' => 'Recording session not found'));
        exit;
    }
    
    // Combine all chunks
    $output = fopen($outputFile, 'wb');
    if ($output === false) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to create final video file'));
        exit;
    }
    
    $fileSize = 0;
    $chunkNum = 0;
    while (true) {
        $chunkFile = $cameraDir . $sessionId . '_chunk_' . $chunkNum . '.webm';
        if (!file_exists($chunkFile)) {
            break;
        }
        $chunkData = file_get_contents($chunkFile);
        if ($chunkData !== false) {
            fwrite($output, $chunkData);
            $fileSize += strlen($chunkData);
            @unlink($chunkFile);
        }
        $chunkNum++;
    }
    
    fclose($output);
    
    // Update metadata
    $metadata = json_decode(file_get_contents($sessionFile), true);
    if (is_array($metadata)) {
        $metadata['status'] = 'completed';
        $metadata['duration'] = $duration;
        $metadata['file_size'] = $fileSize;
        $metadata['completed_at'] = date('Y-m-d H:i:s');
        $metadata['chunks'] = $chunkNum;
        // Try to convert to MP4 for wider compatibility if ffmpeg is available
        $converted = false;
        $webmPath = $outputFile;
        $mp4Path = $cameraDir . $sessionId . '.mp4';
        
        // Check for ffmpeg availability
        $ffmpegAvailable = false;
        @exec('ffmpeg -version 2>&1', $ffmpegOut, $ffmpegRc);
        if (isset($ffmpegRc) && $ffmpegRc === 0) {
            $ffmpegAvailable = true;
        }

        if ($ffmpegAvailable) {
            // Build safe command
            $in = escapeshellarg($webmPath);
            $out = escapeshellarg($mp4Path);
            // Basic conversion: copy codecs where possible, otherwise re-encode to h264/aac
            $cmd = "ffmpeg -y -i $in -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k $out 2>&1";
            @exec($cmd, $ffout, $rc);
            if ($rc === 0 && file_exists($mp4Path)) {
                $converted = true;
                // Prefer MP4 file as primary filename
                $metadata['filename'] = $sessionId . '.mp4';
                $metadata['converted_mp4'] = true;
                // Optionally keep the original webm for archival; uncomment to remove
                // @unlink($webmPath);
            } else {
                $metadata['filename'] = $sessionId . '.webm';
                $metadata['converted_mp4'] = false;
                // conversion failed - leave webm
            }
        } else {
            $metadata['filename'] = $sessionId . '.webm';
            $metadata['converted_mp4'] = false;
        }

        file_put_contents($sessionFile, json_encode($metadata));
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'file' => $sessionId . '.webm', 'size' => $fileSize));
}

function handleListRecordings() {
    global $camerasDir;
    
    $camera = isset($_GET['camera']) ? sanitizeInput($_GET['camera']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $recordings = array();
    
    if ($camera && is_dir($camerasDir . $camera)) {
        $cameraDir = $camerasDir . $camera . '/';
        $files = glob($cameraDir . '*.json');
        
        if ($files) {
            foreach ($files as $file) {
                $metadata = json_decode(file_get_contents($file), true);
                if (is_array($metadata)) {
                    $recordings[] = array(
                        'session_id' => isset($metadata['session_id']) ? $metadata['session_id'] : '',
                        'camera' => isset($metadata['camera']) ? $metadata['camera'] : '',
                        'started_at' => isset($metadata['started_at']) ? $metadata['started_at'] : '',
                        'duration' => isset($metadata['duration']) ? $metadata['duration'] : 0,
                        'file_size' => isset($metadata['file_size']) ? $metadata['file_size'] : 0,
                        'filename' => isset($metadata['filename']) ? $metadata['filename'] : '',
                        'user_id' => isset($metadata['user_id']) ? $metadata['user_id'] : 'unknown',
                        'status' => isset($metadata['status']) ? $metadata['status'] : 'unknown'
                    );
                }
            }
        }
    }
    
    // Apply pagination
    $total = count($recordings);
    $recordings = array_slice($recordings, $offset, $limit);
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'total' => $total, 'offset' => $offset, 'limit' => $limit, 'recordings' => $recordings));
}

function handleDeleteRecording() {
    global $camerasDir;
    
    if ($userRole !== 'admin' && $userRole !== 'administrator') {
        http_response_code(403);
        echo json_encode(array('error' => 'Permission denied'));
        exit;
    }
    
    $sessionId = isset($_POST['session_id']) ? sanitizeInput($_POST['session_id']) : '';
    $camera = isset($_POST['camera']) ? sanitizeInput($_POST['camera']) : '';
    
    if (!$sessionId || !$camera) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing session_id or camera'));
        exit;
    }
    
    $cameraDir = $camerasDir . $camera . '/';
    // Prefer MP4 if available for broader player compatibility
    $mp4File = $cameraDir . $sessionId . '.mp4';
    $webmFile = $cameraDir . $sessionId . '.webm';
    $videoFile = file_exists($mp4File) ? $mp4File : $webmFile;
    $metaFile = $cameraDir . $sessionId . '.json';
    
    $deleted = 0;
    if (file_exists($videoFile) && @unlink($videoFile)) {
        $deleted++;
    }
    if (file_exists($metaFile) && @unlink($metaFile)) {
        $deleted++;
    }
    
    if ($deleted > 0) {
        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => 'Recording deleted'));
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Recording not found'));
    }
}

function handleDownloadRecording() {
    global $camerasDir;
    
    $sessionId = isset($_GET['session_id']) ? sanitizeInput($_GET['session_id']) : '';
    $camera = isset($_GET['camera']) ? sanitizeInput($_GET['camera']) : '';
    
    if (!$sessionId || !$camera) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing session_id or camera'));
        exit;
    }
    
    $cameraDir = $camerasDir . $camera . '/';
    $videoFile = $cameraDir . $sessionId . '.webm';
    
    if (!file_exists($videoFile)) {
        http_response_code(404);
        echo json_encode(array('error' => 'Recording not found'));
        exit;
    }
    
    // Determine MIME type by extension
    $ext = strtolower(pathinfo($videoFile, PATHINFO_EXTENSION));
    $mime = ($ext === 'mp4') ? 'video/mp4' : 'video/webm';

    // If client requests inline playback, send inline, otherwise attachment
    $disposition = (isset($_GET['inline']) && $_GET['inline']) ? 'inline' : 'attachment';

    header('Content-Type: ' . $mime);
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($videoFile) . '"');
    header('Content-Length: ' . filesize($videoFile));
    // Enable range requests for video players
    if (function_exists('apache_setenv')) @apache_setenv('no-gzip', '1');
    if (ob_get_level()) { ob_end_clean(); }
    readfile($videoFile);
    exit;
}

function handleGetInfo() {
    global $camerasDir;
    
    $sessionId = isset($_GET['session_id']) ? sanitizeInput($_GET['session_id']) : '';
    $camera = isset($_GET['camera']) ? sanitizeInput($_GET['camera']) : '';
    
    if (!$sessionId || !$camera) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing session_id or camera'));
        exit;
    }
    
    $cameraDir = $camerasDir . $camera . '/';
    $metaFile = $cameraDir . $sessionId . '.json';
    
    if (!file_exists($metaFile)) {
        http_response_code(404);
        echo json_encode(array('error' => 'Recording not found'));
        exit;
    }
    
    $metadata = json_decode(file_get_contents($metaFile), true);
    http_response_code(200);
    echo json_encode($metadata);
}

function handleCleanup() {
    global $camerasDir;
    
    if ($userRole !== 'admin' && $userRole !== 'administrator') {
        http_response_code(403);
        echo json_encode(array('error' => 'Permission denied'));
        exit;
    }
    
    $retentionDays = isset($_POST['retention_days']) ? intval($_POST['retention_days']) : 30;
    $maxSize = isset($_POST['max_size_mb']) ? intval($_POST['max_size_mb']) : 5000;
    
    $cutoffTime = time() - ($retentionDays * 86400);
    $deleted = 0;
    $totalFreed = 0;
    
    $cameras = glob($camerasDir . '*', GLOB_ONLYDIR);
    foreach ($cameras as $cameraDir) {
        $files = glob($cameraDir . '/*.json');
        foreach ($files as $file) {
            $metadata = json_decode(file_get_contents($file), true);
            if (is_array($metadata) && isset($metadata['started_timestamp'])) {
                if ($metadata['started_timestamp'] < $cutoffTime) {
                    $sessionId = isset($metadata['session_id']) ? $metadata['session_id'] : '';
                    $videoFile = dirname($file) . '/' . $sessionId . '.webm';
                    
                    if (file_exists($videoFile)) {
                        $size = filesize($videoFile);
                        @unlink($videoFile);
                        $totalFreed += $size;
                    }
                    @unlink($file);
                    $deleted++;
                }
            }
        }
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'deleted' => $deleted, 'freed_mb' => round($totalFreed / 1048576, 2)));
}

// Route to appropriate handler
switch ($action) {
    case 'start':
        handleStartRecording();
        break;
    case 'save':
        handleSaveRecording();
        break;
    case 'finalize':
        handleFinalizeRecording();
        break;
    case 'list':
        handleListRecordings();
        break;
    case 'delete':
        handleDeleteRecording();
        break;
    case 'download':
        handleDownloadRecording();
        break;
    case 'info':
        handleGetInfo();
        break;
    case 'cleanup':
        handleCleanup();
        break;
    default:
        http_response_code(400);
        echo json_encode(array('error' => 'Unknown action: ' . htmlspecialchars($action)));
}
?>
