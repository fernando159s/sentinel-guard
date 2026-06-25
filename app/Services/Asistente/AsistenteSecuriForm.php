<?php

namespace App\Services\Asistente;

use App\Models\User;
use Filament\Facades\Filament;

/**
 * Orquestador del asistente in-app de SecuriForm.
 *
 * Mantiene la conversación con DeepSeek, le ofrece las herramientas del MCP
 * (vía McpToolBridge) y ejecuta el bucle de razonamiento ↔ herramientas. El
 * system prompt acota DURO el alcance al contexto del proyecto: el asistente
 * solo habla de SecuriForm.
 */
class AsistenteSecuriForm
{
    public function __construct(
        private DeepSeekClient $deepseek,
        private McpToolBridge $bridge,
    ) {}

    /**
     * Procesa el historial visible y devuelve la respuesta del asistente.
     *
     * @param  array<int, array{role:string, content:string}>  $historial  Mensajes user/assistant visibles.
     * @return array{texto:string, herramientas:array<int,string>}
     */
    public function responder(array $historial, User $usuario): array
    {
        $maxHistorial = (int) config('asistente.historial_max', 12);

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($usuario)],
        ];

        foreach (array_slice($historial, -$maxHistorial) as $msg) {
            if (in_array($msg['role'], ['user', 'assistant'], true) && trim((string) $msg['content']) !== '') {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $tools = $this->bridge->definiciones();
        $maxIteraciones = (int) config('asistente.max_iteraciones', 6);
        $herramientasUsadas = [];

        for ($i = 0; $i < $maxIteraciones; $i++) {
            $respuesta = $this->deepseek->chat($messages, $tools);
            $mensaje = $respuesta['choices'][0]['message'] ?? [];

            $toolCalls = $mensaje['tool_calls'] ?? [];

            if (empty($toolCalls)) {
                return [
                    'texto' => trim((string) ($mensaje['content'] ?? '')) ?: 'No tengo una respuesta para eso.',
                    'herramientas' => $herramientasUsadas,
                ];
            }

            // Re-añadimos el mensaje del asistente con sus tool_calls tal cual,
            // requisito del protocolo para luego adjuntar los resultados.
            $messages[] = $mensaje;

            foreach ($toolCalls as $call) {
                $nombre = $call['function']['name'] ?? '';
                $argumentos = $this->decodificarArgumentos($call['function']['arguments'] ?? '{}');

                $resultado = $this->bridge->ejecutar($nombre, $argumentos);
                $herramientasUsadas[] = $nombre;

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $resultado['contenido'],
                ];
            }
        }

        return [
            'texto' => 'No pude completar la solicitud tras varios intentos. Reformula la petición, por favor.',
            'herramientas' => $herramientasUsadas,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodificarArgumentos(string $json): array
    {
        $datos = json_decode($json, true);

        return is_array($datos) ? $datos : [];
    }

    private function systemPrompt(User $usuario): string
    {
        $rol = $usuario->roles->first()?->name ?? 'usuario';
        $empresa = Filament::getTenant()?->razon_social
            ?? $usuario->empresa?->razon_social
            ?? ($usuario->empresa_id ? "empresa #{$usuario->empresa_id}" : 'todas las empresas (super admin)');

        $contexto = sprintf(
            "\n\n## Contexto del usuario actual\n- Nombre: %s\n- Rol: %s\n- Empresa (tenant): %s\n- Fecha de hoy: %s",
            $usuario->name,
            $rol,
            $empresa,
            now()->translatedFormat('l d \d\e F \d\e Y'),
        );

        return config('asistente.system_prompt').$contexto;
    }
}
