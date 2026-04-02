<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPoliticas extends ListRecords
{
    protected static string $resource = PoliticaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
