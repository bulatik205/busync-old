<?php 
session_start();
require_once '../config/config.php';
require_once '../modules/user/getUser.php';
define('BASE_PATH', getBackPath(__DIR__));

if (verifyAuth($pdo) === false) {
    header('Location: ' . BASE_PATH . 'login/');
    exit;
}

if (verificationBusiness($pdo, $_SESSION['user_id']) === false) {
    header('Location: ' . BASE_PATH . 'profile/reg/');
    exit;
}

$getUserClass = new getUser($_SESSION['session_token'] ?? null, $pdo);
$userInfo = $getUserClass->get();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuSync | Управляйте вашим бизнесом</title>
    <link rel="stylesheet" href="../sources/css/pages/dashboard/index.css">
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
                <!-- todo -->
            </div>
        </div>
    </main>
</body>

</html>