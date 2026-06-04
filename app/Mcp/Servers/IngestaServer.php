<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CrearActivoDigitalTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('SecuriForm Ingesta')]
#[Version('0.1.0')]
#[Instructions(
    'Servidor para ingresar información a SecuriForm de forma conversacional. '.
    'Usa las herramientas para registrar información (hoy: activos digitales; próximamente registros PSC, equipos y empresas). '.
    'Todas las acciones se ejecutan como el usuario autenticado y respetan su empresa (tenant) y sus permisos. '.
    'Nunca solicites ni envíes credenciales de acceso (contraseñas/2FA) por este canal.'
)]
class IngestaServer extends Server
{
    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        CrearActivoDigitalTool::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $resources = [
        //
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $prompts = [
        //
    ];
}
