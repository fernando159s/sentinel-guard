<?php

namespace App\Mcp\Tools;

use App\Services\Ingesta\EquipoIngestaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('crear-equipo')]
#[Description(
    'Registra un equipo en el inventario de SecuriForm (PC, laptop, impresora, servidor, USB, '.
    'expediente físico, etc.) para la empresa del usuario autenticado. El código interno (EQ-001) '.
    'se genera automáticamente si no se indica.'
)]
class CrearEquipoTool extends Tool
{
    public function handle(Request $request, EquipoIngestaService $service): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('No autenticado. Configura un token de API válido para usar esta herramienta.');
        }

        try {
            $equipo = $service->crear($request->all(), $user);
        } catch (AuthorizationException $e) {
            return Response::error($e->getMessage());
        } catch (ValidationException $e) {
            return Response::error('No se pudo registrar el equipo: '.collect($e->errors())->flatten()->implode(' '));
        }

        return Response::text(sprintf(
            '✅ Equipo registrado: %s — %s %s (%s) para la empresa #%d. Estado: %s.',
            $equipo->codigo_interno,
            $equipo->marca ?: 's/marca',
            $equipo->modelo ?: '',
            $equipo->tipo,
            $equipo->empresa_id,
            $equipo->estado,
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tipo' => $schema->string()
                ->enum(['pc_escritorio', 'laptop', 'impresora', 'servidor', 'usb', 'disco_externo', 'telefono', 'tablet', 'dispositivo_red', 'dvd_cd', 'expediente_fisico', 'soporte_nube', 'otro'])
                ->description('Tipo de equipo o soporte.')
                ->required(),
            'marca' => $schema->string()->description('Marca, ej. Lenovo, HP, Dell.'),
            'modelo' => $schema->string()->description('Modelo, ej. ThinkPad T14.'),
            'numero_serie' => $schema->string()->description('Número de serie (único). Opcional.'),
            'codigo_interno' => $schema->string()->description('Código de inventario. Si lo omites se genera EQ-XXX.'),
            'categoria' => $schema->string()->enum(['tecnologico', 'no_tecnologico'])->description('Categoría del activo. Por defecto tecnologico.'),
            'clasificacion_soporte' => $schema->string()->enum(['hdd_interno', 'hdd_externo', 'usb', 'servidor', 'nube', 'dvd', 'expediente_fisico', 'otro'])->description('Clasificación del soporte de información.'),
            'contenido_datos' => $schema->string()->description('Descripción de los datos que almacena.'),
            'sistema_operativo' => $schema->string()->description('Sistema operativo (solo equipos tecnológicos).'),
            'procesador' => $schema->string()->description('Procesador (solo equipos tecnológicos).'),
            'ram_gb' => $schema->integer()->description('RAM en GB.'),
            'disco_gb' => $schema->integer()->description('Disco en GB.'),
            'ubicacion' => $schema->string()->description('Ubicación física, ej. "Oficina principal - Piso 2".'),
            'estado' => $schema->string()->enum(['activo', 'mantenimiento', 'obsoleto', 'dado_de_baja'])->description('Estado. Por defecto activo.'),
            'nivel_sensibilidad' => $schema->string()->enum(['publico', 'interno', 'confidencial', 'sensible'])->description('Sensibilidad de la información. Por defecto interno.'),
            'fecha_adquisicion' => $schema->string()->description('Fecha de adquisición (YYYY-MM-DD).'),
            'fecha_garantia' => $schema->string()->description('Vencimiento de garantía (YYYY-MM-DD).'),
            'observaciones' => $schema->string()->description('Notas adicionales.'),
            'empresa_id' => $schema->integer()->description('Solo para super admin: ID de la empresa destino.'),
        ];
    }
}
