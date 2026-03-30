<?php

namespace App\Controllers;

use App\Helpers\Auth;

/**
 * Controller — Clase base para todos los controladores.
 *
 * Métodos utilitarios: view(), redirect(), json(), authorize().
 */
abstract class Controller
{
    /**
     * Renderizar una vista dentro del layout.
     *
     * @param string               $viewPath Ruta relativa sin extensión (ej: 'records/list')
     * @param array<string, mixed> $data     Variables disponibles en la vista
     * @param string               $layout   Layout a usar ('main' o 'auth')
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'main'): void
    {
        // Extraer variables para que estén disponibles en la vista
        extract($data);

        // Datos globales siempre disponibles en las vistas
        $authUser = Auth::isLoggedIn() ? [
            'id'         => Auth::userId(),
            'empresa_id' => Auth::empresaId(),
            'nombre'     => $_SESSION['nombre'] ?? '',
            'email'      => $_SESSION['email'] ?? '',
            'rol'        => Auth::role(),
        ] : null;

        $appName = APP_NAME;

        // Capturar el contenido de la vista
        ob_start();
        $viewFile = dirname(__DIR__) . '/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>Vista no encontrada: {$viewPath}</p>";
        }
        $content = ob_get_clean();

        // Renderizar dentro del layout
        $layoutFile = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Redirigir a otra URL.
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    /**
     * Respuesta JSON.
     *
     * @param mixed $data
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Verificar roles permitidos. Atajo para Auth::requireRole().
     *
     * @param string[] $roles
     */
    protected function authorize(array $roles): void
    {
        Auth::requireRole($roles);
    }

    /**
     * Obtener empresa_id de la sesión (SIEMPRE de sesión, nunca de request).
     */
    protected function empresaId(): int
    {
        return Auth::empresaId();
    }

    /**
     * Guardar mensaje flash en sesión para mostrar en la siguiente vista.
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Obtener y limpiar mensaje flash.
     *
     * @return array{type: string, message: string}|null
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
