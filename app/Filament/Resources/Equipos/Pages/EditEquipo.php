<?php

namespace App\Filament\Resources\Equipos\Pages;

use App\Filament\Resources\Equipos\EquipoResource;
use App\Models\EquipoAsignacion;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditEquipo extends EditRecord
{
    protected static string $resource = EquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('asignar')
                ->label('Asignar')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('Asignar a')
                        ->options(fn () => User::where('empresa_id', Filament::getTenant()?->id)
                            ->where('estado', 'activo')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('condicion_entrega')
                        ->label('Condicion del equipo al entregar')
                        ->options(['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'])
                        ->required(),
                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(2)
                        ->placeholder('Accesorios entregados, observaciones...'),
                ])
                ->action(function (array $data) {
                    EquipoAsignacion::create([
                        'equipo_id' => $this->record->id,
                        'user_id' => $data['user_id'],
                        'tipo' => 'asignacion',
                        'fecha_inicio' => now(),
                        'condicion_entrega' => $data['condicion_entrega'],
                        'notas' => $data['notas'] ?? null,
                        'asignado_por' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Equipo asignado')
                        ->body('El equipo fue asignado a ' . User::find($data['user_id'])?->name)
                        ->success()
                        ->send();
                })
                ->visible(fn () => ! $this->record->estaAsignado() && $this->record->estado === 'activo'),

            Action::make('transferir')
                ->label('Transferir')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('nuevo_user_id')
                        ->label('Transferir a')
                        ->options(fn () => User::where('empresa_id', Filament::getTenant()?->id)
                            ->where('estado', 'activo')
                            ->where('id', '!=', $this->record->asignacionVigente?->user_id)
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('condicion_devolucion')
                        ->label('Condicion al recoger del usuario anterior')
                        ->options(['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'])
                        ->required(),
                    Select::make('condicion_entrega')
                        ->label('Condicion al entregar al nuevo usuario')
                        ->options(['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'])
                        ->required(),
                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $vigente = $this->record->asignacionVigente;
                        if ($vigente) {
                            $vigente->update([
                                'fecha_fin' => now(),
                                'condicion_devolucion' => $data['condicion_devolucion'],
                            ]);
                        }

                        EquipoAsignacion::create([
                            'equipo_id' => $this->record->id,
                            'user_id' => $data['nuevo_user_id'],
                            'tipo' => 'transferencia',
                            'fecha_inicio' => now(),
                            'condicion_entrega' => $data['condicion_entrega'],
                            'notas' => $data['notas'] ?? null,
                            'asignado_por' => auth()->id(),
                        ]);
                    });

                    Notification::make()
                        ->title('Equipo transferido')
                        ->body('Transferido a ' . User::find($data['nuevo_user_id'])?->name)
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->estaAsignado()),

            Action::make('devolver')
                ->label('Devolver')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Devolver equipo')
                ->modalDescription('El equipo quedara libre y sin usuario asignado.')
                ->form([
                    Select::make('condicion_devolucion')
                        ->label('Condicion del equipo al devolver')
                        ->options(['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'])
                        ->required(),
                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $vigente = $this->record->asignacionVigente;
                    if ($vigente) {
                        $vigente->update([
                            'fecha_fin' => now(),
                            'condicion_devolucion' => $data['condicion_devolucion'],
                        ]);
                    }

                    EquipoAsignacion::create([
                        'equipo_id' => $this->record->id,
                        'user_id' => $vigente?->user_id ?? auth()->id(),
                        'tipo' => 'devolucion',
                        'fecha_inicio' => now(),
                        'fecha_fin' => now(),
                        'condicion_entrega' => $data['condicion_devolucion'],
                        'notas' => $data['notas'] ?? null,
                        'asignado_por' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Equipo devuelto')
                        ->body('El equipo esta disponible para asignar.')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->estaAsignado()),

            DeleteAction::make()->label('Desactivar'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
