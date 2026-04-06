<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Filament\Resources\Registros\RegistroResource;
use App\Services\ExcelExportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListRegistros extends ListRecords
{
    protected static string $resource = RegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn () => auth()->user()?->hasRole(['super_admin', 'admin_empresa']))
                ->action(function () {
                    $query = $this->getFilteredTableQuery();
                    $registros = $query->with('creador')->limit(5000)->get();
                    $empresa = Filament::getTenant();
                    $path = ExcelExportService::exportRegistros($registros, $empresa?->razon_social);

                    return response()->download($path)->deleteFileAfterSend();
                }),
            CreateAction::make(),
        ];
    }
}
