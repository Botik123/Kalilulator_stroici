<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'СтройКалькулятор',
        'tagline' => 'Точные расчёты стройматериалов',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL),
        'url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Europe/Moscow',
        'email' => getenv('APP_EMAIL') ?: 'x0bar@yandex.ru',
        'telegram' => getenv('APP_TELEGRAM') ?: 'https://t.me/tgkrostikaizkms',
        'vk' => getenv('APP_VK') ?: 'https://vk.ru/yfvyv5',
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'stroycalc',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'sqlite_path' => getenv('DB_SQLITE_PATH') ?: BASE_PATH . '/storage/database.sqlite',
        'auto_install' => filter_var(getenv('DB_AUTO_INSTALL') ?: '1', FILTER_VALIDATE_BOOL),
    ],
    'security' => [
        'session_name' => getenv('SESSION_NAME') ?: 'stroycalc_sid',
        'admin_session_key' => 'admin_user',
        'calculations_per_10min' => 30,
    ],
    'pagination' => [
        'articles' => 12,
    ],
];
