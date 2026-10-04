<?php
session_start();
require_once '../config/config.php';
require_once '../modules/user/getUser.php';
define('BASE_PATH', getBackPath(__DIR__));

require_once '../modules/user/getApiTokens.php';

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

$getApiTokens = new getApiTokens($_SESSION['user_id'] ?? null, $pdo);
$getApiTokensResult = $getApiTokens->get();
$apiTokens = $getApiTokensResult['keys'];

$limit = 50;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/busync/api/v1/getItems/?limit=" . $limit);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "API-key: " . $apiTokens[0]['api_key']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$getItemsResult = json_decode(curl_exec($ch), true);

$itemsFields = $getItemsResult['fields'];
$totalItems = $getItemsResult['total'] ?? count($itemsFields);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuSync | Товары</title>
    <link rel="stylesheet" href="../sources/css/pages/items/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <fieldset class="item--body--content success" id="items-success"></fieldset>
                <fieldset class="item--body--content error" id="items-error"></fieldset>

                <div class="modal-overlay" id="editModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Редактировать товар</h2>
                            <button class="modal-close" onclick="closeEditModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" onsubmit="event.preventDefault(); updateItem();">
                                <input type="hidden" id="edit_item_id">

                                <fieldset>
                                    <legend>Название товара</legend>
                                    <input type="text" id="edit_item_name" minlength="4" maxlength="255" placeholder="Название" required>
                                </fieldset>

                                <fieldset>
                                    <legend>Описание товара</legend>
                                    <textarea id="edit_item_description" minlength="4" maxlength="10000" placeholder="Описание"></textarea>
                                </fieldset>

                                <fieldset>
                                    <legend>Артикул</legend>
                                    <input type="text" id="edit_item_art" minlength="4" maxlength="255" placeholder="Артикул">
                                </fieldset>

                                <fieldset>
                                    <legend>Категория</legend>
                                    <input type="text" id="edit_item_category" minlength="4" maxlength="100" placeholder="Категория">
                                </fieldset>

                                <fieldset>
                                    <legend>Себестоимость</legend>
                                    <input type="number" step="0.01" id="edit_item_cost" placeholder="Себестоимость" required>
                                </fieldset>

                                <fieldset>
                                    <legend>Розничная цена</legend>
                                    <input type="number" step="0.01" id="edit_item_retail" placeholder="Розничная цена" required>
                                </fieldset>

                                <fieldset>
                                    <legend>Производитель</legend>
                                    <input type="text" id="edit_item_manufacturer" minlength="4" maxlength="255" placeholder="Производитель">
                                </fieldset>

                                <fieldset>
                                    <legend>Остаток</legend>
                                    <input type="number" id="edit_item_remain" placeholder="Остаток">
                                </fieldset>

                                <fieldset>
                                    <legend>Единица измерения</legend>
                                    <input type="text" id="edit_item_unit" minlength="4" maxlength="255" placeholder="Единица измерения">
                                </fieldset>

                                <fieldset>
                                    <legend>Статус</legend>
                                    <input type="text" id="edit_item_status" minlength="4" maxlength="255" placeholder="Статус">
                                </fieldset>

                                <div class="modal-buttons">
                                    <button type="button" onclick="closeEditModal()">Отмена</button>
                                    <button type="submit">Сохранить изменения</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal-overlay" id="deleteModal">
                    <div class="modal-content delete-modal">
                        <div class="modal-header delete-header">
                            <h2><i class="fas fa-exclamation-triangle"></i> Подтверждение удаления</h2>
                            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
                        </div>
                        <div class="modal-body delete-body">
                            <div class="delete-icon">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <p class="delete-question">Действительно хотите удалить товар?</p>
                            <div class="delete-item-info" id="deleteItemInfo">

                            </div>
                            <div class="delete-buttons">
                                <button class="delete-cancel-btn" onclick="closeDeleteModal()">
                                    <i class="fas fa-times"></i> Отмена
                                </button>
                                <button class="delete-confirm-btn" onclick="confirmDelete()">
                                    <i class="fas fa-check"></i> Да, удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-add">
                    <div class="form-add--header">
                        <h2>Добавить товар</h2>
                    </div>

                    <div class="form-add--body">
                        <fieldset>
                            <legend>Название товара</legend>
                            <input type="text" id="item_name" minlength="4" maxlength="255" placeholder="Название: не меньше 4 и не больше 255 символов">
                        </fieldset>

                        <fieldset>
                            <legend>Описание товара</legend>
                            <textarea type="text" id="item_description" minlength="4" maxlength="10000" placeholder="Описание: не меньше 4 и не больше 10.000 символов"></textarea>
                        </fieldset>

                        <fieldset>
                            <legend>Артикул</legend>
                            <input type="text" id="item_art" minlength="4" maxlength="255" placeholder="Артикул: не меньше 4 и не больше 255 символов">
                        </fieldset>

                        <fieldset>
                            <legend>Категория</legend>
                            <input type="text" id="item_category" minlength="4" maxlength="100" placeholder="Категория: не меньше 4 и не больше 100 символов">
                        </fieldset>

                        <fieldset>
                            <legend>Себестоимость</legend>
                            <input type="number" step="0.01" id="item_cost" placeholder="Себестоимость, число (до 2 знаков после запятой)">
                        </fieldset>

                        <fieldset>
                            <legend>Розничная цена</legend>
                            <input type="number" step="0.01" id="item_retail" placeholder="Розничная цена, число (до 2 знаков после запятой)">
                        </fieldset>

                        <fieldset>
                            <legend>Производитель</legend>
                            <input type="text" id="item_manufacturer" minlength="4" maxlength="255" placeholder="Производитель: не меньше 4 и не больше 255 символов">
                        </fieldset>

                        <fieldset>
                            <legend>Остаток</legend>
                            <input type="number" id="item_remain" placeholder="Остаток, число">
                        </fieldset>

                        <fieldset>
                            <legend>Единица измерения</legend>
                            <input type="text" id="item_unit" minlength="4" maxlength="255" placeholder="Единица измерения: не меньше 4 и не больше 255 символов">
                        </fieldset>

                        <fieldset>
                            <legend>Статус</legend>
                            <input type="text" id="item_status" minlength="4" maxlength="255" placeholder="Статус, например, 'под заказ': не меньше 4 и не больше 255 символов">
                        </fieldset>

                        <button onclick="createItem()">Создать</button>
                    </div>
                </div>

                <div class="items">
                    <div class="items--body">
                        <h2>Все товары</h2>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th style="width: 50px;"></th>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Артикул</th>
                                    <th>Категория</th>
                                    <th>Остаток</th>
                                    <th>Розница</th>
                                    <th>Себестоимость</th>
                                    <th>Производитель</th>
                                    <th>Единица измерения</th>
                                    <th>Статус</th>
                                    <th>Описание</th>
                                </tr>
                            </thead>

                            <tbody id="items-table-body">
                                <?php if (!empty($itemsFields) && is_array($itemsFields)): ?>
                                    <?php foreach ($itemsFields as $item): ?>
                                        <tr ondblclick="this.classList.toggle('highlight')" style="cursor: pointer;">
                                            <td style="text-align: center;">
                                                <button class="edit-btn btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)) ?>)" title="Редактировать товар">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </td>

                                            <td style="text-align: center">
                                                <button class="delete-btn btn" onclick="openDeleteModal(<?php echo htmlspecialchars(json_encode($item)) ?>)" title="Удалить товар">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['id'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_name'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_art'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_category'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_remain'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_retail'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_cost'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_manufacturer'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_unit'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_status'] ?? '—') ?></td>
                                            <td><?php echo htmlspecialchars($item['item_description'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="13" style="text-align: center; padding: 40px;">Товаров пока нет</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="load-more-container" id="loadMoreContainer" style="text-align: center; margin-top: 20px; display: <?php echo (count($itemsFields) >= $limit) ? 'block' : 'none'; ?>;">
                            <button id="loadMoreBtn" class="load-more-btn" onclick="loadMoreItems()">
                                <i class="fas fa-spinner"></i> Показать еще
                            </button>
                            <div id="loadingSpinner" style="display: none;">
                                <i class="fas fa-circle-notch fa-spin"></i> Загрузка...
                            </div>
                        </div>

                        <div id="noMoreItems" style="text-align: center; margin-top: 20px; color: #888; display: none;">
                            Больше товаров нет
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const API_KEY = '<?php echo htmlspecialchars($apiTokens[0]['api_key']) ?>';
        const ITEMS_LIMIT = <?php echo $limit; ?>;
        let currentOffset = <?php echo count($itemsFields); ?>;
        let isLoading = false;
        let hasMoreItems = <?php echo (count($itemsFields) >= $limit) ? 'true' : 'false'; ?>;
    </script>
    <script src="../sources/js/items/ajax.js"></script>
    <script src="../sources/js/items/openMenu.js"></script>
</body>

</html>