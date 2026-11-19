<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function user(): ?array
    {
        $user = $_SESSION[config('security.admin_session_key', 'admin_user')] ?? null;

        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $login, string $password): bool
    {
        $stmt = db()->prepare('SELECT id, login, password_hash FROM users WHERE login = ? LIMIT 1');
        $stmt->execute([trim($login)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }

        session_regenerate_id(true);
        $_SESSION[config('security.admin_session_key', 'admin_user')] = [
            'id' => (int) $user['id'],
            'login' => (string) $user['login'],
        ];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION[config('security.admin_session_key', 'admin_user')]);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('admin_error', 'Войдите, чтобы открыть админ-панель.');
            redirect('/admin/login');
        }
    }

    public static function hasUsers(): bool
    {
        try {
            return (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
