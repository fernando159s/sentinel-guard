<?php

namespace App\Filament\Resources\Equipos\Pages;

use App\Filament\Resources\Equipos\EquipoResource;
use App\Models\EquipoAsignacion;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipo extends CreateRecord
{
    protected static string $resource = EquipoResource::class;

    protected function afterCreate(): void
    {
        EquipoAsignacion::create([
            'equipo_id' => $this->record->id,
            'user_id' => null,
            'tipo' => 'ingreso_nuevo',
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'asignado_por' => auth()->id(),
        ]);
    }
}
