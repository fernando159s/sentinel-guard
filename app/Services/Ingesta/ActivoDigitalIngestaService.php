<?php

namespace App\Services\Ingesta;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Models\ActivoDigital;
use App\Models\User;
use App\Services\Ingesta\Concerns\ResuelveEmpresa;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Núcleo de ingesta de Activos Digitales: valida, autoriza y persiste un
 * activo respetando el tenant del actor. Es agnóstico del canal — hoy lo
 * usa el servidor MCP; mañana el chat in-app puede reusar el mismo método.
 *
 * La auditoría la registra automáticamente AuditableObserver (ver
 * AppServiceProvider), por lo que aquí no se llama a AuditService.
 */
class ActivoDigitalIngestaService
{
    use ResuelveEmpresa;

    /** Roles autorizados a gestionar (crear) activos digitales. */
    private const ROLES_GESTION = ['super_admin', 'admin_empresa'];

    /**
     * @param  array<string,mixed>  $payload
     *
     * @throws AuthorizationException si el rol del actor no permite gestionar activos
     * @throws ValidationException si el payload es inválido o falta la empresa destino
     */
    public function crear(array $payload, User $actor): ActivoDigital
    {
        if (! $actor->hasRole(self::ROLES_GESTION)) {
            throw new AuthorizationException(
                'Tu rol no permite registrar activos digitales (requiere admin de empresa o super admin).'
            );
        }

        $empresaId = $this->resolverEmpresa($actor, isset($payload['empresa_id']) ? (int) $payload['empresa_id'] : null);

        $data = $this->validar($payload);
        $data['empresa_id'] = $empresaId;

        return ActivoDigital::create($data);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    private function validar(array $payload): array
    {
        $data = Validator::make($payload, [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::enum(TipoActivoDigital::class)],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'identificador' => ['nullable', 'string', 'max:255'],
            'modalidad_pago' => ['nullable', Rule::enum(ModalidadPago::class)],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', 'in:PEN,USD,EUR'],
            'metodo_pago' => ['nullable', 'string', 'max:255'],
            'renovacion_automatica' => ['nullable', 'boolean'],
            'estado' => ['nullable', Rule::enum(EstadoActivoDigital::class)],
            'nivel_sensibilidad' => ['nullable', 'in:publico,interno,confidencial,sensible'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ])->validate();

        // Defaults coherentes con el formulario de Filament (ActivoDigitalForm).
        $data['modalidad_pago'] ??= ModalidadPago::Mensual->value;
        $data['moneda'] ??= 'PEN';
        $data['estado'] ??= EstadoActivoDigital::Activo->value;
        $data['nivel_sensibilidad'] ??= 'interno';

        return $data;
    }
}
