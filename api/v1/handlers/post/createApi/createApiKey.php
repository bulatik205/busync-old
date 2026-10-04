<?php
class createApiKey
{
    public int $userId;
    private PDO $pdo;

    public function __construct(int $userId, PDO $pdo)
    {
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    public function create(): array
    {
        $data = [];

        try {
            // todo: if key exist?
            $apiKey = bin2hex(random_bytes(128));

            $stmt = $this->pdo->prepare("INSERT INTO users_api(user_id, api_key, is_system) VALUES(?, ?, ?)");
            $stmt->execute([
                $this->userId,
                $apiKey,
                "true"
            ]);

            $lastInsertId = $this->pdo->lastInsertId();

            $data['success'] = true;
            $data['fields']['last_inserted_id'] = $lastInsertId;
            $data['fields']['last_inserted_apikey'] = $apiKey;
            $data['fields']['last_inserted_apikey_substr'] = substr($apiKey, 0, 16);

            return $data;
        } catch (Exception $e) {
            error_log(htmlspecialchars($e->getMessage()));
            $data['success'] = false;
            $data['error']['code'] = 500;
            $data['error']['message'] = "Server error";
            return $data;
        }
    }
}
