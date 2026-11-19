<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Установщик доступен только из командной строки.');
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/bootstrap.php';

echo "СтройКалькулятор — установка\n";
echo "Драйвер БД: " . config('db.driver') . "\n\n";

try {
    $pdo = db();
} catch (PDOException $e) {
    $driver = strtolower((string) config('db.driver'));

    if ($driver === 'mysql' && str_contains($e->getMessage(), 'Unknown database')) {
        echo "База данных не найдена, пробую создать...\n";

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', config('db.host'), config('db.port'), config('db.charset', 'utf8mb4'));
            $server = new PDO($dsn, (string) config('db.user'), (string) config('db.pass'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $name = (string) config('db.name');
            if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
                throw new RuntimeException('Недопустимое имя базы данных: ' . $name);
            }
            $server->exec('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            App\Core\Database::reset();
            $pdo = db();
            echo "База данных `{$name}` создана.\n";
        } catch (Throwable $createError) {
            fwrite(STDERR, "Не удалось создать базу данных: " . $createError->getMessage() . "\n");
            fwrite(STDERR, "Создайте базу вручную и укажите её в .env.\n");
            exit(1);
        }
    } else {
        fwrite(STDERR, "Ошибка подключения к БД: " . $e->getMessage() . "\n");
        fwrite(STDERR, "Проверьте параметры DB_* в .env. Для локального запуска можно использовать DB_DRIVER=sqlite.\n");
        exit(1);
    }
}

try {
    App\Core\Installer::install($pdo, true);
    echo "Схема и демо-контент установлены.\n";

    $login = getenv('ADMIN_LOGIN') ?: '';
    $password = getenv('ADMIN_PASSWORD') ?: '';

    if ($login !== '' && $password !== '') {
        App\Core\Installer::createAdmin($pdo, $login, $password);
        echo "Администратор «{$login}» создан или обновлён.\n";
    } elseif ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        echo "Администратор ещё не создан. Откройте /admin/setup в браузере или задайте ADMIN_LOGIN и ADMIN_PASSWORD.\n";
    }

    echo "\nГотово. Откройте сайт в браузере.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Ошибка установки: " . $e->getMessage() . "\n");
    exit(1);
}
