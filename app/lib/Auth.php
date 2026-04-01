<?php
declare(strict_types=1);

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireUser(): array
    {
        $u = self::user();
        if (!$u) {
            http_response_code(401);
            exit('Unauthorized');
        }
        return $u;
    }

    public static function login(string $email, string $password): bool
    {
        $email = trim($email);

        $row = Database::fetchOne('SELECT id, name, email, role, password_hash FROM users WHERE email = :email LIMIT 1', [
            'email' => $email,
        ]);

        if (!$row) {
            return false;
        }

        if (!password_verify($password, $row['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int)$row['id'],
            'name' => (string)$row['name'],
            'email' => (string)$row['email'],
            'role' => (string)$row['role'],
        ];

        return true;
    }

    public static function register(string $name, string $email, string $password, string $role): bool
    {
        $allowed = ['admin', 'coach', 'player'];
        if (!in_array($role, $allowed, true)) {
            return false;
        }

        // Ensure email uniqueness.
        $existing = Database::fetchOne('SELECT id FROM users WHERE email = :email LIMIT 1', [
            'email' => $email,
        ]);
        if ($existing) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::execute(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)',
            [
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash,
                'role' => $role,
            ]
        );

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}

