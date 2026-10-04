<?php
session_start();

require_once '../../config/config.php';

define('BASE_PATH', getBackPath(__DIR__));

require_once BASE_PATH . 'modules/user/registrationService.php';

if (verifyAuth($pdo) !== false) {
    header('Location: ' . BASE_PATH . 'dashboard/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . 'reg/');
    exit;
}

$userInputs = [
    'username' => [
        'type' => 'username',
        'value' => trim($_POST['username'] ?? ''),
        'minLength' => 4,
        'maxLength' => 50
    ],
    'password' => [
        'type' => 'password',
        'value' => $_POST['password'] ?? '',
        'minLength' => 8,
        'maxLength' => 72
    ],
    'csrf_token' => [
        'type' => 'csrf_token',
        'value' => $_POST['csrf_token'] ?? '',
        'compare_with' => $_SESSION['csrf_token'] ?? ''
    ]
];

$registrationService = new RegistrationService($userInputs, $pdo);

$errors = $registrationService->validateInputs();
if (!empty($errors)) {
    unset($_SESSION['csrf_token']);
    header('Location: ' . BASE_PATH . 'reg?error=' . $errors[0]);
    exit;
}

$username = $userInputs['username']['value'];
if ($registrationService->isUsernameTaken($username)) {
    header('Location: ' . BASE_PATH . 'reg?error=username_exists');
    exit;
}

$result = $registrationService->registerUser();

if ($result['success']) {
    $_SESSION['user_id'] = $result['user_id'];
    $_SESSION['session_token'] = $result['session_token'];
    $_SESSION['username'] = $result['username'];
    unset($_SESSION['csrf_token']);
    
    header('Location: ' . BASE_PATH . 'dashboard/');
    exit;
} else {
    unset($_SESSION['csrf_token']);
    databaseLog($result['error'], __DIR__);
    header('Location: ' . BASE_PATH . 'reg?error=registration_failed');
    exit;
}