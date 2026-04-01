<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Plantilla')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identificador')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('asunto')
                    ->label('Asunto')
                    ->limit(50)
                    ->searchable(),
                IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Última edición')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('nombre');
    }
}
