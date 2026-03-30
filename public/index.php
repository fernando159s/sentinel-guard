<?php
/**
 * SecuriForm — Front Controller
 * Todas las peticiones pasan por aquí vía .htaccess.
 */

declare(strict_types=1);

// Cargar configuración global
require_once dirname(__DIR__) . '/config/config.php';

// Iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => APP_ENV === 'production',
        'cookie_samesite' => 'Lax',
        'gc_maxlifetime'  => SESSION_LIFETIME * 60,
    ]);
}

// Headers de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Content Security Policy — permite Bootstrap CDN, Chart.js CDN y estilos inline de Bootstrap
$csp = implode('; ', [
    "default-src 'self'",
    "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    "img-src 'self' data:",
    "connect-src 'self'",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'",
]);
header('Content-Security-Policy: ' . $csp);

// Obtener URI limpia
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && $basePath !== '\\') {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = '/' . trim($requestUri, '/');

$requestMethod = $_SERVER['REQUEST_METHOD'];

// Despachar ruta
use App\Helpers\Router;

$router = new Router();
require_once dirname(__DIR__) . '/app/routes.php';
$router->dispatch($requestMethod, $requestUri);
