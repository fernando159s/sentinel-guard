<?php

namespace App\Mcp\Tools;

use App\Enums\EstadoActivoDigital;
use App\Enums\TipoActivoDigital;
use App\Services\Ingesta\ActivoDigitalConsultaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('consultar_activos_digitales')]
#[Description(
    'Consulta (lista/busca) los activos digitales ya registrados en SecuriForm para la empresa del '.
    'usuario autenticado: cuentas WhatsApp/Meta, suscripciones SaaS, dominios, licencias, etc. '.
    'Permite filtrar por tipo, estado, texto o por vencimientos próximos. NUNCA devuelve credenciales.'
)]
class ConsultarActivosDigitalesTool extends Tool
{
    public function handle(Request $request, ActivoDigitalConsultaService $service): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('No autenticado. Configura un token de API válido para usar esta herramienta.');
        }

        $resultado = $service->buscar($request->all(), $user);

        if ($resultado['total'] === 0) {
            return Response::text('No se encontraron activos digitales con esos criterios.');
        }

        $lineas = collect($resultado['activos'])->map(function (array $a): string {
            $extra = collect([
                $a['proveedor'] ? 'proveedor: '.$a['proveedor'] : null,
                $a['costo'] ? 'costo: '.$a['costo'].' ('.$a['modalidad_pago'].')' : null,
                $a['vencimiento'] ? 'vence: '.$a['vencimiento'] : null,
                $a['empresa'] ? 'empresa: '.$a['empresa'] : null,
            ])->filter()->implode(' · ');

            return sprintf(
                '• %s — %s [%s · %s]%s',
                $a['codigo'],
                $a['nombre'],
                $a['tipo'],
                $a['estado'],
                $extra !== '' ? ' · '.$extra : '',
            );
        });

        return Response::text(
            "Se encontraron {$resultado['total']} activo(s) digital(es):\n".$lineas->implode("\n")
        );
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'texto' => $schema->string()
                ->description('Búsqueda libre por nombre, proveedor, código interno o identificador.'),
            'tipo' => $schema->string()
                ->enum(array_column(TipoActivoDigital::cases(), 'value'))
                ->description('Filtra por tipo: whatsapp, meta, suscripcion_saas, dominio, licencia_unica, correo, redes_sociales, otro.'),
            'estado' => $schema->string()
                ->enum(array_column(EstadoActivoDigital::cases(), 'value'))
                ->description('Filtra por estado: activo, suspendido, vencido, cancelado.'),
            'solo_por_vencer' => $schema->boolean()
                ->description('Si es true, solo devuelve activos vencidos o por vencer dentro de los próximos días.'),
            'dias_vencimiento' => $schema->integer()
                ->description('Ventana en días para "solo_por_vencer". Por defecto 30.'),
            'limite' => $schema->integer()
                ->description('Máximo de resultados (1-50). Por defecto 20.'),
            'empresa_id' => $schema->integer()
                ->description('Solo para super admin: acota la consulta a una empresa concreta. Los demás usan su propia empresa.'),
        ];
    }
}
