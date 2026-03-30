<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Super admin and helpdesk agents see all data
        if ($user->hasRole(['super_admin', 'agente_helpdesk'])) {
            return;
        }

        // Everyone else only sees their company's data
        if ($user->empresa_id) {
            $builder->where($model->getTable() . '.empresa_id', $user->empresa_id);
        }
    }
}
