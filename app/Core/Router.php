<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: mixed}> */
    private array $routes = [];

    public function get(string $pattern, mixed $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, mixed $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => '/' . trim($pattern, '/'),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);
        $base = base_path();

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        $allowed = [];

        foreach ($this->routes as $route) {
            $params = $this->match($route['pattern'], $path);

            if ($params === null) {
                continue;
            }

            if ($route['method'] !== $method) {
                $allowed[] = $route['method'];
                continue;
            }

            $this->invoke($route['handler'], $params);
            return;
        }

        if ($allowed !== []) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            abort(405, 'Этот адрес не поддерживает такой метод запроса.');
        }

        abort(404, 'Такой страницы нет. Возможно, ссылка устарела или в адресе опечатка.');
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        if ($pattern === $path) {
            return [];
        }

        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn (array $m): string => '(?P<' . $m[1] . '>[^/]+)',
            $pattern
        );

        $regex = '#^' . $regex . '$#u';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param array<string, string> $params
     */
    private function invoke(mixed $handler, array $params): void
    {
        if (is_callable($handler)) {
            $result = $handler($params);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                throw new RuntimeException('Controller not found: ' . $class);
            }

            $controller = new $class();
            $result = $controller->{$method}($params);
        } else {
            throw new RuntimeException('Invalid route handler.');
        }

        if (is_string($result)) {
            echo $result;
        }
    }
}
