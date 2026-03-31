<?php

namespace App\Filament\Resources\Empresas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuarios';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
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
                    ->placeholder('Nunca'),
            ]);
    }
}
