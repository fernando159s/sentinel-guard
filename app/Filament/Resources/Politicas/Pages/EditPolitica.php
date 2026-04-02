<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPolitica extends EditRecord
{
    protected static string $resource = PoliticaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
