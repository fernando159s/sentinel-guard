<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ticket')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asunto')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('empresa.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->toggleable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),
                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'consulta' => 'info',
                        'problema_tecnico' => 'warning',
                        'error_registro' => 'danger',
                        'solicitud_acceso' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baja' => 'gray',
                        'media' => 'info',
                        'alta' => 'warning',
                        'urgente' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nuevo' => 'info',
                        'en_revision' => 'warning',
                        'esperando_usuario' => 'gray',
                        'resuelto' => 'success',
                        'cerrado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nuevo' => 'Nuevo',
                        'en_revision' => 'En revisión',
                        'esperando_usuario' => 'Esperando usuario',
                        'resuelto' => 'Resuelto',
                        'cerrado' => 'Cerrado',
                        default => $state,
                    }),
                TextColumn::make('agente.name')
                    ->label('Agente')
                    ->placeholder('Sin asignar')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'nuevo' => 'Nuevo',
                        'en_revision' => 'En revisión',
                        'esperando_usuario' => 'Esperando usuario',
                        'resuelto' => 'Resuelto',
                        'cerrado' => 'Cerrado',
                    ]),
                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'urgente' => 'Urgente',
                    ]),
                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'consulta' => 'Consulta',
                        'problema_tecnico' => 'Problema técnico',
                        'error_registro' => 'Error en registro',
                        'solicitud_acceso' => 'Solicitud de acceso',
                        'otro' => 'Otro',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
