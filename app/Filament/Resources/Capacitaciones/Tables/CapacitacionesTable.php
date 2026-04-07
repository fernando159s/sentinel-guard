<?php

namespace App\Filament\Resources\Capacitaciones\Tables;

use App\Enums\ModalidadCapacitacion;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CapacitacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tema')
                    ->label('Tema')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hora_inicio')
                    ->label('Hora')
                    ->time('H:i'),
                TextColumn::make('duracion_minutos')
                    ->label('Duracion')
                    ->suffix(' min')
                    ->alignCenter(),
                TextColumn::make('modalidad')
                    ->label('Modalidad')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ModalidadCapacitacion ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ModalidadCapacitacion ? $state->color() : 'gray'),
                TextColumn::make('expositor')
                    ->label('Expositor')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('asistencia')
                    ->label('Asistencia')
                    ->getStateUsing(function ($record) {
                        $total = $record->asistencias()->count();
                        $asistieron = $record->asistencias()->where('asistio', true)->count();

                        return $total > 0 ? "{$asistieron}/{$total}" : '—';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $total = $record->asistencias()->count();
                        if ($total === 0) {
                            return 'gray';
                        }
                        $pct = $record->asistencias()->where('asistio', true)->count() / $total;

                        return $pct >= 0.8 ? 'success' : ($pct >= 0.5 ? 'warning' : 'danger');
                    }),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('modalidad')
                    ->options(collect(ModalidadCapacitacion::cases())
                        ->mapWithKeys(fn ($m) => [$m->value => $m->label()])),
            ])
            ->defaultSort('fecha', 'desc');
    }
}
