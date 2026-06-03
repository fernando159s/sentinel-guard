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
     * ¿Puede ver/descifrar credenciales? Requiere el permiso explicito
     * `ver_credenciales` o ser el responsable de la cuenta.
     */
    public function view(User $user, ActivoDigitalCredencial $credencial): bool
    {
        if ($user->can('ver_credenciales')) {
            return true;
        }

        return $credencial->activoDigital?->responsable_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return $this->gestiona($user) || $user->can('ver_credenciales');
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
