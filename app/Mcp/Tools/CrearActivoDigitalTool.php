<?php

namespace App\Mcp\Tools;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Services\Ingesta\ActivoDigitalIngestaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('crear-activo-digital')]
#[Description(
    'Registra un nuevo activo digital en SecuriForm (cuenta WhatsApp/Meta, suscripción SaaS, '.
    'dominio, licencia, etc.) para la empresa del usuario autenticado. NO maneja credenciales de acceso.'
)]
class CrearActivoDigitalTool extends Tool
{
    public function handle(Request $request, ActivoDigitalIngestaService $service): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('No autenticado. Configura un token de API válido para usar esta herramienta.');
        }

        try {
            $activo = $service->crear($request->all(), $user);
        } catch (AuthorizationException $e) {
            return Response::error($e->getMessage());
        } catch (ValidationException $e) {
            return Response::error('No se pudo crear el activo: '.collect($e->errors())->flatten()->implode(' '));
        }

        return Response::text(sprintf(
            '✅ Activo digital creado: %s — "%s" (%s · %s) para la empresa #%d. Estado: %s. Vencimiento: %s.',
            $activo->codigo_interno,
            $activo->nombre,
            $activo->tipo->label(),
            $activo->modalidad_pago->label(),
            $activo->empresa_id,
            $activo->estado->label(),
            $activo->fecha_vencimiento?->format('d/m/Y') ?? 'sin vencimiento',
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'nombre' => $schema->string()
                ->description('Nombre de la cuenta o activo, ej. "WhatsApp Ventas", "Google Workspace".')
                ->required(),
            'tipo' => $schema->string()
                ->enum(array_column(TipoActivoDigital::cases(), 'value'))
                ->description('Tipo: whatsapp, meta, suscripcion_saas, dominio, licencia_unica, correo, redes_sociales, otro.')
                ->required(),
            'proveedor' => $schema->string()
                ->description('Proveedor del servicio, ej. Meta, Google, Adobe, GoDaddy.'),
            'url' => $schema->string()
                ->description('URL del panel de acceso, ej. https://business.facebook.com.'),
            'identificador' => $schema->string()
                ->description('Cómo se identifica la cuenta en el proveedor: nro de teléfono, email o business ID.'),
            'modalidad_pago' => $schema->string()
                ->enum(array_column(ModalidadPago::cases(), 'value'))
                ->description('Modalidad: mensual, anual, pago_unico, gratuito. Por defecto mensual.'),
            'costo' => $schema->number()
                ->description('Costo del periodo (>= 0). Omitir si es gratuito.'),
            'moneda' => $schema->string()
                ->enum(['PEN', 'USD', 'EUR'])
                ->description('Moneda del costo. Por defecto PEN.'),
            'metodo_pago' => $schema->string()
                ->description('Método de pago, ej. "Visa ***1234", transferencia, PayPal.'),
            'renovacion_automatica' => $schema->boolean()
                ->description('Si la suscripción se renueva automáticamente.'),
            'estado' => $schema->string()
                ->enum(array_column(EstadoActivoDigital::cases(), 'value'))
                ->description('Estado: activo, suspendido, vencido, cancelado. Por defecto activo.'),
            'nivel_sensibilidad' => $schema->string()
                ->enum(['publico', 'interno', 'confidencial', 'sensible'])
                ->description('Nivel de sensibilidad de la información. Por defecto interno.'),
            'fecha_adquisicion' => $schema->string()
                ->description('Fecha de adquisición en formato YYYY-MM-DD.'),
            'fecha_vencimiento' => $schema->string()
                ->description('Próximo vencimiento/renovación (YYYY-MM-DD). Solo aplica a mensual/anual.'),
            'observaciones' => $schema->string()
                ->description('Notas adicionales sobre el activo.'),
            'empresa_id' => $schema->integer()
                ->description('Solo para super admin: ID de la empresa destino. Los admin de empresa lo ignoran (usan su propia empresa).'),
        ];
    }
}
