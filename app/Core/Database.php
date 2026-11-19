<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = strtolower((string) config('db.driver', 'mysql'));

        if ($driver === 'sqlite') {
            $path = (string) config('db.sqlite_path');
            if (!str_starts_with($path, '/') && !preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
                $path = BASE_PATH . '/' . ltrim($path, '/\\');
            }

            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new PDOException('Не удалось создать каталог для базы данных: ' . $directory);
            }

            self::$pdo = new PDO('sqlite:' . $path);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::$pdo->exec('PRAGMA journal_mode = WAL');

            if (config('db.auto_install') && !Installer::isInstalled(self::$pdo)) {
                Installer::install(self::$pdo, true);
            }

            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            config('db.host'),
            config('db.port'),
            config('db.name'),
            config('db.charset', 'utf8mb4')
        );

        self::$pdo = new PDO($dsn, (string) config('db.user'), (string) config('db.pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    public static function reset(): void
    {
        self::$pdo = null;
    }
}
