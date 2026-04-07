<?php

namespace App\Filament\Resources\Backups\Pages;

use App\Enums\TipoFormato;
use App\Filament\Resources\Backups\BackupProgramacionResource;
use App\Models\BackupEjecucion;
use App\Models\Registro;
use App\Services\RegistroNumberService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditBackupProgramacion extends EditRecord
{
    protected static string $resource = BackupProgramacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('marcar_ejecutado')
                ->label('Marcar como ejecutado')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Textarea::make('notas')
                        ->label('Notas de ejecucion')
                        ->rows(2)
                        ->placeholder('Observaciones sobre la ejecucion del backup...'),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $programacion = $this->record;
                        $empresaId = Filament::getTenant()->id;

                        // 1. Create F12 registro
                        $tipo = TipoFormato::F12;
                        $numero = RegistroNumberService::generate($empresaId, $tipo);

                        $registro = Registro::create([
                            'empresa_id' => $empresaId,
                            'tipo_formato' => $tipo->value,
                            'numero_registro' => $numero,
                            'datos' => [
                                'nombre_backup' => $programacion->nombre,
                                'fecha_copia' => now()->toDateString(),
                                'periodicidad' => $programacion->periodicidad,
                                'descripcion_contenido' => $programacion->descripcion,
                            ],
                            'estado' => 'activo',
                            'creado_por' => auth()->id(),
                            'equipo_id' => $programacion->equipo_id,
                        ]);

                        // 2. Create ejecucion
                        BackupEjecucion::create([
                            'programacion_id' => $programacion->id,
                            'fecha_ejecucion' => now(),
                            'ejecutado_por' => auth()->id(),
                            'estado' => 'ejecutado',
                            'notas' => $data['notas'] ?? null,
                            'registro_id' => $registro->id,
                        ]);

                        // 3. Advance next backup date
                        $programacion->update([
                            'proximo_backup' => $programacion->calcularProximoBackup(),
                        ]);
                    });

                    Notification::make()
                        ->title('Backup ejecutado')
                        ->body('Se genero automaticamente un registro F12.')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->activo),

            DeleteAction::make(),
        ];
    }
}
