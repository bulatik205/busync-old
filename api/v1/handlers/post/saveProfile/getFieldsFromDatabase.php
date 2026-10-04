<?php
class getFieldsFromDatabase
{
    public string $userId;
    private PDO $pdo;

    public function __construct(string $userId, PDO $pdo)
    {
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    public function get(): mixed
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users_info WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $SQLresult = $stmt->fetch();

            if (empty($SQLresult)) {
                $stmt = $this->pdo->prepare("INSERT INTO users_info(user_id) VALUES (?)");
                $stmt->execute([$this->userId]);
                return [
                    'first_name' => "",
                    'last_name' => "",
                    'father_name' => "",
                    'business_type' => "",
                    'business_site' => "",
                    'phone' => ""
                ];
            }

            return $SQLresult;
        } catch (PDOException $e) {
            return false;
        }
    }
}
