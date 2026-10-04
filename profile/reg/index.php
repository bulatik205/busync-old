<?php
session_start();
require_once '../../config/config.php';
define('BASE_PATH', getBackPath(__DIR__));

if (verifyAuth($pdo) === false) {
    header('Location: ' . BASE_PATH . 'login/');
    exit;
}

if (verificationBusiness($pdo, $_SESSION['user_id']) !== false) {
    header('Location: ' . BASE_PATH . 'dashboard/');
    exit;
}

$csrf_token = bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrf_token;

$errorMessages = [
    'empty_business_name' => 'Заполните имя бизнеса',
    'empty_business_location' => 'Заполните адрес бизнеса',
    'long_business_name' => 'Имя бизнеса слишком длинное (нужно до 60)',
    'long_business_location' => 'Адрес бизнеса слишком длинный (нужно до 60)',
    'short_business_name' => 'Имя бизнеса слишком короткое (нужно от 3)',
    'short_business_location' => 'Адрес бизнеса слишком короткий (нужно от 3)',
    'invalid_business_name' => 'Неверный формат имени (нужно только a-z, A-Z, а-я, А-Я, 0-9)',
    'invalid_business_location' => 'Неверный формат адреса (нужно только a-z, A-Z, а-я, А-Я, 0-9)',
    'invalid_business_profit' => 'Неверный формат текущей прибыли (можно только числа 0-9)',
    'invalid_business_welcome' => 'Неверный формат желательной прибыли (можно только числа 0-9)',
    'big_business_profit' => 'Ваш заработок больше миллиадра?',
    'negative_business_profit' => 'Ваш заработок меньше 0?',
    'big_business_welcome' => 'Вы хотите заработок больше миллиадра?',
    'negative_business_welcome' => 'Вы хотите заработок меньше 0?',
    'profit_less_welcome' => 'Ваша желательная прибыль меньше текущей?',
    'database' => 'Ошибка сервера. Попробуйте позже'
];

$errorWithQuery = isset($_GET['error'])
    ? ($errorMessages[$_GET['error']] ?? 'Неизвестная ошибка. Попробуйте позже')
    : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuSync | Регистрация бизнеса</title>
    <link rel="stylesheet" href="../../sources/css/pages/profile/reg/index.css">
</head>

<body>
    <main class="main">
        <div class="main--header">
            <h1>Зарегистрируйте бизнес</h1>
            <p>Для продолжения, заполните анкету. Это займет менее минуты. </p>
        </div>

        <?php if (isset($errorWithQuery)): ?>
            <div class="main--errors">
                <p>Ошибка: <?php echo(htmlspecialchars($errorWithQuery)) ?></p>
            </div>
        <?php endif ?>

        <div class="main--body">
            <form action="../../handlers/profile/reg/index.php" class="form" method="post">
                <input type="text" style="display: none" value="<?php echo htmlspecialchars($csrf_token) ?>" name="csrf_token">

                <div class="form--field">
                    <div class="form--field--label">
                        <img src="../../sources/images/system/business_name.png" alt="">
                        <label for="business_name">Название бизнеса</label>
                    </div>

                    <div class="form--field--input">
                        <input type="text" name="business_name" required placeholder="⚡️ Мой стартап" id="required" minlength="3" maxlength="60">
                    </div>
                </div>

                <div class="form--field">
                    <div class="form--field--label">
                        <img src="../../sources/images/system/business_city.png" alt="">
                        <label for="business_location">Адрес бизнеса</label>
                    </div>

                    <div class="form--field--input">
                        <input type="text" name="business_location" required placeholder="🐧 Там, где пингвинчики" id="required" minlength="3" maxlength="60">
                    </div>
                </div>

                <div class="form--field">
                    <div class="form--field--label">
                        <img src="../../sources/images/system/business_money_1.png" alt="">
                        <label for="business_profit">Средняя чистая прибыль</label>
                    </div>

                    <div class="form--field--warring">
                        <p>Введите только число без точек и пробелов. Используется для улучшенной аналитики. Это не обязательно, можно заполнить позже.</p>
                    </div>

                    <div class="form--field--input">
                        <input type="text" name="business_profit" placeholder="💸 100.000" minlength="3" maxlength="60">
                    </div>
                </div>

                <div class="form--field">
                    <div class="form--field--label">
                        <img src="../../sources/images/system/business_money_2.png" alt="">
                        <label for="business_welcome">Какую прибыль вы хотите</label>
                    </div>

                    <div class="form--field--warring">
                        <p>Введите только число без точек и пробелов. Используется для улучшенной аналитики. Это не обязательно, можно заполнить позже.</p>
                    </div>

                    <div class="form--field--input">
                        <input type="text" name="business_welcome" placeholder="💰 110.000" minlength="3" maxlength="60">
                    </div>
                </div>

                <button type="submit">Зарегистрировать</button>
            </form>
        </div>
    </main>
</body>

</html>