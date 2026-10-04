<?php
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
require_once BASE_PATH . 'api/v1/handlers/delete/deleteItem/validateInput.php';
require_once BASE_PATH . 'api/v1/handlers/delete/deleteItem/deleteItem.php';

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

$itemId = trim($_GET['id']) ?? null;

if ($itemId == null) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => 'Empty fields'
        ]
    ]);
    exit;
}

$validateInput = new validateInput($itemId);
$validateInputResult = $validateInput->validate();

if ($validateInputResult) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => 'Invalid inputs'
        ]
    ]);
    exit;
}

$deleteItem = new deleteItem($itemId, $userId, $pdo);
$deleteItemResult = $deleteItem->delete();

if (!$deleteItemResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $deleteItemResult['error']['code'],
            'message' => $deleteItemResult['error']['message']
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => true
]);
exit;