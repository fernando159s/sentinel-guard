<?php

namespace App\Filament\Resources\Checklists\Pages;

use App\Filament\Resources\Checklists\ChecklistPlantillaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChecklistPlantilla extends EditRecord
{
    protected static string $resource = ChecklistPlantillaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
