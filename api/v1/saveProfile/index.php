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
require_once BASE_PATH . 'api/v1/handlers/post/saveProfile/fieldsValidator.php';
require_once BASE_PATH . 'api/v1/handlers/post/saveProfile/getFieldsFromDatabase.php';
require_once BASE_PATH . 'api/v1/handlers/post/saveProfile/saveFields.php';

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
}

$fields = [
    'first_name',
    'last_name',
    'father_name',
    'business_type',
    'business_site',
    'phone'
];

$fieldsValidator = new fieldsValidator($update['fields']);
$fieldsValidatorResult = $fieldsValidator->validate();

if (!$fieldsValidatorResult) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 422,
            'message' => 'Invalid fields'
        ]
    ]);
    exit;
}

$getFieldsFromDatabase = new getFieldsFromDatabase($validateApiKeyResult['userId'], $pdo);
$getFieldsFromDatabaseResult = $getFieldsFromDatabase->get();

if ($getFieldsFromDatabaseResult === false) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 500,
            'message' => 'Server error'
        ]
    ]);
    exit;
}

$saveFields = new saveFields($update, $pdo, $getFieldsFromDatabaseResult);
$saveFieldsResult = $saveFields->save();

if (!$saveFieldsResult['success']) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 500,
            'message' => 'Server error'
        ]
    ]);
    exit;
}

echo json_encode($saveFieldsResult);
exit;
