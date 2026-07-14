<?php
/**
 * SecureVision CCTV - Camera Management API
 * Handles CRUD operations for cameras with persistent JSON storage
 * PHP 5.3+ Compatible
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

function sanitize_input($input) {
    $value = isset($input) ? $input : '';
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function getDefaultCameras() {
    return array(
        array(
            'id' => 1,
            'name' => 'Lobby North',
            'location' => 'Main Entrance',
            'ip' => 'http://192.168.1.50',
            'model' => 'Hikvision',
            'serial' => 'DS2CD3123456',
            'group' => 'Main',
            'status' => 'Enabled',
            'schedule' => '24/7',
            'lastTested' => null,
            'testStatus' => 'unknown'
        ),
        array(
            'id' => 2,
            'name' => 'Parking Lot',
            'location' => 'Ground Level',
            'ip' => 'http://192.168.1.51',
            'model' => 'Uniview',
            'serial' => 'UNV987654321',
            'group' => 'Perimeter',
            'status' => 'Enabled',
            'schedule' => '24/7',
            'lastTested' => null,
            'testStatus' => 'unknown'
        ),
        array(
            'id' => 3,
            'name' => 'Server Room',
            'location' => 'Basement',
            'ip' => 'http://192.168.1.52',
            'model' => 'Axis',
            'serial' => 'AXIS5678',
            'group' => 'Critical',
            'status' => 'Enabled',
            'schedule' => 'Manual',
            'lastTested' => null,
            'testStatus' => 'unknown'
        ),
        array(
            'id' => 4,
            'name' => 'Reception',
            'location' => 'Front Desk',
            'ip' => 'http://192.168.1.60',
            'model' => 'Dahua',
            'serial' => 'DHI123456789AB',
            'group' => 'Main',
            'status' => 'Disabled',
            'schedule' => 'Manual',
            'lastTested' => null,
            'testStatus' => 'unknown'
        )
    );
}

$camerasFile = __DIR__ . '/logs/cameras.json';
$logsDir = __DIR__ . '/logs';

// Ensure logs directory exists
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}

// Ensure logs directory is writable
if (!is_writable($logsDir)) {
    @chmod($logsDir, 0755);
}

function loadCameras() {
    global $camerasFile;
    
    if (!file_exists($camerasFile)) {
        $cameras = getDefaultCameras();
        $json = json_encode($cameras, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return getDefaultCameras();
        }
        @file_put_contents($camerasFile, $json);
        return $cameras;
    }
    
    $content = @file_get_contents($camerasFile);
    if ($content === false || empty($content)) {
        $cameras = getDefaultCameras();
        $json = json_encode($cameras, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        @file_put_contents($camerasFile, $json);
        return $cameras;
    }
    
    $cameras = json_decode($content, true);
    if ($cameras === null || !is_array($cameras)) {
        $cameras = getDefaultCameras();
        $json = json_encode($cameras, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        @file_put_contents($camerasFile, $json);
        return $cameras;
    }
    
    return $cameras;
}

function saveCameras($cameras) {
    global $camerasFile;
    $json = json_encode($cameras, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return @file_put_contents($camerasFile, $json) !== false;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$input = array();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = array();
    }
}

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

if ($action === 'list') {
    $cameras = loadCameras();
    http_response_code(200);
    echo json_encode(array('success' => true, 'cameras' => $cameras));
    exit;
}

if ($method === 'POST' && $action === 'add') {
    $cameras = loadCameras();
    
    // Get next ID
    $newId = 1;
    foreach ($cameras as $cam) {
        if (isset($cam['id']) && $cam['id'] >= $newId) {
            $newId = $cam['id'] + 1;
        }
    }
    
    $name = isset($input['name']) ? sanitize_input($input['name']) : '';
    $location = isset($input['location']) ? sanitize_input($input['location']) : '';
    $ip = isset($input['ip']) ? sanitize_input($input['ip']) : '';
    $model = isset($input['model']) ? sanitize_input($input['model']) : '';
    $serial = isset($input['serial']) ? sanitize_input($input['serial']) : '';
    $group = isset($input['group']) ? sanitize_input($input['group']) : '';
    $status = isset($input['status']) ? $input['status'] : 'Enabled';
    $schedule = isset($input['schedule']) ? sanitize_input($input['schedule']) : '24/7';
    
    $camera = array(
        'id' => $newId,
        'name' => $name,
        'location' => $location,
        'ip' => $ip,
        'model' => $model,
        'serial' => $serial,
        'group' => $group,
        'status' => ($status === 'Enabled' || $status === 'Disabled') ? $status : 'Enabled',
        'schedule' => $schedule,
        'lastTested' => null,
        'testStatus' => 'unknown'
    );
    
    if (!$camera['name'] || !$camera['ip']) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Name and IP are required'));
        exit;
    }
    
    $cameras[] = $camera;
    if (saveCameras($cameras)) {
        echo json_encode(array('success' => true, 'message' => 'Camera added', 'camera' => $camera));
    } else {
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => 'Failed to save camera'));
    }
    exit;
}

if ($method === 'POST' && $action === 'update') {
    $cameras = loadCameras();
    $id = isset($input['id']) ? intval($input['id']) : 0;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Invalid camera ID'));
        exit;
    }
    
    $found = false;
    foreach ($cameras as &$cam) {
        if ($cam['id'] === $id) {
            if (isset($input['name'])) $cam['name'] = sanitize_input($input['name']);
            if (isset($input['location'])) $cam['location'] = sanitize_input($input['location']);
            if (isset($input['ip'])) $cam['ip'] = sanitize_input($input['ip']);
            if (isset($input['model'])) $cam['model'] = sanitize_input($input['model']);
            if (isset($input['serial'])) $cam['serial'] = sanitize_input($input['serial']);
            if (isset($input['group'])) $cam['group'] = sanitize_input($input['group']);
            if (isset($input['status'])) $cam['status'] = ($input['status'] === 'Enabled' || $input['status'] === 'Disabled') ? $input['status'] : 'Enabled';
            if (isset($input['schedule'])) $cam['schedule'] = sanitize_input($input['schedule']);
            $found = true;
            
            if (!$cam['name'] || !$cam['ip']) {
                http_response_code(400);
                echo json_encode(array('success' => false, 'error' => 'Name and IP are required'));
                exit;
            }
            
            if (saveCameras($cameras)) {
                echo json_encode(array('success' => true, 'message' => 'Camera updated', 'camera' => $cam));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'error' => 'Failed to save camera'));
            }
            exit;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode(array('success' => false, 'error' => 'Camera not found'));
    }
    exit;
}

if ($method === 'POST' && $action === 'delete') {
    $cameras = loadCameras();
    $id = isset($input['id']) ? intval($input['id']) : 0;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'Invalid camera ID'));
        exit;
    }
    
    $found = false;
    foreach ($cameras as $key => $cam) {
        if ($cam['id'] === $id) {
            unset($cameras[$key]);
            $found = true;
            break;
        }
    }
    
    if ($found) {
        $cameras = array_values($cameras);
        if (saveCameras($cameras)) {
            echo json_encode(array('success' => true, 'message' => 'Camera deleted'));
        } else {
            http_response_code(500);
            echo json_encode(array('success' => false, 'error' => 'Failed to delete camera'));
        }
    } else {
        http_response_code(404);
        echo json_encode(array('success' => false, 'error' => 'Camera not found'));
    }
    exit;
}

if ($method === 'POST' && $action === 'bulk-delete') {
    $cameras = loadCameras();
    $ids = isset($input['ids']) ? $input['ids'] : array();
    
    if (!is_array($ids) || empty($ids)) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => 'No IDs provided'));
        exit;
    }
    
    $deleted = 0;
    foreach ($cameras as $key => $cam) {
        if (in_array($cam['id'], $ids)) {
            unset($cameras[$key]);
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $cameras = array_values($cameras);
        if (saveCameras($cameras)) {
            echo json_encode(array('success' => true, 'message' => 'Deleted ' . $deleted . ' camera(s)'));
        } else {
            http_response_code(500);
            echo json_encode(array('success' => false, 'error' => 'Failed to delete cameras'));
        }
    } else {
        http_response_code(404);
        echo json_encode(array('success' => false, 'error' => 'No cameras found to delete'));
    }
    exit;
}

http_response_code(400);
echo json_encode(array('success' => false, 'error' => 'Invalid action'));
?>
