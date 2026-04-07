<?php

namespace App\Filament\Resources\Backups\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BackupProgramacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipo.codigo_interno')
                    ->label('Equipo')
                    ->placeholder('General')
                    ->badge()
                    ->color('info'),
                TextColumn::make('periodicidad')
                    ->label('Periodicidad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diaria' => 'danger',
                        'semanal' => 'warning',
                        'quincenal' => 'warning',
                        'mensual' => 'info',
                        'trimestral' => 'gray',
                        'puntual' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('proximo_backup')
                    ->label('Proximo backup')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record): string {
                        if (! $record->activo) {
                            return 'gray';
                        }
                        if ($record->proximo_backup->isPast()) {
                            return 'danger';
                        }
                        if ($record->proximo_backup->isToday() || $record->proximo_backup->isTomorrow()) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->weight('bold'),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('ejecuciones_count')
                    ->label('Ejecuciones')
                    ->counts('ejecuciones')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('proximo_backup', 'asc')
            ->filters([
                SelectFilter::make('activo')
                    ->options(['1' => 'Activos', '0' => 'Inactivos']),
                SelectFilter::make('periodicidad')
                    ->options([
                        'diaria' => 'Diaria',
                        'semanal' => 'Semanal',
                        'quincenal' => 'Quincenal',
                        'mensual' => 'Mensual',
                        'trimestral' => 'Trimestral',
                        'puntual' => 'Puntual',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
