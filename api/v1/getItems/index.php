<?php
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
require_once BASE_PATH . 'api/v1/handlers/get/getItems/getItems.php';
require_once BASE_PATH . 'api/v1/handlers/get/getItems/validateLimits.php';

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

$limitInGet = $_GET['limit'] ?? null;
$offsetInGet = $_GET['offset'] ?? null;

$validateLimits = new validateLimits($limitInGet, $offsetInGet);
$validateLimitsResult = $validateLimits->validate();

if (!$validateLimitsResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 400,
            'message' => $validateLimitsResult['error']['message']
        ]
    ]);
    exit;
}

$limit = $limitInGet !== null ? (int)$limitInGet : 10;
$offset = $offsetInGet !== null ? (int)$offsetInGet : 0;

$getItems = new getItems($userId, $pdo, $offset, $limit);
$getItemsResult = $getItems->get();

if (!$getItemsResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $getItemsResult['error']['code'],
            'message' => $getItemsResult['error']['message']        
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'fields' => $getItemsResult['fields']
]);
exit;