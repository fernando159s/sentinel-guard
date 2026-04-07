<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
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
                    ->sortable()
                    ->description(fn ($record) => $record->email),
                TextColumn::make('rol')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin_empresa' => 'Admin',
                        'agente_helpdesk' => 'Agente',
                        'solo_lectura' => 'Lectura',
                        default => 'Usuario',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin_empresa' => 'warning',
                        'agente_helpdesk' => 'primary',
                        'solo_lectura' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'activo' ? 'success' : 'danger'),
                TextColumn::make('empresa.razon_social')
                    ->label('Empresa')
                    ->limit(25)
                    ->toggleable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),
                TextColumn::make('puesto')
                    ->label('Puesto')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dni')
                    ->label('DNI')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo']),
            ])
            ->recordActions([
                Action::make('ver_equipo')
                    ->label('Equipo')
                    ->icon('heroicon-o-computer-desktop')
                    ->color('gray')
                    ->size('sm')
                    ->url(function ($record) {
                        $asignacion = $record->equiposAsignados()->with('equipo')->first();
                        if (! $asignacion?->equipo) {
                            return null;
                        }
                        $tenant = Filament::getTenant();

                        return "/admin/{$tenant->ruc}/equipos/{$asignacion->equipo->id}/edit";
                    })
                    ->visible(fn ($record) => $record->equiposAsignados()->exists()),
                Action::make('ver_tickets')
                    ->label('Tickets')
                    ->icon('heroicon-o-ticket')
                    ->color('gray')
                    ->size('sm')
                    ->url(function ($record) {
                        $tenant = Filament::getTenant();

                        return "/admin/{$tenant->ruc}/tickets?tableFilters[creado_por][value]={$record->id}";
                    }),
                EditAction::make()->label('')->icon('heroicon-o-pencil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
