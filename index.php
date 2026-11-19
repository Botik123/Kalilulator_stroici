<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';

$router = require BASE_PATH . '/app/routes.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    error_log('[stroycalc] ' . $e->getMessage() . "\n" . $e->getTraceAsString());

    $debug = (bool) config('app.debug');
    $message = 'Что-то пошло не так. Попробуйте обновить страницу.';

    if ($e instanceof PDOException) {
        $message = 'База данных ещё не настроена. Запустите установщик: php bin/install.php — или проверьте параметры подключения в .env.';
    }

    http_response_code(500);
    echo view('errors/error', [
        'pageTitle' => 'Ошибка сервера',
        'metaDescription' => 'Внутренняя ошибка сервера.',
        'errorCode' => 500,
        'errorMessage' => $message,
        'errorDetails' => $debug ? $e->getMessage() : null,
        'activeNav' => '',
    ]);
}
