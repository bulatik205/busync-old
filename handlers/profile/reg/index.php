<?php
session_start();
require_once '../../../config/config.php';
define('BASE_PATH', getBackPath(__DIR__));

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || verifyAuth($pdo) === false) {
    header('Location: ' . BASE_PATH . 'login/');
    exit;
}

if (verificationBusiness($pdo, $_SESSION['user_id']) !== false) {
    header('Location: ' . BASE_PATH . 'dashboard/');
    exit;
}

$inputsRequired = [
    'business_name' => trim($_POST['business_name']),
    'business_location' => trim($_POST['business_location'])
];

$inputsOptional = [
    'business_profit' => trim($_POST['business_profit']) ?? null,
    'business_welcome' => trim($_POST['business_welcome']) ?? null
];

function validateInputs($inputsRequired, $inputsOptional): bool
{
    foreach ($inputsRequired as $key => $value) {
        if (empty($value)) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=empty_' . $key);
            return false;
        }

        if (strlen($value) > 60) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=long_' . $key);
            return false;
        }

        if (strlen($value) < 3) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=short_' . $key);
            return false;
        }

        if (!preg_match('/^[a-zA-Zа-яА-Я0-9\s\-\.,]+$/u', $value)) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=invalid_' . $key);
            return false;
        }
    }

    foreach ($inputsOptional as $key => $value) {
        if ($value === '') {
            continue;
        }

        if (!preg_match('/^[0-9]+$/', $value)) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=invalid_' . $key);
            return false;
        }

        if ((int)$value > 1000000000) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=big_' . $key);
            return false;
        }

        if ((int)$value < 0) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=negative_' . $key);
            return false;
        }
    }

    if (isset($inputsOptional['business_profit']) && isset($inputsOptional['business_welcome'])) {
        if ((int)$inputsOptional['business_profit'] > (int)$inputsOptional['business_welcome']) {
            header('Location: ' . BASE_PATH . 'profile/reg?error=profit_less_welcome');
            return false;
        }
    }

    return true;
}

if (!validateInputs($inputsRequired, $inputsOptional)) {
    exit;
}

try {
    $apiKey = bin2hex(random_bytes(128));

    $stmt = $pdo->prepare("INSERT INTO users_api(user_id, api_key, used_by, is_system) VALUES(?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $apiKey,
        "Этот ключ используется для внутренний запросов. Вы не можете его использовать для API, но и показывать этот ключ никому не надо.",
        "true"
    ]);
    $stmt = $pdo->prepare('INSERT INTO business_info (user_id, name, location, welcome_money, current_money) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $_SESSION['user_id'],
        $inputsRequired['business_name'], 
        $inputsRequired['business_location'], 
        $inputsOptional['business_welcome'] ?? 0, 
        $inputsOptional['business_profit'] ?? 0
    ]);
    $lastInsertId = $pdo->lastInsertId();

    if ($lastInsertId) {
        header('Location: ' . BASE_PATH . 'dashboard/');
        exit;
    } 

    header('Location: ' . BASE_PATH . 'profile/reg?error=database');
    exit;
} catch (Exception $e) {
    error_log(htmlspecialchars($e->getMessage()));
    header('Location: ' . BASE_PATH . 'profile/reg?error=database');
    exit;
}
