<?php

namespace App\Filament\Resources\ActivosDigitales\Pages;

use App\Filament\Resources\ActivosDigitales\ActivoDigitalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditActivoDigital extends EditRecord
{
    protected static string $resource = ActivoDigitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
