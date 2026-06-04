<?php

namespace App\Filament\Resources\ActivosDigitales\Tables;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivosDigitalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_interno')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TipoActivoDigital $state): string => $state->label())
                    ->icon(fn (TipoActivoDigital $state): string => $state->icon()),
                TextColumn::make('proveedor')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('modalidad_pago')
                    ->label('Pago')
                    ->badge()
                    ->color(fn (ModalidadPago $state): string => $state->color())
                    ->formatStateUsing(fn (ModalidadPago $state): string => $state->label()),
                TextColumn::make('costo')
                    ->label('Costo')
                    ->formatStateUsing(fn ($state, $record): string => $state ? $record->moneda . ' ' . number_format((float) $state, 2) : '—')
                    ->toggleable(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record): string => $record->estaVencido() ? 'danger' : ($record->porVencer(30) ? 'warning' : 'gray')),
                TextColumn::make('responsables.name')
                    ->label('Responsables')
                    ->badge()
                    ->separator(',')
                    ->placeholder('Sin asignar')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (EstadoActivoDigital $state): string => $state->color())
                    ->formatStateUsing(fn (EstadoActivoDigital $state): string => $state->label()),
                TextColumn::make('vinculo')
                    ->label('Vinculado a')
                    ->state(fn ($record): string => collect([
                        $record->registro?->numero_registro,
                        $record->equipo?->codigo_interno,
                    ])->filter()->implode(' · ') ?: '—')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
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
                    ->options(TipoActivoDigital::options()),
                SelectFilter::make('modalidad_pago')
                    ->label('Modalidad de pago')
                    ->options(ModalidadPago::options()),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoActivoDigital::options()),
                Filter::make('por_vencer')
                    ->label('Por vencer (30 dias)')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query
                        ->whereIn('modalidad_pago', [ModalidadPago::Mensual->value, ModalidadPago::Anual->value])
                        ->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))),
                Filter::make('vinculados')
                    ->label('Solo vinculados a PSC/equipo')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query
                        ->where(fn (Builder $q) => $q->whereNotNull('registro_id')->orWhereNotNull('equipo_id'))),
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
