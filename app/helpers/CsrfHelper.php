<?php

namespace App\Helpers;

/**
 * CsrfHelper — Protección CSRF para formularios POST.
 *
 * Uso en formularios:
 *   <input type="hidden" name="_csrf_token" value="<?= CsrfHelper::generate() ?>">
 *
 * Validación en controlador:
 *   CsrfHelper::verify($_POST['_csrf_token'] ?? '');
 */
class CsrfHelper
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generar token CSRF y almacenarlo en sesión.
     * Si ya existe uno válido, lo reutiliza (por sesión, no por request).
     */
    public static function generate(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Verificar que el token enviado coincide con el de sesión.
     * Si falla, retorna 403 y detiene la ejecución.
     */
    public static function verify(string $token): void
    {
        $sessionToken = $_SESSION[self::TOKEN_KEY] ?? '';

        if ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            exit('Error de seguridad: token CSRF inválido. Recarga la página e intenta de nuevo.');
        }
    }

    /**
     * Verificar sin detener ejecución. Retorna true/false.
     */
    public static function isValid(string $token): bool
    {
        $sessionToken = $_SESSION[self::TOKEN_KEY] ?? '';
        return $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

    /**
     * Generar campo HTML hidden listo para insertar en formulario.
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::TOKEN_KEY . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Regenerar token (usar después de operaciones sensibles).
     */
    public static function regenerate(): string
    {
        unset($_SESSION[self::TOKEN_KEY]);
        return self::generate();
    }
}
