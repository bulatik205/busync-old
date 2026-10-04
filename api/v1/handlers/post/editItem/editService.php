<?php
class editService
{
    public array $inputs;
    public array $result;
    private PDO $pdo;

    public function __construct(array $inputs, PDO $pdo)
    {
        $this->inputs = $inputs;
        $this->pdo = $pdo;
    }

    private function validateIsEmptySaved($itemsFromDatabase): bool
    {
        return empty($itemsFromDatabase);
    }

    private function getSavedItem(): mixed
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM items WHERE id = ? LIMIT 1");
            $stmt->execute([$this->inputs['id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function edit()
    {
        $savedItem = $this->getSavedItem();

        if ($savedItem === false) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => "Server error"
                ]
            ];
        }

        if ($this->validateIsEmptySaved($savedItem)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 404, 
                    'message' => "ID " . $this->inputs['id'] . " not found"
                ]
            ];
        }

        try {
            $sql = "UPDATE items SET 
                    item_name = ?, 
                    item_art = ?, 
                    item_category = ?, 
                    item_cost = ?, 
                    item_retail = ?, 
                    item_manufacturer = ?, 
                    item_remain = ?, 
                    item_unit = ?, 
                    item_status = ?, 
                    item_description = ?
                    WHERE id = ? AND user_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $this->inputs['item_name'] ?? $savedItem['item_name'],
                $this->inputs['item_art'] ?? $savedItem['item_art'],
                $this->inputs['item_category'] ?? $savedItem['item_category'],
                $this->inputs['item_cost'] ?? $savedItem['item_cost'],
                $this->inputs['item_retail'] ?? $savedItem['item_retail'],
                $this->inputs['item_manufacturer'] ?? $savedItem['item_manufacturer'],
                $this->inputs['item_remain'] ?? $savedItem['item_remain'],
                $this->inputs['item_unit'] ?? $savedItem['item_unit'],
                $this->inputs['item_status'] ?? $savedItem['item_status'],
                $this->inputs['item_description'] ?? $savedItem['item_description'],
                $this->inputs['id'],
                $this->inputs['user_id'] ?? $savedItem['user_id']
            ]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 400,
                        'message' => "No changes made or item not found"
                    ]
                ];
            }

            return [
                'success' => true
            ];
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => "Server error"
                ]
            ];
        }
    }
}