<?php

namespace App\Filament\Resources\Equipos\RelationManagers;

use App\Enums\EstadoActivoDigital;
use App\Enums\TipoActivoDigital;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivosDigitalesRelationManager extends RelationManager
{
    protected static string $relationship = 'activosDigitales';

    protected static ?string $title = 'Activos digitales vinculados';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_interno')->label('Codigo'),
                TextColumn::make('nombre')->label('Cuenta')->limit(30),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TipoActivoDigital $state): string => $state->label()),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (EstadoActivoDigital $state): string => $state->color())
                    ->formatStateUsing(fn (EstadoActivoDigital $state): string => $state->label()),
            ])
            ->emptyStateHeading('Sin activos digitales vinculados')
            ->emptyStateDescription('Vincula cuentas desde el modulo de Activos Digitales.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
