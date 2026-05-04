<?php

namespace App\Filament\Resources\Capacitaciones\Pages;

use App\Filament\Resources\Capacitaciones\CapacitacionResource;
use App\Models\CapacitacionAsistencia;
use Filament\Actions\Action as PageAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewCapacitacion extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CapacitacionResource::class;

    protected string $view = 'filament.resources.capacitaciones.pages.view-capacitacion';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check();
    }

    public function getTitle(): string
    {
        return $this->record->tema;
    }

    public function getBreadcrumb(): string
    {
        return $this->record->tema;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (auth()->user()?->hasRole(['super_admin', 'admin_empresa'])) {
            $actions[] = EditAction::make();

            $actions[] = PageAction::make('marcar_asistencia_masiva')
                ->label('Marcar asistencia masiva')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Marcar asistencia masiva')
                ->modalDescription('Esto marcara como "asistio" a todos los usuarios que aun no tienen asistencia confirmada.')
                ->action(function () {
                    $this->record->asistencias()
                        ->where('asistio', false)
                        ->update([
                            'asistio' => true,
                            'confirmado_por' => auth()->id(),
                            'fecha_confirmacion' => now(),
                        ]);

                    Notification::make()
                        ->success()
                        ->title('Asistencia masiva registrada')
                        ->send();
                });
        }

        return $actions;
    }

    public function table(Table $table): Table
    {
        $isAdmin = auth()->user()?->hasRole(['super_admin', 'admin_empresa']);

        return $table
            ->query(
                CapacitacionAsistencia::query()
                    ->where('capacitacion_id', $this->record->id)
                    ->with(['user', 'confirmador'])
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->join('users', 'capacitacion_asistencias.user_id', '=', 'users.id')
                            ->orderBy('users.name', $direction)
                            ->select('capacitacion_asistencias.*');
                    }),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.puesto')
                    ->label('Puesto')
                    ->placeholder('—'),
                IconColumn::make('asistio')
                    ->label('Asistio')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('confirmador.name')
                    ->label('Confirmado por')
                    ->placeholder('—'),
                TextColumn::make('fecha_confirmacion')
                    ->label('Fecha confirmacion')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->recordActions($isAdmin ? [
                TableAction::make('toggle_asistencia')
                    ->label(fn ($record) => $record->asistio ? 'Quitar' : 'Marcar')
                    ->icon(fn ($record) => $record->asistio ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->asistio ? 'danger' : 'success')
                    ->action(function ($record) {
                        if ($record->asistio) {
                            $record->update([
                                'asistio' => false,
                                'confirmado_por' => null,
                                'fecha_confirmacion' => null,
                            ]);
                        } else {
                            $record->update([
                                'asistio' => true,
                                'confirmado_por' => auth()->id(),
                                'fecha_confirmacion' => now(),
                            ]);
                        }
                    }),
            ] : [])
            ->defaultSort('user_id');
    }

    public function confirmarAsistencia(): void
    {
        $capacitacion = $this->record;

        if (! $capacitacion->dentroVentanaConfirmacion()) {
            Notification::make()
                ->danger()
                ->title('Periodo de confirmacion expirado')
                ->body('Han pasado mas de 24 horas desde que termino la capacitacion. Contacta a tu administrador.')
                ->send();

            return;
        }

        CapacitacionAsistencia::updateOrCreate(
            ['capacitacion_id' => $capacitacion->id, 'user_id' => auth()->id()],
            [
                'asistio' => true,
                'confirmado_por' => auth()->id(),
                'fecha_confirmacion' => now(),
            ]
        );

        Notification::make()
            ->success()
            ->title('Asistencia confirmada')
            ->send();
    }

    public function getMiAsistenciaProperty(): ?CapacitacionAsistencia
    {
        return CapacitacionAsistencia::where('capacitacion_id', $this->record->id)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function getIsAdminProperty(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }
}
