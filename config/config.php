<?php
/**
 * SecuriForm — Configuración global
 * Carga variables de entorno y define constantes del sistema.
 */

declare(strict_types=1);

// Autoload de Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Validar variables obligatorias
$dotenv->required([
    'APP_SECRET',
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
])->notEmpty();

// ============================================================
// CONSTANTES DE APLICACIÓN
// ============================================================
define('APP_NAME',     $_ENV['APP_NAME']     ?? 'SecuriForm');
define('APP_ENV',      $_ENV['APP_ENV']      ?? 'development');
define('APP_DEBUG',    ($_ENV['APP_DEBUG']    ?? 'false') === 'true');
define('APP_URL',      rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'));
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'America/Lima');
define('APP_SECRET',   $_ENV['APP_SECRET']);
define('APP_ROOT',     $_ENV['APP_ROOT'] ?? dirname(__DIR__));

date_default_timezone_set(APP_TIMEZONE);

// ============================================================
// BASE DE DATOS
// ============================================================
define('DB_HOST',    $_ENV['DB_HOST']);
define('DB_PORT',    (int) ($_ENV['DB_PORT'] ?? 3306));
define('DB_NAME',    $_ENV['DB_NAME']);
define('DB_USER',    $_ENV['DB_USER']);
define('DB_PASS',    $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// ============================================================
// EMAIL (SMTP)
// ============================================================
define('MAIL_HOST',         $_ENV['MAIL_HOST']         ?? 'smtp.gmail.com');
define('MAIL_PORT',    (int)($_ENV['MAIL_PORT']        ?? 587));
define('MAIL_USERNAME',     $_ENV['MAIL_USERNAME']     ?? '');
define('MAIL_PASSWORD',     $_ENV['MAIL_PASSWORD']     ?? '');
define('MAIL_ENCRYPTION',   $_ENV['MAIL_ENCRYPTION']   ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@securiform.local');
define('MAIL_FROM_NAME',    $_ENV['MAIL_FROM_NAME']    ?? APP_NAME);

// ============================================================
// ARCHIVOS / UPLOADS
// ============================================================
define('UPLOAD_PATH',     $_ENV['UPLOAD_PATH']     ?? dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_SIZE', (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880));

// ============================================================
// SESIONES
// ============================================================
define('SESSION_LIFETIME', (int) ($_ENV['SESSION_LIFETIME'] ?? 480));
define('SESSION_NAME',     $_ENV['SESSION_NAME']     ?? 'securiform_sess');

// ============================================================
// ROLES DEL SISTEMA
// ============================================================
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN',       'admin_empresa');
define('ROLE_USER',        'usuario');
define('ROLE_AGENT',       'agente_helpdesk');
define('ROLE_READONLY',    'solo_lectura');

// ============================================================
// CONFIGURACIÓN GENERAL
// ============================================================
define('PAGINATION_LIMIT',     (int) ($_ENV['PAGINATION_LIMIT']     ?? 20));
define('TICKET_REOPEN_DAYS',   (int) ($_ENV['TICKET_REOPEN_DAYS']   ?? 7));
define('LOGIN_MAX_ATTEMPTS',   (int) ($_ENV['LOGIN_MAX_ATTEMPTS']   ?? 5));
define('LOGIN_LOCKOUT_MINUTES',(int) ($_ENV['LOGIN_LOCKOUT_MINUTES']?? 15));

// ============================================================
// MANEJO DE ERRORES
// ============================================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
