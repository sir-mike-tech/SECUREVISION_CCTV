<?php
/**
 * SecureVision CCTV - Camera Connection Test
 * Tests camera connectivity for RTSP, HTTP, and HTTPS streams
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input'));
    exit;
}

$ip = isset($input['ip']) ? trim($input['ip']) : null;

if (!$ip) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No IP/URL provided'));
    exit;
}

$result = testCameraConnection($ip);
echo json_encode($result);
exit;

function testCameraConnection($url) {
    try {
        $protocol = strtolower(substr($url, 0, strpos($url, '://')));
        
        if ($protocol === false) {
            return [
                'success' => false,
                'error' => 'Invalid URL format (no protocol)',
                'code' => 'INVALID_URL'
            ];
        }
        
        // Handle RTSP streams
        if ($protocol === 'rtsp') {
            return testRTSP($url);
        }
        
        // Handle HTTP/HTTPS
        if (in_array($protocol, ['http', 'https'])) {
            return testHTTP($url);
        }
        
        // Unknown protocol
        return [
            'success' => false,
            'error' => 'Unknown protocol: ' . $protocol,
            'code' => 'UNKNOWN_PROTOCOL'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Test error: ' . $e->getMessage(),
            'code' => 'TEST_EXCEPTION'
        ];
    }
}

function testHTTP($url) {
    $start = microtime(true);
    
    try {
        if (!extension_loaded('curl')) {
            $elapsed = round((microtime(true) - $start) * 1000, 2);
            return [
                'success' => false,
                'error' => 'cURL extension not available',
                'code' => 'NO_CURL',
                'elapsed' => $elapsed
            ];
        }

        $ch = curl_init();
        if ($ch === false) {
            $elapsed = round((microtime(true) - $start) * 1000, 2);
            return [
                'success' => false,
                'error' => 'Failed to initialize cURL',
                'code' => 'CURL_INIT_ERROR',
                'elapsed' => $elapsed
            ];
        }

        @curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        @curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        @curl_setopt($ch, CURLOPT_USERAGENT, 'SecureVision CCTV Monitor');

        $result = @curl_exec($ch);
        $httpCode = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = @curl_error($ch);
        @curl_close($ch);

        $elapsed = round((microtime(true) - $start) * 1000, 2);

        if ($error && strpos($error, 'Couldn\'t resolve host') !== false) {
            return [
                'success' => false,
                'error' => 'Camera not reachable (DNS resolution failed)',
                'code' => 'UNREACHABLE',
                'elapsed' => $elapsed
            ];
        }

        if ($error && strpos($error, 'Failed to connect') !== false) {
            return [
                'success' => false,
                'error' => 'Camera not reachable (connection refused)',
                'code' => 'CONNECTION_REFUSED',
                'elapsed' => $elapsed
            ];
        }

        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error,
                'code' => 'CONNECTION_ERROR',
                'elapsed' => $elapsed
            ];
        }

        // Check HTTP status code
        if ($httpCode >= 200 && $httpCode < 400) {
            return [
                'success' => true,
                'message' => 'Camera reachable (HTTP ' . $httpCode . ')',
                'code' => 'REACHABLE',
                'httpCode' => $httpCode,
                'elapsed' => $elapsed,
                'protocol' => 'HTTP/HTTPS'
            ];
        } 
        elseif ($httpCode === 401 || $httpCode === 403) {
            return [
                'success' => true,
                'message' => 'Camera reachable (requires authentication)',
                'code' => 'REACHABLE_AUTH_REQUIRED',
                'httpCode' => $httpCode,
                'elapsed' => $elapsed,
                'protocol' => 'HTTP/HTTPS'
            ];
        }
        else {
            return [
                'success' => true,
                'message' => 'Camera responded (HTTP ' . $httpCode . ')',
                'code' => 'RESPONDED',
                'httpCode' => $httpCode,
                'elapsed' => $elapsed,
                'protocol' => 'HTTP/HTTPS'
            ];
        }
    } catch (Exception $e) {
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        return [
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage(),
            'code' => 'EXCEPTION',
            'elapsed' => $elapsed
        ];
    }
}

function testRTSP($url) {
    $start = microtime(true);
    
    // Try using ffprobe if available
    if (commandExists('ffprobe')) {
        return testRTSPWithFFProbe($url);
    }
    
    // Fallback: basic socket check
    return testRTSPWithSocket($url);
}

function testRTSPWithFFProbe($url) {
    $start = microtime(true);
    $cmd = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1:noesc=1 "' . escapeshellarg($url) . '" 2>&1';
    
    exec($cmd, $output, $returnCode);
    $elapsed = round((microtime(true) - $start) * 1000, 2);
    
    if ($returnCode === 0) {
        return [
            'success' => true,
            'message' => 'RTSP stream reachable',
            'code' => 'RTSP_OK',
            'elapsed' => $elapsed,
            'protocol' => 'RTSP (FFProbe)'
        ];
    } else {
        return [
            'success' => false,
            'error' => 'RTSP stream not accessible',
            'code' => 'RTSP_FAILED',
            'elapsed' => $elapsed,
            'protocol' => 'RTSP (FFProbe)',
            'details' => implode(' ', $output)
        ];
    }
}

function testRTSPWithSocket($url) {
    $start = microtime(true);
    
    // Parse RTSP URL
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? null;
    $port = $parsed['port'] ?? 554;
    
    if (!$host) {
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        return [
            'success' => false,
            'error' => 'Invalid RTSP URL',
            'code' => 'INVALID_URL',
            'elapsed' => $elapsed
        ];
    }
    
    try {
        $socket = @fsockopen($host, $port, $errno, $errstr, 3);
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        
        if ($socket) {
            fclose($socket);
            return [
                'success' => true,
                'message' => 'RTSP port accessible (server responds)',
                'code' => 'RTSP_PORT_OPEN',
                'elapsed' => $elapsed,
                'protocol' => 'RTSP (Socket)'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'RTSP port unreachable: ' . $errstr,
                'code' => 'RTSP_PORT_CLOSED',
                'elapsed' => $elapsed,
                'protocol' => 'RTSP (Socket)'
            ];
        }
    } catch (Exception $e) {
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        return [
            'success' => false,
            'error' => 'Connection error: ' . $e->getMessage(),
            'code' => 'RTSP_ERROR',
            'elapsed' => $elapsed
        ];
    }
}

function commandExists($cmd) {
    $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
    return !empty($return);
}
?>


