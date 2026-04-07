<?php

namespace App\Filament\Resources\Capacitaciones\Pages;

use App\Filament\Resources\Capacitaciones\CapacitacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCapacitaciones extends ListRecords
{
    protected static string $resource = CapacitacionResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
