<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    /**
     * @var array<int, array{method: string, path: string, handler: callable}>
     */
    private array $routes;

    /**
     * @param array<int, array{method: string, path: string, handler: callable}> $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($this->matchRoute($route['method'], $route['path'], $method, $path)) {
                $this->handleRoute($route['handler']);
                return;
            }
        }

        $this->sendJson(
            ['error' => 'http.error.not_found'],
            404
        );
    }

    private function matchRoute(string $routeMethod, string $routePath, string $requestMethod, string $requestPath): bool
    {
        if (strtoupper($routeMethod) !== strtoupper($requestMethod)) {
            return false;
        }

        // Простое точное совпадение пути: /polls, /auth/login и т.п.
        return rtrim($routePath, '/') === rtrim($requestPath, '/');
    }

    /**
     * @param callable $handler
     */
    private function handleRoute(callable $handler): void
    {
        // Контроллеры сами отправляют JSON-ответ и код статуса.
        $handler();
    }

    /**
     * Вспомогательный метод, если вдруг захочется вернуть JSON прямо из роутера.
     *
     * @param mixed $data
     */
    public function sendJson($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
