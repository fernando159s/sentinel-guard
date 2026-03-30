<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        AuditService::log(
            accion: 'crear',
            entidad: $model->getTable(),
            entidadId: $model->getKey(),
            datosNuevos: $model->getAttributes(),
        );
    }

    public function updated(Model $model): void
    {
        $changed = $model->getChanges();
        unset($changed['updated_at']);

        if (empty($changed)) {
            return;
        }

        AuditService::log(
            accion: 'editar',
            entidad: $model->getTable(),
            entidadId: $model->getKey(),
            datosAnteriores: array_intersect_key($model->getOriginal(), $changed),
            datosNuevos: $changed,
        );
    }

    public function deleted(Model $model): void
    {
        AuditService::log(
            accion: $model->isForceDeleting() ? 'eliminar' : 'desactivar',
            entidad: $model->getTable(),
            entidadId: $model->getKey(),
            datosAnteriores: $model->getAttributes(),
        );
    }

    public function restored(Model $model): void
    {
        AuditService::log(
            accion: 'restaurar',
            entidad: $model->getTable(),
            entidadId: $model->getKey(),
        );
    }
}
