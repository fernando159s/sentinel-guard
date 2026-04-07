<?php

namespace App\Filament\Resources\Capacitaciones\Pages;

use App\Filament\Resources\Capacitaciones\CapacitacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCapacitaciones extends ListRecords
{
    protected static string $resource = CapacitacionResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (auth()->user()?->hasRole(['super_admin', 'admin_empresa'])) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
}
