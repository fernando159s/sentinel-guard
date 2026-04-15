<?php

namespace App\Filament\Resources\Empresas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrosRelationManager extends RelationManager
{
    protected static string $relationship = 'registros';

    protected static ?string $title = 'Registros recientes';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('numero_registro')
                    ->label('Número')
                    ->searchable(),
                TextColumn::make('tipo_formato')
                    ->label('Formato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('creador.name')
                    ->label('Creado por'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ]);
    }
}
