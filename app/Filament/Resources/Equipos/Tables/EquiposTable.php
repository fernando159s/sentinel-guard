<?php

namespace App\Filament\Resources\Equipos\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EquiposTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_interno')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pc_escritorio' => 'info',
                        'laptop' => 'primary',
                        'impresora' => 'gray',
                        'servidor' => 'warning',
                        'usb' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pc_escritorio' => 'PC Escritorio',
                        'laptop' => 'Laptop',
                        'impresora' => 'Impresora',
                        'servidor' => 'Servidor',
                        'usb' => 'USB',
                        'otro' => 'Otro',
                        default => $state,
                    }),
                TextColumn::make('marca')
                    ->label('Marca / Modelo')
                    ->formatStateUsing(fn ($record): string => trim(($record->marca ?? '') . ' ' . ($record->modelo ?? '')) ?: '—')
                    ->searchable(['marca', 'modelo']),
                TextColumn::make('numero_serie')
                    ->label('N° Serie')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('asignacionVigente.user.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'mantenimiento' => 'warning',
                        'obsoleto' => 'gray',
                        'dado_de_baja' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'activo' => 'Activo',
                        'mantenimiento' => 'Mantenimiento',
                        'obsoleto' => 'Obsoleto',
                        'dado_de_baja' => 'Dado de baja',
                        default => $state,
                    }),
                TextColumn::make('ubicacion')
                    ->label('Ubicacion')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('nivel_sensibilidad')
                    ->label('Sensibilidad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publico' => 'gray',
                        'interno' => 'info',
                        'confidencial' => 'warning',
                        'sensible' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'pc_escritorio' => 'PC Escritorio',
                        'laptop' => 'Laptop',
                        'impresora' => 'Impresora',
                        'servidor' => 'Servidor',
                        'usb' => 'USB',
                        'otro' => 'Otro',
                    ]),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'mantenimiento' => 'Mantenimiento',
                        'obsoleto' => 'Obsoleto',
                        'dado_de_baja' => 'Dado de baja',
                    ]),
                SelectFilter::make('nivel_sensibilidad')
                    ->label('Sensibilidad')
                    ->options([
                        'publico' => 'Publico',
                        'interno' => 'Interno',
                        'confidencial' => 'Confidencial',
                        'sensible' => 'Sensible',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
