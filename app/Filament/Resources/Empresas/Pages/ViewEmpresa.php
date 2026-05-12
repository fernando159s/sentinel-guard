<?php

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewEmpresa extends ViewRecord
{
    protected static string $resource = EmpresaResource::class;

    public function getTitle(): string
    {
        return $this->record->razon_social;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Métricas')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('registros_mes')
                            ->label('Registros este mes')
                            ->state(fn () => Registro::withoutGlobalScopes()
                                ->where('empresa_id', $this->record->id)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count()
                            )
                            ->badge()
                            ->color('success'),
                        TextEntry::make('tickets_abiertos')
                            ->label('Tickets abiertos')
                            ->state(fn () => Ticket::withoutGlobalScopes()
                                ->where('empresa_id', $this->record->id)
                                ->whereNotIn('estado', ['resuelto', 'cerrado'])
                                ->count()
                            )
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                        TextEntry::make('usuarios_activos')
                            ->label('Usuarios activos')
                            ->state(fn () => User::withoutGlobalScopes()
                                ->where('empresa_id', $this->record->id)
                                ->where('estado', 'activo')
                                ->count()
                            )
                            ->badge()
                            ->color('info'),
                        TextEntry::make('ultimo_acceso')
                            ->label('Último acceso')
                            ->state(fn () => User::withoutGlobalScopes()
                                ->where('empresa_id', $this->record->id)
                                ->whereNotNull('ultimo_acceso')
                                ->max('ultimo_acceso')
                            )
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Nunca'),
                    ]),

                Section::make('Datos de la empresa')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        ImageEntry::make('logo_path')
                            ->label('Logo sistema')
                            ->disk('logos')
                            ->height(60)
                            ->defaultImageUrl(url('/images/placeholder.png')),
                        ImageEntry::make('logo_documentos_path')
                            ->label('Logo documentos')
                            ->disk('logos')
                            ->height(60)
                            ->placeholder('—'),
                        TextEntry::make('ruc')
                            ->label('RUC'),
                        TextEntry::make('razon_social')
                            ->label('Razón social'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('telefono')
                            ->label('Teléfono')
                            ->placeholder('—'),
                        TextEntry::make('direccion')
                            ->label('Dirección')
                            ->placeholder('—'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'activo' => 'success',
                                'inactivo' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime('d/m/Y'),
                    ]),

                Section::make('Actividad mensual (últimos 6 meses)')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('actividad_mensual')
                            ->label('')
                            ->state(function () {
                                $meses = collect();
                                for ($i = 5; $i >= 0; $i--) {
                                    $fecha = now()->subMonths($i);
                                    $registros = Registro::withoutGlobalScopes()
                                        ->where('empresa_id', $this->record->id)
                                        ->whereMonth('created_at', $fecha->month)
                                        ->whereYear('created_at', $fecha->year)
                                        ->count();
                                    $tickets = Ticket::withoutGlobalScopes()
                                        ->where('empresa_id', $this->record->id)
                                        ->whereMonth('created_at', $fecha->month)
                                        ->whereYear('created_at', $fecha->year)
                                        ->count();
                                    $meses->push($fecha->translatedFormat('M Y') . ": {$registros} registros, {$tickets} tickets");
                                }
                                return $meses->implode(' | ');
                            }),
                    ]),
            ]);
    }
}
