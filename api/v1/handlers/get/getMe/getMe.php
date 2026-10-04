<?php
class getMe {
    private int $userId;
    private PDO $pdo;

    function __construct(int $userId, PDO $pdo) {
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    function get() : array {
        $data = [];

        try {
            $stmt = $this->pdo->prepare("SELECT id, username, data FROM `users` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$this->userId]);
            $SQLUsersData = $stmt->fetch();

            $stmt = $this->pdo->prepare("SELECT first_name, last_name, father_name, business_type, business_site, phone FROM `users_info` WHERE `user_id` = ? LIMIT 1");
            $stmt->execute([$this->userId]);
            $SQLUsersInfoData = $stmt->fetch();

            $data['success'] = true;
            $data['user']['id'] = $SQLUsersData['id'] ?? null;
            $data['user']['username'] = $SQLUsersData['username'] ?? null;
            $data['user']['registration_date'] = $SQLUsersData['data'] ?? null;
            $data['user']['first_name'] = $SQLUsersInfoData['first_name'] ?? null;
            $data['user']['last_name'] = $SQLUsersInfoData['last_name'] ?? null;
            $data['user']['father_name'] = $SQLUsersInfoData['father_name'] ?? null;
            $data['user']['business_type'] = $SQLUsersInfoData['business_type'] ?? null;
            $data['user']['business_site'] = $SQLUsersInfoData['business_site'] ?? null;
            $data['user']['phone'] = $SQLUsersInfoData['phone'] ?? null;
            
            return $data;
        } catch (PDOException $e) {
            $data['success'] = false;
            $data['error']['message'] = 'server_error';
            $data['error']['code'] = '500';
            return $data;
        }
    }
}