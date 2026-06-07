<?php

namespace App\Services\Ingesta\Concerns;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Resuelve la empresa destino de una ingesta respetando el tenant:
 *  - admin_empresa/usuario → siempre su propia empresa (no pueden elegir otra).
 *  - super_admin (sin empresa propia) → debe indicar un empresa_id válido.
 */
trait ResuelveEmpresa
{
    protected function resolverEmpresa(User $actor, ?int $empresaId): int
    {
        if ($actor->empresa_id) {
            return (int) $actor->empresa_id;
        }

        if (! $empresaId || ! Empresa::query()->whereKey($empresaId)->exists()) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Como super admin debes indicar un empresa_id válido (la empresa destino).',
            ]);
        }

        return (int) $empresaId;
    }
}
