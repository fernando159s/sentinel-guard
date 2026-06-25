<?php

namespace App\Services\Ingesta;

use App\Models\Equipo;
use App\Models\EquipoAsignacion;
use App\Models\User;
use App\Services\Ingesta\Concerns\ResuelveEmpresa;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Núcleo de ingesta de Equipos (inventario de PCs/hardware/soportes). Valida,
 * autoriza por rol, resuelve el tenant, genera el código interno (EQ-XXX) y
 * registra el asiento de "ingreso_nuevo" en el historial. Agnóstico de canal.
 *
 * La auditoría la registra AuditableObserver al crear el modelo.
 */
class EquipoIngestaService
{
    use ResuelveEmpresa;

    private const ROLES_GESTION = ['super_admin', 'admin_empresa'];

    private const TIPOS = [
        'pc_escritorio', 'laptop', 'impresora', 'servidor', 'usb', 'disco_externo',
        'telefono', 'tablet', 'dispositivo_red', 'dvd_cd', 'expediente_fisico', 'soporte_nube', 'otro',
    ];

    private const ESTADOS = ['activo', 'mantenimiento', 'obsoleto', 'dado_de_baja'];

    private const CATEGORIAS = ['tecnologico', 'no_tecnologico'];

    private const SENSIBILIDAD = ['publico', 'interno', 'confidencial', 'sensible'];

    private const CLASIFICACION = ['hdd_interno', 'hdd_externo', 'usb', 'servidor', 'nube', 'dvd', 'expediente_fisico', 'otro'];

    /**
     * @param  array<string,mixed>  $payload
     *
     * @throws AuthorizationException si el rol no permite gestionar equipos
     * @throws ValidationException si el payload es inválido
     */
    public function crear(array $payload, User $actor): Equipo
    {
        if (! $actor->hasRole(self::ROLES_GESTION)) {
            throw new AuthorizationException('Tu rol no permite registrar equipos (requiere admin de empresa o super admin).');
        }

        $empresaId = $this->resolverEmpresa($actor, isset($payload['empresa_id']) ? (int) $payload['empresa_id'] : null);

        $data = Validator::make($payload, [
            'tipo' => ['required', Rule::in(self::TIPOS)],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255', Rule::unique('equipos', 'numero_serie')],
            'codigo_interno' => ['nullable', 'string', 'max:255'],
            'categoria' => ['nullable', Rule::in(self::CATEGORIAS)],
            'clasificacion_soporte' => ['nullable', Rule::in(self::CLASIFICACION)],
            'contenido_datos' => ['nullable', 'string'],
            'sistema_operativo' => ['nullable', 'string', 'max:255'],
            'procesador' => ['nullable', 'string', 'max:255'],
            'ram_gb' => ['nullable', 'integer', 'min:0'],
            'disco_gb' => ['nullable', 'integer', 'min:0'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in(self::ESTADOS)],
            'nivel_sensibilidad' => ['nullable', Rule::in(self::SENSIBILIDAD)],
            'fecha_adquisicion' => ['nullable', 'date'],
            'fecha_garantia' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ])->validate();

        $data['empresa_id'] = $empresaId;
        $data['categoria'] ??= 'tecnologico';
        $data['estado'] ??= 'activo';
        $data['nivel_sensibilidad'] ??= 'interno';

        if (empty($data['codigo_interno'])) {
            $data['codigo_interno'] = $this->generarCodigoInterno($empresaId);
        }

        $equipo = Equipo::create($data);

        // Registra el ingreso en el historial (igual que CreateEquipo en Filament).
        EquipoAsignacion::create([
            'equipo_id' => $equipo->id,
            'user_id' => null,
            'tipo' => 'ingreso_nuevo',
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'asignado_por' => $actor->id,
        ]);

        return $equipo;
    }

    /** Siguiente correlativo EQ-XXX por empresa. */
    private function generarCodigoInterno(int $empresaId): string
    {
        $ultimo = Equipo::withTrashed()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('codigo_interno', 'like', 'EQ-%')
            ->orderByDesc('id')
            ->value('codigo_interno');

        $secuencia = $ultimo ? ((int) substr($ultimo, 3)) + 1 : 1;

        return 'EQ-'.str_pad((string) $secuencia, 3, '0', STR_PAD_LEFT);
    }
}
