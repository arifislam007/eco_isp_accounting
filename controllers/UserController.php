<?php
declare(strict_types=1);

class UserController
{
    public function getAll(): array
    {
        $pdo = db();
        $stmt = $pdo->query(
            'SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $userId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT id, name, email, role, is_active, created_at FROM users WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $role = (string) ($payload['role'] ?? 'viewer');

        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Name, email, and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        if (!in_array($role, ['admin', 'manager', 'viewer'], true)) {
            $role = 'viewer';
        }

        $pdo = db();

        // Check if email already exists
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists.'];
        }

        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
            );
            $stmt->execute([$name, $email, $passwordHash, $role]);
            return ['success' => true, 'message' => 'User created successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()];
        }
    }

    public function update(int $userId, array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $role = (string) ($payload['role'] ?? 'viewer');
        $isActive = (int) ($payload['is_active'] ?? 1);

        if (empty($name)) {
            return ['success' => false, 'message' => 'Name is required.'];
        }

        if (!in_array($role, ['admin', 'manager', 'viewer'], true)) {
            $role = 'viewer';
        }

        $pdo = db();

        try {
            $stmt = $pdo->prepare(
                'UPDATE users SET name = ?, role = ?, is_active = ? WHERE id = ?'
            );
            $stmt->execute([$name, $role, $isActive, $userId]);
            return ['success' => true, 'message' => 'User updated successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()];
        }
    }

    public function updatePassword(int $userId, string $newPassword): array
    {
        if (empty($newPassword)) {
            return ['success' => false, 'message' => 'Password is required.'];
        }

        $pdo = db();

        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$passwordHash, $userId]);
            return ['success' => true, 'message' => 'Password updated successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating password: ' . $e->getMessage()];
        }
    }

    public function delete(int $userId): array
    {
        // Prevent deleting the last admin user
        $pdo = db();
        $checkStmt = $pdo->query(
            'SELECT COUNT(*) as count FROM users WHERE role = "admin" AND id != ' . (int) $userId
        );
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (($result['count'] ?? 0) == 0) {
            return ['success' => false, 'message' => 'Cannot delete the last admin user.'];
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            return ['success' => true, 'message' => 'User deleted successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }
}
