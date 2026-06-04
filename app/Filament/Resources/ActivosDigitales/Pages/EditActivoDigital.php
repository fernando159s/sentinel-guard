<?php

namespace App\Filament\Resources\ActivosDigitales\Pages;

use App\Filament\Resources\ActivosDigitales\ActivoDigitalResource;
use App\Support\ActivoDigitalPdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditActivoDigital extends EditRecord
{
    protected static string $resource = ActivoDigitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ficha_pdf')
                ->label('Ficha PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $this->record->load(['responsables', 'pagos.registradoPor']);
                    $bytes = ActivoDigitalPdf::ficha(Filament::getTenant(), $this->record);

                    return response()->streamDownload(
                        fn () => print ($bytes),
                        'ficha_'.$this->record->codigo_interno.'.pdf',
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
