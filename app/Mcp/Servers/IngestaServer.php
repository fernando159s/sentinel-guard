<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CrearActivoDigitalTool;
use App\Mcp\Tools\CrearRegistroTool;
use App\Mcp\Tools\FormatosRegistroTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('SecuriForm Ingesta')]
#[Version('0.2.0')]
#[Instructions(
    'Servidor para ingresar información a SecuriForm de forma conversacional. '.
    'Hoy puedes registrar activos digitales y registros de seguridad PSC (13 formatos F01–F13); próximamente equipos y empresas. '.
    'Para registros, primero consulta formatos-registro para saber los campos del formato, luego usa crear-registro. '.
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
        FormatosRegistroTool::class,
        CrearRegistroTool::class,
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
