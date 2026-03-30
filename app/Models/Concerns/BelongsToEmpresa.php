<?php

namespace App\Models\Concerns;

use App\Models\Scopes\EmpresaScope;

trait BelongsToEmpresa
{
    protected static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope(new EmpresaScope);

        // Auto-set empresa_id on create from authenticated user
        static::creating(function ($model) {
            if (! $model->empresa_id && auth()->check() && auth()->user()->empresa_id) {
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });
    }
}
