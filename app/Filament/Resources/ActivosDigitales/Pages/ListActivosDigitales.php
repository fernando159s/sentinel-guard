<?php

namespace App\Filament\Resources\ActivosDigitales\Pages;

use App\Filament\Resources\ActivosDigitales\ActivoDigitalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivosDigitales extends ListRecords
{
    protected static string $resource = ActivoDigitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
