<?php

use App\Mcp\Servers\IngestaServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Servers
|--------------------------------------------------------------------------
|
| Servidor de ingesta conversacional. Se autentica con un token de API de
| Sanctum (header Authorization: Bearer <token>); cada herramienta corre
| como el usuario del token y respeta su empresa (tenant) y permisos.
|
*/

Mcp::web('/mcp/ingesta', IngestaServer::class)
    ->middleware('auth:sanctum');
