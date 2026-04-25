<?php
declare(strict_types=1);

class AuthController
{
    private AuthModel $authModel;

    public function __construct(?AuthModel $authModel = null)
    {
        $this->authModel = $authModel ?? new AuthModel();
    }

    public function login(string $email, string $password): array
    {
        $user = $this->authModel->findByEmail($email);

        if (!$user || !(int) $user['is_active'] || !password_verify($password, (string) $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        login_user($user);

        return ['success' => true, 'message' => 'Logged in successfully.'];
    }
}
