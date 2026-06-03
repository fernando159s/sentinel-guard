<?php

namespace App\Policies;

use App\Models\ActivoDigitalCredencial;
use App\Models\User;

class ActivoDigitalCredencialPolicy
{
    /**
     * Solo super_admin y admin_empresa gestionan el modulo de activos digitales.
     */
    private function gestiona(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_empresa']);
    }

    /**
     * ¿Puede ver/descifrar credenciales?
     * - super_admin: siempre (llave maestra)
     * - admin_empresa: todas las cuentas de su empresa
     * - cualquier usuario: si esta en la lista de responsables de la cuenta
     */
    public function view(User $user, ActivoDigitalCredencial $credencial): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $activo = $credencial->activoDigital;

        if (! $activo) {
            return false;
        }

        if ($user->hasRole('admin_empresa') && $activo->empresa_id === $user->empresa_id) {
            return true;
        }

        return $activo->esResponsable($user);
    }

    public function viewAny(User $user): bool
    {
        // El acceso real se decide por cuenta en view().
        return true;
    }

    public function create(User $user): bool
    {
        return $this->gestiona($user);
    }

    public function update(User $user, ActivoDigitalCredencial $credencial): bool
    {
        return $this->gestiona($user);
    }

    public function delete(User $user, ActivoDigitalCredencial $credencial): bool
    {
        return $this->gestiona($user);
    }
}
