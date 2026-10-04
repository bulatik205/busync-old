<?php
session_start();
require_once '../config/config.php';
define('BASE_PATH', getBackPath(__DIR__));
define('LOCALHOST_API_PATH', BASE_PATH . API_PATH);

require_once '../modules/user/getApiTokens.php';

if (verifyAuth($pdo) === false) {
    header('Location: ' . BASE_PATH . 'login/');
    exit;
}

if (verificationBusiness($pdo, $_SESSION['user_id']) === false) {
    header('Location: ' . BASE_PATH . 'profile/reg/');
    exit;
}

$getApiTokens = new getApiTokens($_SESSION['user_id'] ?? null, $pdo);
$getApiTokensResult = $getApiTokens->get();
$apiTokens = $getApiTokensResult['keys'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/busync/api/v1/getMe/");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "API-key: " . $apiTokens[0]['api_key']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$getMeResult = json_decode(curl_exec($ch), true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuSync | Ваш профиль</title>
    <link rel="stylesheet" href="../sources/css/pages/profile/index.css">
</head>

<body>
    <main>
        <div class="left">
            <nav>
                <button><img src="../sources/images/system/home.png" alt="">Главная</button>
                <button><img src="../sources/images/system/shop.png" alt="">Товары</button>
                <button><img src="../sources/images/system/deal.png" alt="">Сделки</button>
                <button><img src="../sources/images/system/contacts.png" alt="">Конткты</button>
            </nav>
        </div>

        <div class="right">
            <div class="right--top--panel">
                <form class="right--top--panel--search">
                    <input type="text" required placeholder="Поиск по аккаунту">
                    <button>Поиск</button>
                </form>

                <div class="right--top--panel--profile">
                    <button onclick="window.location = '../profile/'">
                        <?php if (isset($userInfo['username'])): ?>
                            <?php echo htmlspecialchars($userInfo['username']) ?>
                        <?php else: ?>
                            Профиль
                        <?php endif ?>
                    </button>
                </div>
            </div>

            <div class="right--center--panel">
                <fieldset class="profile--body--content success" id="profile-success">
                    <p>Успешно!</p>
                </fieldset>

                <fieldset class="profile--body--content error" id="profile-error">
                    <p>Неуспешно, попробуйте позже</p>
                </fieldset>

                <div class="profile--header">
                    <h2>Ваши настройки</h2>
                </div>

                <div class="profile--body">
                    <div class="custom-fields">
                        <fieldset class="profile--body--content">
                            <legend>Имя</legend>
                            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($getMeResult['user']['first_name'] ?? "") ?>">
                        </fieldset>

                        <fieldset class="profile--body--content">
                            <legend>Фамилия</legend>
                            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($getMeResult['user']['last_name'] ?? "") ?>">
                        </fieldset>

                        <fieldset class="profile--body--content">
                            <legend>Отчество</legend>
                            <input type="text" name="father_name" id="father_name" value="<?php echo htmlspecialchars($getMeResult['user']['father_name'] ?? "") ?>">
                        </fieldset>

                        <fieldset class="profile--body--content">
                            <legend>Тип бизнеса</legend>

                            <div class="profile--body--content--radio">
                                <input type="radio" name="business_type" id="business_type_ip" value="ip" <?php echo ($getMeResult['user']['business_type'] ?? '') == "ip" ? 'checked' : 'checked' ?>>
                                <label for="business_type_ip">ИП</label>
                            </div>

                            <div class="profile--body--content--radio">
                                <input type="radio" name="business_type" id="business_type_ooo" value="ooo" <?php echo ($getMeResult['user']['business_type'] ?? '') == "ooo" ? 'checked' : '' ?>>
                                <label for="business_type_ooo">ООО</label>
                            </div>

                            <div class="profile--body--content--radio">
                                <input type="radio" name="business_type" id="business_type_sz" value="sz" <?php echo ($getMeResult['user']['business_type'] ?? '') == "sz" ? 'checked' : '' ?>>
                                <label for="business_type_sz">Самозанятый</label>
                            </div>

                            <div class="profile--body--content--radio">
                                <input type="radio" name="business_type" id="business_type_other" value="other" <?php echo ($getMeResult['user']['business_type'] ?? '') == "other" ? 'checked' : '' ?>>
                                <label for="business_type_other">Другой</label>
                            </div>
                        </fieldset>

                        <fieldset class="profile--body--content">
                            <legend>Сайт бизнеса</legend>
                            <input type="url" name="business_site" id="business_site" value="<?php echo htmlspecialchars($getMeResult['user']['business_site'] ?? "") ?>">
                        </fieldset>

                        <fieldset class="profile--body--content">
                            <legend>Телефон бизнеса</legend>
                            <input type="number" name="phone" id="phone" value="<?php echo htmlspecialchars($getMeResult['user']['phone'] ?? "") ?>">
                        </fieldset>

                        <div class="profile--body--content">
                            <button onclick=" saveProfile(API_KEY)">Сохранить</button>
                        </div>
                    </div>

                    <div class="api-tokens">
                        <div class="api-tokens--header">
                            <h2>Ваши API-key</h2>
                        </div>

                        <div class="api-tokens--body">
                            <?php if (!$getApiTokensResult['success']): ?>
                                <p>Ошибка загрузки API-key</p>
                            <?php else: ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <td>API-key</td>
                                            <td>Можно использовать</td>
                                            <td>Где используется</td>
                                            <td>Создан</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody id="api-keys">
                                        <?php foreach ($apiTokens as $token): ?>
                                            <?php
                                                $tokenUsed = !empty($token['used_by']);
                                            ?>
                                            <tr style="background-color: <?php echo $tokenUsed ? '#c577473f' : '#5dce8c4e'; ?>">
                                                <td>
                                                    <?php echo htmlspecialchars(substr($token['api_key'], 0, 16) . "..."); ?>
                                                </td>

                                                <td>
                                                    <?php echo $tokenUsed ? "❌" : "✅"; ?>
                                                </td>

                                                <td><?php echo htmlspecialchars($token['used_by'] ?? 'Нигде'); ?></td>
                                                <td><?php echo htmlspecialchars($token['data'] ?? ''); ?></td>
                                                <td>
                                                    <button onclick="copy('<?php echo htmlspecialchars($token['api_key']); ?>')">
                                                        Копировать
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <tr id="create-api-block" style="background-color: #5d88ce47;">
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td><button onclick="createApi(API_KEY)">Создать новый</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../sources/js/profile/copy.js"></script>
    <script src="../sources/js/profile/ajax.js"></script>
    <script>
        const API_KEY = '<?php echo htmlspecialchars($apiTokens[0]['api_key']) ?>';
    </script>
</body>

</html>