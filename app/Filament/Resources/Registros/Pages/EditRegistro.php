<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Filament\Resources\Registros\RegistroResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistro extends EditRecord
{
    protected static string $resource = RegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Desactivar'),
            RestoreAction::make()->label('Restaurar'),
            ForceDeleteAction::make()->label('Eliminar permanente'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['modificado_por'] = auth()->id();

        return $data;
    }
}
