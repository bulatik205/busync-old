<?php
class saveFields {
    public array $update;
    public array $fieldsFromDatabase;
    private PDO $pdo;

    public function __construct(array $update, PDO $pdo, array $fieldsFromDatabase) {
        $this->update = $update;
        $this->fieldsFromDatabase = $fieldsFromDatabase;
        $this->pdo = $pdo;
    }

    public function save() : array {
        $data = [];

        try {
            $stmt = $this->pdo->prepare(
                "UPDATE users_info 
                SET 
                    `first_name` = ?, 
                    `last_name` = ?, 
                    `father_name` = ?, 
                    `business_type` = ?, 
                    `business_site` = ?, 
                    `phone`	= ?
                WHERE 
                    user_id = ?
            ");
            $stmt->execute([
                $this->update['fields']['first_name'] ?? $this->fieldsFromDatabase['first_name'],
                $this->update['fields']['last_name'] ?? $this->fieldsFromDatabase['last_name'],
                $this->update['fields']['father_name'] ?? $this->fieldsFromDatabase['father_name'],
                $this->update['fields']['business_type'] ?? $this->fieldsFromDatabase['business_type'],
                $this->update['fields']['business_site'] ?? $this->fieldsFromDatabase['business_site'],
                $this->update['fields']['phone'] ?? $this->fieldsFromDatabase['phone'],
                $this->fieldsFromDatabase['user_id']
            ]);

            $data['success'] = true;
            $data['data'] = $this->update;
        } catch (PDOException $e) {
            $data['success'] = false;
            $data['error']['message'] = 'server_error';
            $data['error']['code'] = '500'; 
        }

        return $data;
    }
}