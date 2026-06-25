<?php

namespace App\Services\Ingesta;

use App\Enums\TipoFormato;
use App\Models\Registro;
use App\Models\User;
use App\Services\Ingesta\Concerns\ResuelveEmpresa;
use App\Services\RegistroNumberService;
use App\Services\RegistroSchemaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Núcleo de ingesta de Registros de seguridad (los 13 formatos PSC). Valida el
 * array `datos` contra el esquema del formato (RegistroSchemaService), autoriza
 * por rol, resuelve el tenant, genera el número correlativo y persiste.
 * Agnóstico de canal: hoy lo usa el MCP; mañana el chat in-app.
 *
 * La auditoría la registra AuditableObserver; la notificación de incidencias
 * (F09) la dispara RegistroObserver — ambos automáticos al crear el modelo.
 */
class RegistroIngestaService
{
    use ResuelveEmpresa;

    /** Roles que pueden crear registros (solo_lectura y agente quedan fuera). */
    private const ROLES_GESTION = ['super_admin', 'admin_empresa', 'usuario'];

    /**
     * @param  array<string,mixed>  $datos
     *
     * @throws AuthorizationException si el rol no permite crear registros
     * @throws ValidationException si el formato o los datos son inválidos
     */
    public function crear(string $tipoFormato, array $datos, User $actor, ?int $empresaId = null): Registro
    {
        if (! $actor->hasRole(self::ROLES_GESTION)) {
            throw new AuthorizationException('Tu rol no permite crear registros de seguridad.');
        }

        $tipo = TipoFormato::tryFrom(strtoupper(trim($tipoFormato)));

        if (! $tipo) {
            throw ValidationException::withMessages([
                'tipo_formato' => 'Formato inválido. Usa uno de: '.implode(', ', array_column(TipoFormato::cases(), 'value')).'.',
            ]);
        }

        $empresaId = $this->resolverEmpresa($actor, $empresaId);

        $validado = Validator::make(
            ['datos' => $datos],
            RegistroSchemaService::rules($tipo),
        )->validate();

        return Registro::create([
            'empresa_id' => $empresaId,
            'tipo_formato' => $tipo->value,
            'numero_registro' => RegistroNumberService::generate($empresaId, $tipo),
            'datos' => $validado['datos'] ?? [],
            'estado' => 'activo',
            'creado_por' => $actor->id,
        ]);
    }
}
