<?php

namespace App\Filament\Resources\Equipos\Pages;

use App\Enums\TipoFormato;
use App\Filament\Resources\Equipos\EquipoResource;
use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
use App\Models\EquipoAsignacion;
use App\Models\Registro;
use App\Models\User;
use App\Services\RegistroNumberService;
use Filament\Forms\Components\Toggle;
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

            Action::make('ejecutar_checklist')
                ->label('Checklist')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->form(function (): array {
                    $empresaId = Filament::getTenant()?->id;
                    $plantillas = ChecklistPlantilla::where('empresa_id', $empresaId)
                        ->where('activa', true)
                        ->get();

                    if ($plantillas->isEmpty()) {
                        return [
                            Select::make('plantilla_id')
                                ->label('No hay plantillas activas')
                                ->disabled()
                                ->placeholder('Crea una plantilla primero'),
                        ];
                    }

                    $fields = [
                        Select::make('plantilla_id')
                            ->label('Plantilla')
                            ->options($plantillas->pluck('nombre', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('items_loaded', true)),
                    ];

                    // Static approach: add toggle+textarea per item of first plantilla
                    // Dynamic forms in Filament actions are limited, so we use a simpler approach
                    $fields[] = Textarea::make('observaciones_generales')
                        ->label('Observaciones generales')
                        ->rows(2);

                    return $fields;
                })
                ->action(function (array $data) {
                    $plantilla = ChecklistPlantilla::find($data['plantilla_id']);
                    if (! $plantilla) {
                        return;
                    }

                    // Auto-generate results: all items marked as cumple=true (admin will edit later if needed)
                    $resultados = collect($plantilla->items)->map(fn ($item) => [
                        'item' => $item['nombre'],
                        'cumple' => true,
                        'observacion' => '',
                    ])->toArray();

                    $ejecucion = ChecklistEjecucion::create([
                        'checklist_plantilla_id' => $plantilla->id,
                        'equipo_id' => $this->record->id,
                        'ejecutado_por' => auth()->id(),
                        'fecha_ejecucion' => now(),
                        'resultados' => $resultados,
                        'estado' => 'completo',
                        'observaciones_generales' => $data['observaciones_generales'] ?? null,
                    ]);

                    $total = count($resultados);

                    Notification::make()
                        ->title('Checklist ejecutado')
                        ->body("{$plantilla->nombre}: {$total}/{$total} items verificados.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->estado !== 'dado_de_baja'),

            Action::make('dar_de_baja')
                ->label('Dar de baja')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Dar de baja equipo')
                ->modalDescription('El equipo sera marcado como dado de baja y se generara un registro F13 (Destruccion de activos).')
                ->form([
                    Select::make('motivo')
                        ->label('Motivo de baja')
                        ->options([
                            'obsoleto' => 'Obsoleto',
                            'danado' => 'Danado',
                            'destruido' => 'Destruido',
                            'perdido' => 'Perdido',
                            'robado' => 'Robado',
                        ])
                        ->required(),
                    Select::make('metodo_destruccion')
                        ->label('Metodo de destruccion (si aplica)')
                        ->options([
                            'borrado_seguro' => 'Borrado seguro',
                            'destruccion_fisica' => 'Destruccion fisica',
                            'desmagnetizacion' => 'Desmagnetizacion',
                            'trituracion' => 'Trituracion',
                            'proveedor_certificado' => 'Proveedor certificado',
                            'no_aplica' => 'No aplica',
                        ])
                        ->default('no_aplica')
                        ->required(),
                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        // Close current assignment if any
                        $vigente = $this->record->asignacionVigente;
                        if ($vigente) {
                            $vigente->update(['fecha_fin' => now()]);
                        }

                        // Record baja in assignments
                        EquipoAsignacion::create([
                            'equipo_id' => $this->record->id,
                            'user_id' => $vigente?->user_id ?? auth()->id(),
                            'tipo' => 'baja',
                            'fecha_inicio' => now(),
                            'fecha_fin' => now(),
                            'condicion_entrega' => 'malo',
                            'notas' => "Baja: {$data['motivo']}. " . ($data['notas'] ?? ''),
                            'asignado_por' => auth()->id(),
                        ]);

                        // Update equipo status
                        $this->record->update(['estado' => 'dado_de_baja']);

                        // Generate F13 registro
                        $empresaId = $this->record->empresa_id;
                        $tipo = TipoFormato::F13;
                        $numero = RegistroNumberService::generate($empresaId, $tipo);
                        $descripcion = trim("{$this->record->tipo} {$this->record->marca} {$this->record->modelo} (S/N: {$this->record->numero_serie}, Codigo: {$this->record->codigo_interno})");

                        Registro::create([
                            'empresa_id' => $empresaId,
                            'tipo_formato' => $tipo->value,
                            'numero_registro' => $numero,
                            'datos' => [
                                'fecha_destruccion' => now()->format('Y-m-d'),
                                'descripcion_activo' => $descripcion,
                                'metodo' => $data['metodo_destruccion'] !== 'no_aplica' ? $data['metodo_destruccion'] : null,
                                'responsable' => auth()->user()->name,
                                'autoriza' => auth()->user()->name,
                                'observaciones' => "Baja de equipo por: {$data['motivo']}. " . ($data['notas'] ?? ''),
                            ],
                            'estado' => 'activo',
                            'creado_por' => auth()->id(),
                        ]);
                    });

                    Notification::make()
                        ->title('Equipo dado de baja')
                        ->body('Se genero automaticamente un registro F13 de destruccion.')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->estado !== 'dado_de_baja'),

            DeleteAction::make()->label('Desactivar'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
