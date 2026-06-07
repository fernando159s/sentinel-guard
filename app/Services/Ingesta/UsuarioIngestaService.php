<?php

namespace App\Services\Ingesta;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Núcleo de ingesta de Usuarios. Es el más sensible: aplica guardas contra
 * escalación de privilegios y aislamiento por empresa.
 *
 *  - Solo super_admin y admin_empresa pueden crear usuarios.
 *  - admin_empresa solo puede asignar roles de empresa (no super_admin ni
 *    agente_helpdesk) y el usuario queda forzado a SU empresa.
 *  - super_admin puede asignar cualquier rol; para roles de empresa debe
 *    indicar empresa_id; los roles de staff (super_admin/agente) van sin empresa.
 *
 * El password nunca se devuelve: si no lo dan, se genera uno aleatorio y el
 * usuario debe establecerlo vía "Olvidé mi contraseña". La creación se audita
 * por AuditableObserver.
 */
class UsuarioIngestaService
{
    private const ROLES_GESTION = ['super_admin', 'admin_empresa'];

    /** Roles sin empresa (staff de plataforma). */
    private const ROLES_STAFF = ['super_admin', 'agente_helpdesk'];

    /**
     * @param  array<string,mixed>  $payload
     *
     * @throws AuthorizationException si el actor no puede crear usuarios
     * @throws ValidationException si los datos o el rol solicitado no son válidos
     */
    public function crear(array $payload, User $actor): User
    {
        if (! $actor->hasRole(self::ROLES_GESTION)) {
            throw new AuthorizationException('Tu rol no permite crear usuarios (requiere admin de empresa o super admin).');
        }

        $rolesPermitidos = $actor->hasRole('super_admin')
            ? ['super_admin', 'agente_helpdesk', 'admin_empresa', 'usuario', 'solo_lectura']
            : ['admin_empresa', 'usuario', 'solo_lectura'];

        $data = Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'rol' => ['required', Rule::in($rolesPermitidos)],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
            'password' => ['nullable', 'string', 'min:8'],
            'empresa_id' => ['nullable', 'integer'],
            'dni' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ], [
            'rol.in' => 'Tu rol no permite asignar ese rol al nuevo usuario.',
        ])->validate();

        $empresaId = $this->resolverEmpresaUsuario($data['rol'], $actor, isset($data['empresa_id']) ? (int) $data['empresa_id'] : null);

        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? Str::password(16),
            'empresa_id' => $empresaId,
            'rol' => $data['rol'],
            'estado' => $data['estado'] ?? 'activo',
            'dni' => $data['dni'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'puesto' => $data['puesto'] ?? null,
            'direccion' => $data['direccion'] ?? null,
        ]);

        $usuario->syncRoles($data['rol']);

        return $usuario;
    }

    /**
     * Resuelve la empresa del NUEVO usuario según su rol y el actor.
     */
    private function resolverEmpresaUsuario(string $rol, User $actor, ?int $empresaId): ?int
    {
        // Roles de staff de plataforma: sin empresa.
        if (in_array($rol, self::ROLES_STAFF, true)) {
            return null;
        }

        // admin_empresa: el nuevo usuario queda en SU empresa (no puede elegir otra).
        if (! $actor->hasRole('super_admin')) {
            return (int) $actor->empresa_id;
        }

        // super_admin creando un rol de empresa: debe indicar empresa_id válido.
        if (! $empresaId || ! Empresa::query()->whereKey($empresaId)->exists()) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Para un rol de empresa debes indicar un empresa_id válido.',
            ]);
        }

        return $empresaId;
    }
}
