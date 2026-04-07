<?php

namespace App\Filament\Resources\Politicas\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class VersionesRelationManager extends RelationManager
{
    protected static string $relationship = 'versiones';

    protected static ?string $title = 'Historial de versiones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('creador.name')
                    ->label('Modificado por')
                    ->placeholder('—'),
                IconColumn::make('archivo_path')
                    ->label('Archivo')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : 'heroicon-o-x-mark')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('descargar')
                    ->label('Descargar')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->archivo_path))
                    ->action(fn ($record) => Storage::disk('local')->download(
                        $record->archivo_path,
                        $record->archivo_nombre ?? 'documento'
                    )),
            ])
            ->heading('Versiones anteriores')
            ->emptyStateHeading('Sin versiones anteriores')
            ->emptyStateDescription('Se creara un registro cada vez que se incremente la version.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
