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
require_once BASE_PATH . 'api/v1/handlers/post/createApi/createApiKey.php';

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

$userId = (int)$validateApiKeyResult['userId'] ?? null;

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

$createApiKey = new createApiKey($userId, $pdo);
$createApiKeyResult = $createApiKey->create();

if (!$createApiKeyResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $createApiKeyResult['error']['code'],
            'message' => $createApiKeyResult['error']['message']
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'fields' => [
        'last_inserted_id' => $createApiKeyResult['fields']['last_inserted_id'],
        'last_inserted_apikey' => $createApiKeyResult['fields']['last_inserted_apikey'],
        'last_inserted_apikey_substr' => $createApiKeyResult['fields']['last_inserted_apikey_substr']
    ]
]);
exit;
