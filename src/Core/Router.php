<?php

declare(strict_types=1);

namespace GeoPlans\Core;

/**
 * Clase Router para la gestión de rutas y despacho de peticiones.
 */
class Router
{
    private array $routes = [];

    /**
     * Registra una ruta GET.
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Despacha la petición actual.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset($this->routes[$method][$uri])) {
            [$controllerClass, $action] = $this->routes[$method][$uri];

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    return;
                }
            }
        }

        $this->handle404();
    }

    /**
     * Maneja el error 404 Not Found.
     */
    private function handle404(): void
    {
        http_response_code(404);
        echo "404 - Página no encontrada.";
    }
}