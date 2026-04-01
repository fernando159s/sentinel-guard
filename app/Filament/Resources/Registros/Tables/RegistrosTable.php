<?php

namespace App\Filament\Resources\Registros\Tables;

use App\Enums\TipoFormato;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegistrosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_registro')
                    ->label('N° Registro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_formato')
                    ->label('Formato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => TipoFormato::tryFrom($state)?->label() ?? $state),
                TextColumn::make('creador.name')
                    ->label('Creado por')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo_formato')
                    ->label('Formato')
                    ->options(TipoFormato::options()),
                SelectFilter::make('estado')
                    ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('exportPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $record->load('creador', 'empresa');
                        $path = PdfExportService::exportRegistro($record);

                        return response()->download($path)->deleteFileAfterSend();
                    }),
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function () use ($table) {
                        $query = $table->getQuery();
                        $registros = $query->with('creador')->limit(5000)->get();
                        $empresa = Filament::getTenant();
                        $path = ExcelExportService::exportRegistros($registros, $empresa?->razon_social);

                        return response()->download($path)->deleteFileAfterSend();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
