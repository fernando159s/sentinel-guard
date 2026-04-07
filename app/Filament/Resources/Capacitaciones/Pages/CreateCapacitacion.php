<?php

namespace App\Filament\Resources\Capacitaciones\Pages;

use App\Filament\Resources\Capacitaciones\CapacitacionResource;
use App\Models\CapacitacionAsistencia;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCapacitacion extends CreateRecord
{
    protected static string $resource = CapacitacionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = Filament::getTenant()->id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $empresaId = Filament::getTenant()->id;

        $userIds = User::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->pluck('id');

        foreach ($userIds as $userId) {
            CapacitacionAsistencia::create([
                'capacitacion_id' => $this->record->id,
                'user_id' => $userId,
                'asistio' => false,
            ]);
        }
    }
}
