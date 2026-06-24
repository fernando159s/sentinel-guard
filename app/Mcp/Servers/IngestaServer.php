<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ConsultarActivosDigitalesTool;
use App\Mcp\Tools\CrearActivoDigitalTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('SecuriForm Ingesta')]
#[Version('0.1.0')]
#[Instructions(
    'Servidor para ingresar y consultar información de SecuriForm de forma conversacional. '.
    'Usa las herramientas para registrar y consultar información (hoy: activos digitales; próximamente registros PSC, equipos y empresas). '.
    'Todas las acciones se ejecutan como el usuario autenticado y respetan su empresa (tenant) y sus permisos. '.
    'Nunca solicites ni envíes credenciales de acceso (contraseñas/2FA) por este canal.'
)]
class IngestaServer extends Server
{
    /**
     * Lista canónica de herramientas del MCP. El chat in-app reusa exactamente
     * este mismo conjunto (ver App\Services\Asistente\McpToolBridge), de modo
     * que MCP y chat comparten una única fuente de verdad.
     *
     * @var array<int, class-string<Tool>>
     */
    public const TOOLS = [
        CrearActivoDigitalTool::class,
        ConsultarActivosDigitalesTool::class,
    ];

    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = self::TOOLS;

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
