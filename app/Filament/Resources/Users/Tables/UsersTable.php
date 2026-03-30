<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('empresa.razon_social')
                    ->label('Empresa')
                    ->sortable()
                    ->limit(30)
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),
                TextColumn::make('rol')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin_empresa' => 'warning',
                        'usuario' => 'info',
                        'agente_helpdesk' => 'primary',
                        'solo_lectura' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                    }),
                TextColumn::make('ultimo_acceso')
                    ->label('Último acceso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Nunca'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rol')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin_empresa' => 'Admin Empresa',
                        'usuario' => 'Usuario',
                        'agente_helpdesk' => 'Agente Helpdesk',
                        'solo_lectura' => 'Solo Lectura',
                    ]),
                SelectFilter::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
