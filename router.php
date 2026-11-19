<?php
declare(strict_types=1);

// Маршрутизатор для встроенного сервера PHP:
// php -S localhost:8000 router.php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

// Не отдаём наружу служебные каталоги.
if (preg_match('#^/(app|database|bin|storage|vendor)(/|$)#', $path) || str_contains($path, '..')) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found';
    return true;
}

$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
