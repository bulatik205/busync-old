<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 405,
            'message' => 'Method Not Allowed'
        ]
    ]);
    exit;
}

require_once '../config/config.php';
define('BASE_PATH', getBackPath(__DIR__));

require_once BASE_PATH . 'api/v1/handlers/validate/validateApiKey.php';
require_once BASE_PATH . 'api/v1/handlers/post/editItem/editService.php';
require_once BASE_PATH . 'api/v1/handlers/post/editItem/validateInputs.php';

$headers = getallheaders();

if (!isset($headers['API-key'])) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 401,
            'message' => 'Unauthorized'
        ]
    ]);
    exit;
}

$apiKey = $headers['API-key'];

$validateApiKey = new validateApiKey($apiKey, $pdo);
$validateApiKeyResult = $validateApiKey->validate();

if (!$validateApiKeyResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $validateApiKeyResult['error']['code'],
            'message' => $validateApiKeyResult['error']['message']
        ]
    ]);
    exit;
}

$userId = (int)$validateApiKeyResult['userId'];

if (!is_numeric($userId)) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => 'invalid_user_id'
        ]
    ]);
    exit;
}

$update = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => 'Invalid JSON format',
            'details' => json_last_error_msg()
        ]
    ]);
    exit;
}

if (!isset($update['fields'])) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => 'Empty fields'
        ]
    ]);
    exit;
}

$update['fields']['user_id'] = $userId;

$validateInputs = new validateInputs($update['fields']);
$validateInputsResult = $validateInputs->validate();

if (!$validateInputsResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $validateInputsResult['error']['code'],
            'message' => $validateInputsResult['error']['message']
        ]
    ]);
    exit;
}

$editService = new editService($update['fields'], $pdo);
$editServiceResult = $editService->edit();

if (!$editServiceResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $editServiceResult['error']['code'],
            'message' => $editServiceResult['error']['message']
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => true
]);
exit;
