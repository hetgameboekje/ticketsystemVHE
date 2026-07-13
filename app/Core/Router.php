<?php

namespace App\Core;

use App\Shared\Log\PaginaBezoekLogger;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void
    {
        $regex = preg_replace('#\{id\}#', '(\d+)', $pattern);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        // /api/* routes zijn machine-to-machine (API-sleutel i.p.v. sessiecookie, zie
        // Controller::heeftApiSleutelMetScope) en hebben daarom geen CSRF-token — die clients
        // hebben nooit een browsersessie om er een uit te lezen.
        if ($method === 'POST' && !str_starts_with($path, '/api/') && !Csrf::verify($this->csrfTokenFromRequest())) {
            http_response_code(419);
            echo '419 - Ongeldig of verlopen beveiligingstoken. Herlaad de pagina en probeer het opnieuw.';
            return;
        }

        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            if (preg_match($regex, $path, $matches)) {
                array_shift($matches);
                $params = array_map('intval', $matches);

                PaginaBezoekLogger::log($method, $uri);

                [$class, $action] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Pagina niet gevonden.';
    }

    private function csrfTokenFromRequest(): ?string
    {
        if (is_string($_POST['_csrf'] ?? null) && $_POST['_csrf'] !== '') {
            return $_POST['_csrf'];
        }

        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return is_string($header) ? $header : null;
    }
}
