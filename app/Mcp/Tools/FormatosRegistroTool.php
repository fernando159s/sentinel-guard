<?php

namespace App\Mcp\Tools;

use App\Enums\TipoFormato;
use App\Services\RegistroSchemaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('formatos-registro')]
#[Description(
    'Consulta los formatos de seguridad PSC (F01–F13). Sin argumentos lista los 13 con su nombre y para qué sirven. '.
    'Con tipo_formato=<código> devuelve los campos exactos de ese formato (obligatorios/opcionales y opciones válidas). '.
    'Úsalo antes de crear-registro para saber qué datos enviar.'
)]
class FormatosRegistroTool extends Tool
{
    public function handle(Request $request): Response
    {
        $arg = $request->get('tipo_formato');

        if ($arg) {
            $tipo = TipoFormato::tryFrom(strtoupper(trim((string) $arg)));

            if (! $tipo) {
                return Response::error('Formato inválido. Códigos válidos: F01 a F13.');
            }

            $lineas = ["Campos de {$tipo->label()} (prefijo {$tipo->prefix()}, {$tipo->psc()}):"];

            foreach (RegistroSchemaService::fields($tipo) as $campo) {
                $req = $campo['required'] ? 'OBLIGATORIO' : 'opcional';
                $opciones = $campo['options'] ? ' — opciones: '.implode(', ', $campo['options']) : '';
                $lineas[] = "- {$campo['name']} ({$req}, {$campo['type']}): {$campo['label']}{$opciones}";
            }

            $lineas[] = '';
            $lineas[] = 'Envía estos campos dentro del objeto "datos" en crear-registro. Fechas en YYYY-MM-DD (o YYYY-MM-DD HH:MM para fecha/hora).';

            return Response::text(implode("\n", $lineas));
        }

        $lineas = ['Formatos de seguridad PSC disponibles (usa el código en crear-registro):'];

        foreach (TipoFormato::cases() as $tipo) {
            $lineas[] = "- {$tipo->value} ({$tipo->prefix()}) {$tipo->label()}: {$tipo->description()}";
        }

        $lineas[] = '';
        $lineas[] = 'Llama de nuevo con tipo_formato=<código> para ver los campos exactos de un formato.';

        return Response::text(implode("\n", $lineas));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tipo_formato' => $schema->string()
                ->enum(array_column(TipoFormato::cases(), 'value'))
                ->description('Código del formato (F01–F13) para ver sus campos. Omítelo para listar los 13 formatos.'),
        ];
    }
}
