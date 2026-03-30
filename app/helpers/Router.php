<?php

namespace App\Helpers;

/**
 * Router — Mapeo de URLs a controladores.
 *
 * Uso en routes.php:
 *   $router->get('/ruta',  'ControllerName@method');
 *   $router->post('/ruta', 'ControllerName@method');
 */
class Router
{
    /** @var array<string, array<string, string>> */
    private array $routes = [];

    /**
     * Registrar ruta GET.
     */
    public function get(string $path, string $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    /**
     * Registrar ruta POST.
     */
    public function post(string $path, string $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    /**
     * Agregar ruta al registro interno.
     */
    private function addRoute(string $method, string $path, string $action): void
    {
        $this->routes[$method][$path] = $action;
    }

    /**
     * Despachar la petición al controlador correspondiente.
     */
    public function dispatch(string $method, string $uri): void
    {
        // Normalizar URI
        $uri = '/' . trim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        // Buscar ruta exacta
        $action = $this->routes[$method][$uri] ?? null;

        if ($action === null) {
            $this->notFound();
            return;
        }

        $this->executeAction($action);
    }

    /**
     * Ejecutar la acción: instanciar controlador y llamar método.
     *
     * @param string $action Formato: "ControllerName@methodName"
     */
    private function executeAction(string $action): void
    {
        [$controllerName, $methodName] = explode('@', $action, 2);

        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            $this->serverError("Controlador no encontrado: {$controllerName}");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            $this->serverError("Método no encontrado: {$controllerName}@{$methodName}");
            return;
        }

        $controller->$methodName();
    }

    /**
     * Respuesta 404.
     */
    private function notFound(): void
    {
        http_response_code(404);
        if (file_exists(dirname(__DIR__) . '/views/errors/404.php')) {
            require dirname(__DIR__) . '/views/errors/404.php';
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
        }
    }

    /**
     * Respuesta 500 (solo en modo debug).
     */
    private function serverError(string $message): void
    {
        http_response_code(500);
        if (APP_DEBUG) {
            echo "<h1>500 — Error interno</h1><p>{$message}</p>";
        } else {
            echo '<h1>500 — Error interno del servidor</h1>';
        }
    }
}
