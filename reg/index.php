<?php
session_start();
require_once '../config/config.php';
define('BASE_PATH', getBackPath(__DIR__));

if (verifyAuth($pdo) !== false) {
    header('Location: ' . BASE_PATH . 'dashboard/');
    exit;
}

$csrf_token = bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrf_token;

$errorMessages = [
    'csrf_token_empty' => 'Сессия кончилась. Обновите страницу',
    'csrf_token_invalid' => 'Сессия кончилась. Обновите страницу',
    'username_empty' => 'Введите логин',
    'password_empty' => 'Введите пароль',
    'username_short' => 'Логин короткий (нужно от 4)',
    'username_long' => 'Логин длинный (нужно до 50)',
    'password_short' => 'Пароль короткий (нужно от 8)',
    'password_long' => 'Пароль длинный (нужно до 72)',
    'username_invalid' => 'Недопустимые символы в логине',
    'username_exists' => 'Логин занят',
    'registration_failed' => 'Неизвестная ошибка',
    'server_error' => 'Попробуйте позже'
];

$errorWithQuery = isset($_GET['error']) 
    ? ($errorMessages[$_GET['error']] ?? 'Неизвестная ошибка')
    : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | BuSync</title>
    <link rel="stylesheet" href="../sources/css/pages/login-reg/index.css">
</head>

<body>
    <main>
        <div class="main--body">
            <div class="main--body--header">
                <h1>Регистрация</h1>
            </div>

            <?php if ($errorWithQuery): ?>
                <div class="main--body--errors">
                    <p><?php echo htmlspecialchars($errorWithQuery) ?></p>
                </div>
            <?php endif ?>

            <form action="../handlers/reg/index.php" method="post">
                <input type="text" style="display: none" value="<?php echo htmlspecialchars($csrf_token) ?>" name="csrf_token">

                <div class="form--element">
                    <label for="username">Логин</label>
                    <input type="text" name="username" id="username" required minlength="4" maxlength="50">
                </div>

                <div class="form--element">
                    <label for="password">Пароль</label>
                    <input type="password" name="password" id="password" required minlength="8" maxlength="72">
                </div>

                <button type="submit">Регистрация</button>
            </form>

            <p>Есть аккаунт? <a href="../login/">Войти</a></p>
            <p>Забыли пароль? <a href="../ops/">Восстановить</a></p>
        </div>
    </main>

    <script src="../sources/js/login-reg/clearQuery.js"></script>
</body>

</html>