<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use App\Models\TicketAdjunto;
use App\Models\TicketMensaje;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.pages.view-ticket';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detalle del ticket')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('numero_ticket')
                            ->label('Número'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'nuevo' => 'info',
                                'en_revision' => 'warning',
                                'esperando_usuario' => 'gray',
                                'resuelto' => 'success',
                                'cerrado' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'nuevo' => 'Nuevo',
                                'en_revision' => 'En revisión',
                                'esperando_usuario' => 'Esperando usuario',
                                'resuelto' => 'Resuelto',
                                'cerrado' => 'Cerrado',
                                default => $state,
                            }),
                        TextEntry::make('prioridad')
                            ->label('Prioridad')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'baja' => 'gray',
                                'media' => 'info',
                                'alta' => 'warning',
                                'urgente' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('categoria')
                            ->label('Categoría')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'consulta' => 'Consulta',
                                'problema_tecnico' => 'Problema técnico',
                                'error_registro' => 'Error en registro',
                                'solicitud_acceso' => 'Solicitud de acceso',
                                'otro' => 'Otro',
                                default => $state,
                            }),
                        TextEntry::make('creador.name')
                            ->label('Creado por'),
                        TextEntry::make('agente.name')
                            ->label('Agente asignado')
                            ->placeholder('Sin asignar'),
                        TextEntry::make('empresa.razon_social')
                            ->label('Empresa'),
                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('fecha_cierre')
                            ->label('Fecha de cierre')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public function getMensajes(): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->record->mensajes()->with(['autor', 'adjuntos'])->orderBy('created_at');

        if (! auth()->user()->can('ver_notas_internas')) {
            $query->where('tipo', 'publico');
        }

        return $query->get();
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        return array_filter([
            // Responder
            Action::make('responder')
                ->label('Responder')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->form([
                    Textarea::make('contenido')
                        ->label('Mensaje')
                        ->required()
                        ->rows(4),
                    Toggle::make('interno')
                        ->label('Nota interna (solo visible para agentes)')
                        ->visible(fn () => auth()->user()->can('ver_notas_internas')),
                    FileUpload::make('adjuntos')
                        ->label('Adjuntos')
                        ->multiple()
                        ->disk('tickets')
                        ->maxSize(5120)
                        ->maxFiles(3)
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                ])
                ->action(function (array $data) {
                    $mensaje = $this->record->mensajes()->create([
                        'autor_id' => auth()->id(),
                        'tipo' => ($data['interno'] ?? false) ? 'interno' : 'publico',
                        'contenido' => $data['contenido'],
                        'created_at' => now(),
                    ]);

                    if (! empty($data['adjuntos'])) {
                        foreach ($data['adjuntos'] as $path) {
                            $mensaje->adjuntos()->create([
                                'nombre_original' => basename($path),
                                'nombre_almacenado' => $path,
                                'tipo_mime' => \Illuminate\Support\Facades\Storage::disk('tickets')->mimeType($path),
                                'tamano' => \Illuminate\Support\Facades\Storage::disk('tickets')->size($path),
                                'created_at' => now(),
                            ]);
                        }
                    }

                    $this->record->update(['fecha_ultima_actividad' => now()]);

                    Notification::make()
                        ->title('Mensaje enviado')
                        ->success()
                        ->send();
                })
                ->visible(fn () => ! in_array($this->record->estado, ['cerrado'])),

            // Asignar agente
            $user->can('asignar_tickets') ? Action::make('asignar')
                ->label('Asignar agente')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Select::make('asignado_a')
                        ->label('Agente')
                        ->options(
                            User::role(['super_admin', 'agente_helpdesk'])
                                ->where('estado', 'activo')
                                ->pluck('name', 'id')
                        )
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'asignado_a' => $data['asignado_a'],
                        'fecha_ultima_actividad' => now(),
                    ]);

                    if ($this->record->estado === 'nuevo') {
                        $this->record->update(['estado' => 'en_revision']);
                    }

                    Notification::make()
                        ->title('Agente asignado')
                        ->success()
                        ->send();
                }) : null,

            // Cambiar estado
            $user->can('editar_tickets') ? Action::make('cambiarEstado')
                ->label('Cambiar estado')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('estado')
                        ->label('Nuevo estado')
                        ->options([
                            'nuevo' => 'Nuevo',
                            'en_revision' => 'En revisión',
                            'esperando_usuario' => 'Esperando usuario',
                            'resuelto' => 'Resuelto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $updates = [
                        'estado' => $data['estado'],
                        'fecha_ultima_actividad' => now(),
                    ];

                    if (in_array($data['estado'], ['resuelto', 'cerrado'])) {
                        $updates['fecha_cierre'] = now();
                    }

                    $this->record->update($updates);

                    Notification::make()
                        ->title('Estado actualizado')
                        ->success()
                        ->send();
                }) : null,
        ]);
    }
}
