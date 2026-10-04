<?php
class deleteItem {
    public int $itemId;
    public int $userId;
    private PDO $pdo;

    public function __construct(int $itemId, int $userId, PDO $pdo) {
        $this->itemId = $itemId;
        $this->userId = $userId;
        $this->pdo = $pdo;
    }

    public function delete(): array {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
            $stmt->execute([$this->itemId, $this->userId]);
            
            $rowCount = $stmt->rowCount();

            if ($rowCount === 0) {
                return [
                    "success" => false,
                    "error" => [
                        "code" => 404,
                        "message" => "Item not found"
                    ]
                ];
            }

            return [
                "success" => true
            ];
            
        } catch (PDOException $e) {
            error_log($e->getMessage());
            
            return [
                "success" => false,
                "error" => [
                    "code" => 500,
                    "message" => "Server error"
                ]
            ];
        }
    }
}