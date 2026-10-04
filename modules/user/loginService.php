<?php
class loginService
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

    public function loginUser(): array
    {
        $result = [
            'success' => false,
            'user_id' => null,
            'session_token' => null,
            'error' => null
        ];

        try {
            $username = $this->userInputs['username']['value'] ?? '';
            $password = $this->userInputs['password']['value'] ?? '';

            $stmt = $this->pdo->prepare('SELECT id, password_hash FROM `users` WHERE `username` = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!empty($user)) {
                if (password_verify($password, $user['password_hash'])) {
                    $result['success'] = true;
                    $result['user_id'] = (int)$user['id'];
                    $result['session_token'] = bin2hex(random_bytes(16));
                    $result['username'] = $username;

                    $stmt = $this->pdo->prepare('UPDATE `users` SET `session_token` = ? WHERE `id` = ?');
                    $stmt->execute([$result['session_token'], $result['user_id']]);
                } else {
                    $result['error'] = 'incorrect_input';
                }
            } else {
                $result['error'] = 'incorrect_input';
            }
        } catch (PDOException $e) {
            $result['error'] = 'server_error';
        }

        return $result;
    }
}
