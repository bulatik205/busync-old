<?php
class createItem {
    public int $userId;
    public array $inputs;
    private PDO $pdo;

    public function __construct(int $userId, array $inputs, PDO $pdo) {
        $this->userId = $userId;
        $this->inputs = $inputs;
        $this->pdo = $pdo;
    }

    public function create() : array {
        try {
            $stmt = $this->pdo->prepare(
            "INSERT INTO items(user_id, item_name, item_art, item_category, item_cost, item_retail, item_manufacturer, item_remain, item_unit, item_status, item_description) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> execute([
                $this->userId,
                $this->inputs['item_name'] ?? "Не указано",
                $this->inputs['item_art'] ?? null,
                $this->inputs['item_category'] ?? null,
                $this->inputs['item_cost'] ?? null,
                $this->inputs['item_retail'] ?? null,
                $this->inputs['item_manufacturer'] ?? null,
                $this->inputs['item_remain'] ?? null,
                $this->inputs['item_unit'] ?? null,
                $this->inputs['item_status'] ?? null,
                $this->inputs['item_description'] ?? null
            ]);
            $lastInsertId = $this->pdo->lastInsertId();

            if (empty($lastInsertId)) {
                return [
                    'success' => false, 
                    'error' => [
                        'code' => 500,
                        'message' => 'Insert error'
                    ]
                ];
            }

            return [
                'success' => true,
                'fields' => [
                    'lastInsertId' => $lastInsertId
                ]
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Server error'
                ]
            ];
        }
    }
}