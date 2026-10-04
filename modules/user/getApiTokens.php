<?php
class getApiTokens
{
    private int $userId;
    private PDO $pdo;

    function __construct(string $userId, PDO $pdo)
    {
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    function get(): array
    {
        $data = [];

        try {
            if ($this->userId == null) {
                $data['success'] = false;
                return $data;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM `users_api` WHERE `user_id` = ?");
            $stmt->execute([$this->userId]);
            $SQLdata = $stmt->fetchAll();

            if (empty($SQLdata)) {
                $data['success'] = false;
                return $data;
            }

            $data['success'] = true;
            $data['keys'] = $SQLdata;
            return $data;
        } catch (PDOException $e) {
            databaseLog($e->getMessage(), __DIR__);
            $data['success'] = false;
            return $data;
        }
    }
}
