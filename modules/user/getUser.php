<?php
class getUser {
    private $session_token;
    private $pdo;

    function __construct(string $session_token, $pdo) {
        $this->session_token = $session_token;
        $this->pdo = $pdo;
    }   

    function get() : mixed {
        if (empty($this->session_token)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `session_token` = ? LIMIT 1");
            $stmt->execute([$this->session_token]);
            $user = $stmt->fetch();

            if (empty($user)) {
                return false;
            }

            return $user;
        } catch (PDOException $e) {
            // logger logic
            return false;
        }
    }   
}