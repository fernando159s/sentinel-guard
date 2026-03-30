<?php

namespace App\Helpers;

/**
 * Auth — Sesión, roles y middleware de tenant isolation.
 *
 * Cada método de controlador DEBE llamar a:
 *   Auth::requireLogin()
 *   Auth::requireRole([...])
 *
 * El empresa_id se obtiene SIEMPRE de la sesión, NUNCA del request.
 */
class Auth
{
    /**
     * Verificar que hay una sesión activa. Si no, redirigir a login.
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            self::redirectToLogin();
        }
    }

    /**
     * Verificar que el usuario tiene uno de los roles permitidos.
     * Si no, retorna 403.
     *
     * @param string[] $allowedRoles Lista de constantes ROLE_*
     */
    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();

        $currentRole = $_SESSION['rol'] ?? '';
        if (!in_array($currentRole, $allowedRoles, true)) {
            http_response_code(403);
            exit('Acceso denegado: no tienes permisos para esta acción.');
        }
    }

    /**
     * Verificar si hay sesión activa.
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0;
    }

    /**
     * Obtener el empresa_id del usuario logueado.
     * Retorna 0 si no tiene empresa (super_admin, agente).
     *
     * REGLA: Este valor viene de la sesión, NUNCA del request.
     */
    public static function empresaId(): int
    {
        return (int) ($_SESSION['empresa_id'] ?? 0);
    }

    /**
     * Obtener el ID del usuario logueado.
     */
    public static function userId(): int
    {
        return (int) ($_SESSION['usuario_id'] ?? 0);
    }

    /**
     * Obtener el rol del usuario logueado.
     */
    public static function role(): string
    {
        return $_SESSION['rol'] ?? '';
    }

    /**
     * Verificar si el usuario actual es Super Admin.
     */
    public static function isSuperAdmin(): bool
    {
        return self::role() === ROLE_SUPER_ADMIN;
    }

    /**
     * Verificar si el usuario actual es agente de helpdesk.
     */
    public static function isAgent(): bool
    {
        return self::role() === ROLE_AGENT;
    }

    /**
     * Verificar que el recurso pertenece a la empresa del usuario.
     * Super admin y agentes pueden acceder a cualquier empresa.
     * Retorna 403 si no tiene acceso.
     *
     * @param int $resourceEmpresaId empresa_id del recurso que se quiere acceder
     */
    public static function requireTenantAccess(int $resourceEmpresaId): void
    {
        self::requireLogin();

        // Super admin y agentes pueden acceder a todas las empresas
        if (self::isSuperAdmin() || self::isAgent()) {
            return;
        }

        // Para el resto, el recurso debe pertenecer a su empresa
        if ($resourceEmpresaId !== self::empresaId()) {
            http_response_code(403);
            exit('Acceso denegado: no tienes acceso a datos de otra empresa.');
        }
    }

    /**
     * Iniciar sesión: guardar datos en $_SESSION.
     *
     * @param array{id: int, empresa_id: ?int, nombre: string, email: string, rol: string} $user
     */
    public static function login(array $user): void
    {
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = (int) $user['id'];
        $_SESSION['empresa_id'] = $user['empresa_id'] ? (int) $user['empresa_id'] : 0;
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['rol']        = $user['rol'];
        $_SESSION['login_time'] = time();
    }

    /**
     * Cerrar sesión y destruir datos.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Redirigir a login y detener ejecución.
     */
    private static function redirectToLogin(): void
    {
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
