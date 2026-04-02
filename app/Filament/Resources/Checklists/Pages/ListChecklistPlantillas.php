<?php

namespace App\Filament\Resources\Checklists\Pages;

use App\Filament\Resources\Checklists\ChecklistPlantillaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChecklistPlantillas extends ListRecords
{
    protected static string $resource = ChecklistPlantillaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
