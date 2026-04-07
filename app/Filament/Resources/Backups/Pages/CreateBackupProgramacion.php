<?php

namespace App\Filament\Resources\Backups\Pages;

use App\Filament\Resources\Backups\BackupProgramacionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBackupProgramacion extends CreateRecord
{
    protected static string $resource = BackupProgramacionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creado_por'] = auth()->id();

        return $data;
    }
}
