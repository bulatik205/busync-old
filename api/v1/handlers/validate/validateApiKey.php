<?php
class validateApiKey {
    private string $apiKey;
    private PDO $pdo;

    function __construct(string $apiKey, PDO $pdo) {
        $this->apiKey = $apiKey;
        $this->pdo = $pdo;
    }

    function validate() : array {
        $data = [];

        try {
            $stmt = $this->pdo->prepare("SELECT user_id, used_by FROM `users_api` WHERE `api_key` = ? LIMIT 1");
            $stmt->execute([$this->apiKey]);
            $SQLdata = $stmt->fetch();

            if (empty($SQLdata)) {
                $data['success'] = false;
                $data['error']['message'] = 'Invalid API-key';
                $data['error']['code'] = 401;
                return $data;
            }

            if (empty($SQLdata['used_by'])) {
                $data['success'] = false;
                $data['error']['message'] = 'Unauthorized session';
                $data['error']['code'] = 401;
                return $data;
            }

            $data['success'] = true; 
            $data['userId'] = $SQLdata['user_id'];
            $data['usedBy'] = $SQLdata['used_by'];
            return $data;
        } catch (PDOException $e) {
            exceptionLog($e->getMessage(), __DIR__);
            $data['success'] = false;
            $data['error']['message'] = 'Server error';
            $data['error']['code'] = 500;
            return $data;
        }
    }
}