<?php
class registrationService 
{
    private PDO $pdo;
    private array $userInputs;

    public function __construct(array $userInputs, PDO $pdo)
    {  
        $this->pdo = $pdo;
        $this->userInputs = $userInputs;
    }

    public function validateInputs(): array 
    {
        $errors = [];

        foreach ($this->userInputs as $key => $input) {
            if ($key === 'csrf_token') {
                if (empty($input['value'])) {
                    $errors[] = 'csrf_token_empty';
                } elseif ($input['value'] !== $input['compare_with']) {
                    $errors[] = 'csrf_token_invalid';
                }
                continue;
            }

            if (empty($input['value'])) {
                $errors[] = $input['type'] . '_empty';
                continue;
            }

            $length = strlen($input['value']);
            if ($length < $input['minLength']) {
                $errors[] = $input['type'] . '_short';
            }

            if ($length > $input['maxLength']) {
                $errors[] = $input['type'] . '_long';
            }
        }

        $username = $this->userInputs['username']['value'] ?? '';
        if (!empty($username) && !preg_match('/^[a-zA-Zа-яА-Я0-9_]+$/', $username)) {
            $errors[] = 'username_invalid';
        }

        return $errors;
    }

    public function isUsernameTaken(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM `users` WHERE `username` = ?');
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }

    public function registerUser(): array {
        $result = [
            'success' => false,
            'user_id' => null,
            'session_token' => null,
            'error' => null
        ];

        try {
            $password = $this->userInputs['password']['value'] ?? '';
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sessionToken = bin2hex(random_bytes(16));
            $username = $this->userInputs['username']['value'] ?? '';

            $stmt = $this->pdo->prepare(
                "INSERT INTO `users` (`session_token`, `username`, `password_hash`)
                VALUES (?, ?, ?)"
            );

            $stmt->execute([$sessionToken, $username, $passwordHash]);
            $userId = $this->pdo->lastInsertId();

            if ($userId) {
                $result['success'] = true;
                $result['user_id'] = (int)$userId;
                $result['session_token'] = $sessionToken;
                $result['username'] = $username;
            }
        } catch (Exception $e) {
            $result['error'] = 'server_error';
        }

        return $result;
    }
}