<?php

namespace App\Services\Asistente;

use Illuminate\Container\Container;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response as McpResponse;
use Laravel\Mcp\Server\Tool;
use Throwable;

/**
 * Puente entre las herramientas del servidor MCP y el function-calling del LLM.
 *
 * Reusa EXACTAMENTE las mismas clases Tool que expone IngestaServer: lee su
 * esquema (toArray) para describirlas al modelo y ejecuta su handle() en
 * proceso a través del contenedor. Cada herramienta corre como el usuario
 * autenticado (Request::user()), por lo que respeta empresa (tenant) y permisos
 * igual que cuando se invoca por el transporte MCP real.
 */
class McpToolBridge
{
    /**
     * @param  array<int, class-string<Tool>>  $toolClasses
     */
    public function __construct(private array $toolClasses) {}

    /**
     * Definiciones de las herramientas en el formato de function-calling
     * (compatible OpenAI/DeepSeek) para enviarlas al modelo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function definiciones(): array
    {
        return array_map(function (string $clase): array {
            $tool = $this->instanciar($clase);
            $arr = $tool->toArray();

            return [
                'type' => 'function',
                'function' => [
                    'name' => $arr['name'],
                    'description' => $arr['description'] ?? '',
                    'parameters' => $arr['inputSchema'] ?? ['type' => 'object', 'properties' => (object) []],
                ],
            ];
        }, $this->toolClasses);
    }

    /**
     * Ejecuta una herramienta por su nombre con los argumentos dados.
     *
     * @param  array<string, mixed>  $argumentos
     * @return array{contenido:string, error:bool}
     */
    public function ejecutar(string $nombre, array $argumentos): array
    {
        $tool = $this->resolverPorNombre($nombre);

        if (! $tool) {
            return ['contenido' => "Herramienta desconocida: {$nombre}.", 'error' => true];
        }

        $request = new McpRequest($argumentos);

        try {
            $resultado = Container::getInstance()->call([$tool, 'handle'], ['request' => $request]);
        } catch (Throwable $e) {
            report($e);

            return ['contenido' => 'Ocurrió un error al ejecutar la herramienta: '.$e->getMessage(), 'error' => true];
        }

        return $this->normalizar($resultado);
    }

    /**
     * Convierte la respuesta de una herramienta MCP en texto plano + flag de error.
     *
     * @return array{contenido:string, error:bool}
     */
    private function normalizar(mixed $resultado): array
    {
        if ($resultado instanceof McpResponse) {
            return [
                'contenido' => (string) $resultado->content(),
                'error' => $resultado->isError(),
            ];
        }

        // ResponseFactory u otros: el cast a string suele bastar; si no, JSON.
        if (is_object($resultado) && method_exists($resultado, '__toString')) {
            return ['contenido' => (string) $resultado, 'error' => false];
        }

        return ['contenido' => json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '', 'error' => false];
    }

    private function resolverPorNombre(string $nombre): ?Tool
    {
        foreach ($this->toolClasses as $clase) {
            $tool = $this->instanciar($clase);

            if ($tool->name() === $nombre) {
                return $tool;
            }
        }

        return null;
    }

    /**
     * @param  class-string<Tool>  $clase
     */
    private function instanciar(string $clase): Tool
    {
        return Container::getInstance()->make($clase);
    }
}
