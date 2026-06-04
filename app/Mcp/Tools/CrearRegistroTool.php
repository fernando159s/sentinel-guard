<?php

namespace App\Mcp\Tools;

use App\Enums\TipoFormato;
use App\Services\Ingesta\RegistroIngestaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('crear-registro')]
#[Description(
    'Crea un registro de seguridad PSC (uno de los 13 formatos F01–F13) para la empresa del usuario autenticado. '.
    'Si no conoces los campos exactos del formato, llama antes a formatos-registro. '.
    'El número de registro (p. ej. INC-2026-004) se genera automáticamente.'
)]
class CrearRegistroTool extends Tool
{
    public function handle(Request $request, RegistroIngestaService $service): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('No autenticado. Configura un token de API válido para usar esta herramienta.');
        }

        $empresaId = $request->get('empresa_id');

        try {
            $registro = $service->crear(
                (string) $request->get('tipo_formato'),
                (array) ($request->get('datos') ?? []),
                $user,
                $empresaId !== null ? (int) $empresaId : null,
            );
        } catch (AuthorizationException $e) {
            return Response::error($e->getMessage());
        } catch (ValidationException $e) {
            return Response::error('No se pudo crear el registro: '.collect($e->errors())->flatten()->implode(' '));
        }

        return Response::text(sprintf(
            '✅ Registro creado: %s (%s) para la empresa #%d. Estado: %s.',
            $registro->numero_registro,
            $registro->tipo_formato,
            $registro->empresa_id,
            $registro->estado,
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tipo_formato' => $schema->string()
                ->enum(array_column(TipoFormato::cases(), 'value'))
                ->description('Código del formato de seguridad: F01–F13 (ver formatos-registro).')
                ->required(),
            'datos' => $schema->object()
                ->description('Objeto con los campos del formato (consulta formatos-registro para saber cuáles). Fechas en YYYY-MM-DD, fecha/hora en YYYY-MM-DD HH:MM.')
                ->required(),
            'empresa_id' => $schema->integer()
                ->description('Solo para super admin: ID de la empresa destino. Los demás roles lo ignoran (usan su propia empresa).'),
        ];
    }
}
