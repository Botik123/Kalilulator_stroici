<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;
use Throwable;

final class Installer
{
    public static function isInstalled(PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT 1 FROM calculators LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public static function install(PDO $pdo, bool $withSeed = true): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $schemaFile = BASE_PATH . '/database/schema.' . ($driver === 'sqlite' ? 'sqlite' : 'mysql') . '.sql';

        if (!is_file($schemaFile)) {
            throw new RuntimeException('Не найден файл схемы: ' . $schemaFile);
        }

        self::runSqlFile($pdo, $schemaFile);

        if ($withSeed) {
            self::seed($pdo);
        }
    }

    public static function runSqlFile(PDO $pdo, string $file): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException('Не удалось прочитать файл схемы.');
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            $pdo->exec($statement);
        }
    }

    public static function seed(PDO $pdo): void
    {
        $definitions = calculator_definitions();

        foreach ($definitions as $slug => $definition) {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM calculators WHERE slug = ?');
            $exists->execute([$slug]);

            $coefficients = json_encode($definition['coefficients'], JSON_UNESCAPED_UNICODE);

            if ((int) $exists->fetchColumn() > 0) {
                $update = $pdo->prepare('UPDATE calculators SET name = ?, coefficients = ?, is_active = 1 WHERE slug = ?');
                $update->execute([$definition['name'], $coefficients, $slug]);
            } else {
                $insert = $pdo->prepare('INSERT INTO calculators (slug, name, coefficients, is_active) VALUES (?, ?, ?, 1)');
                $insert->execute([$slug, $definition['name'], $coefficients]);
            }
        }

        $settings = [
            'site_name' => (string) config('app.name'),
            'support_email' => (string) config('app.email'),
            'telegram' => (string) config('app.telegram'),
        ];

        foreach ($settings as $name => $value) {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE name = ?');
            $exists->execute([$name]);
            if ((int) $exists->fetchColumn() === 0) {
                $pdo->prepare('INSERT INTO settings (name, value) VALUES (?, ?)')->execute([$name, $value]);
            }
        }

        $articles = require BASE_PATH . '/database/seed_articles.php';

        foreach ($articles as $index => $article) {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE slug = ?');
            $exists->execute([$article['slug']]);

            if ((int) $exists->fetchColumn() > 0) {
                continue;
            }

            $created = date('Y-m-d H:i:s', strtotime('-' . (6 + $index * 4) . ' days'));

            $insert = $pdo->prepare(
                'INSERT INTO articles
                 (slug, title, excerpt, content, cover_image, meta_title, meta_description, calculator_slug, status, views, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $insert->execute([
                $article['slug'],
                $article['title'],
                $article['excerpt'],
                $article['content'],
                $article['cover_image'] ?? null,
                $article['meta_title'] ?? $article['title'],
                $article['meta_description'] ?? $article['excerpt'],
                $article['calculator_slug'] ?? null,
                $article['status'] ?? 'published',
                $article['views'] ?? 0,
                $created,
                $created,
            ]);
        }
    }

    public static function createAdmin(PDO $pdo, string $login, string $password): void
    {
        $login = trim($login);

        if (mb_strlen($login) < 3) {
            throw new RuntimeException('Логин должен содержать минимум 3 символа.');
        }

        if (mb_strlen($password) < 8) {
            throw new RuntimeException('Пароль должен содержать минимум 8 символов.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $exists = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $exists->execute([$login]);
        $id = $exists->fetchColumn();

        if ($id !== false) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
            return;
        }

        $pdo->prepare('INSERT INTO users (login, password_hash) VALUES (?, ?)')->execute([$login, $hash]);
    }
}
