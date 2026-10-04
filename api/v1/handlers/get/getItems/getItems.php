<?php
class getItems {
    public int $userId;
    public int $offset;
    public int $limit;
    public PDO $pdo;

    public function __construct(int $userId, PDO $pdo, int $offset = 0, int $limit = 10) {
        $this->userId = $userId;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->pdo = $pdo;
    }

    public function get() : array {
        $data = [];

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `items` WHERE user_id = ? ORDER BY id LIMIT ? OFFSET ?");
            /* 
            * I read that MYSQL
            * converts types to string,
            * so I protected myself
            * from unwanted problems
            * by explicitly specifying INT
            */
            $stmt->bindValue(1, $this->userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $this->limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $this->offset, PDO::PARAM_INT);
            $stmt->execute();

            $data['success'] = true;
            $data['fields'] = $stmt->fetchAll();

            /*
            * Two fetch... Todo: think about how to make one request or how to save COUNT in the cache
            */
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM items WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $result = $stmt->fetch();

            $data['total'] = $result['total'];
            return $data;
        } catch (Exception $e) {
            $data = [];
            error_log($e->getMessage());
            $data['success'] = false;
            $data['error']['code'] = 500;
            $data['error']['message'] = "Server error";
            return $data;
        }
    }
}