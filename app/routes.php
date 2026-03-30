<?php
/**
 * SecuriForm — Definición de rutas
 * Mapa de URL → Controlador@método.
 * Se carga desde public/index.php.
 *
 * @var \App\Helpers\Router $router
 */

declare(strict_types=1);

// ============================================================
// AUTH (sin sesión requerida)
// ============================================================
$router->get('/login',            'AuthController@showLogin');
$router->post('/login',           'AuthController@login');
$router->get('/logout',           'AuthController@logout');
$router->get('/recuperar',        'AuthController@showForgotPassword');
$router->post('/recuperar',       'AuthController@sendResetLink');
$router->get('/restablecer',      'AuthController@showResetForm');
$router->post('/restablecer',     'AuthController@resetPassword');

// ============================================================
// DASHBOARD
// ============================================================
$router->get('/',                 'DashboardController@index');
$router->get('/dashboard',       'DashboardController@index');

// ============================================================
// EMPRESAS (Super Admin)
// ============================================================
$router->get('/empresas',            'CompanyController@index');
$router->get('/empresas/crear',      'CompanyController@create');
$router->post('/empresas/crear',     'CompanyController@store');
$router->get('/empresas/editar',     'CompanyController@edit');
$router->post('/empresas/editar',    'CompanyController@update');
$router->get('/empresas/ver',        'CompanyController@show');

// ============================================================
// USUARIOS
// ============================================================
$router->get('/usuarios',            'UserController@index');
$router->get('/usuarios/crear',      'UserController@create');
$router->post('/usuarios/crear',     'UserController@store');
$router->get('/usuarios/editar',     'UserController@edit');
$router->post('/usuarios/editar',    'UserController@update');
$router->get('/usuarios/perfil',     'UserController@profile');
$router->post('/usuarios/perfil',    'UserController@updateProfile');

// ============================================================
// REGISTROS (los 13 formatos de seguridad)
// ============================================================
$router->get('/registros',           'RecordController@index');
$router->get('/registros/crear',     'RecordController@create');
$router->post('/registros/crear',    'RecordController@store');
$router->get('/registros/ver',       'RecordController@show');
$router->get('/registros/editar',    'RecordController@edit');
$router->post('/registros/editar',   'RecordController@update');

// ============================================================
// TICKETS (vista usuario/admin empresa)
// ============================================================
$router->get('/mis-tickets',            'TicketController@index');
$router->get('/mis-tickets/crear',      'TicketController@create');
$router->post('/mis-tickets/crear',     'TicketController@store');
$router->get('/mis-tickets/ver',        'TicketController@show');
$router->post('/mis-tickets/responder', 'TicketController@reply');

// ============================================================
// HELPDESK (vista agente)
// ============================================================
$router->get('/helpdesk',               'AgentController@index');
$router->get('/helpdesk/ticket',        'AgentController@show');
$router->post('/helpdesk/responder',    'AgentController@reply');
$router->post('/helpdesk/asignar',      'AgentController@assign');
$router->post('/helpdesk/estado',       'AgentController@changeStatus');

// ============================================================
// EXPORTACIÓN
// ============================================================
$router->get('/exportar/registro-pdf',  'ExportController@registroPdf');
$router->get('/exportar/listado-excel', 'ExportController@listadoExcel');
$router->get('/exportar/ticket-pdf',    'ExportController@ticketPdf');
$router->get('/exportar/reporte-pdf',   'ExportController@reporteMensualPdf');

// ============================================================
// AUDITORÍA (Super Admin)
// ============================================================
$router->get('/auditoria',              'DashboardController@auditLog');
