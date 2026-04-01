<?php

namespace App\Filament\Pages;

use App\Enums\TipoFormato;
use App\Jobs\SendEmailJob;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use App\Services\RegistroNumberService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

class PanelAgente extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Soporte';

    protected static ?string $navigationLabel = 'Panel de Agente';

    protected static ?string $title = 'Panel de Agente Helpdesk';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.panel-agente';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    // Filters
    #[Url]
    public string $filtroEstado = '';

    #[Url]
    public string $filtroPrioridad = '';

    #[Url]
    public string $filtroAgente = '';

    #[Url]
    public string $filtroEmpresa = '';

    #[Url]
    public string $busqueda = '';

    // Selected ticket
    public ?int $selectedTicketId = null;

    // Reply form
    public string $respuestaContenido = '';
    public bool $respuestaInterna = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole(['super_admin', 'agente_helpdesk']);
    }

    public function getTicketsProperty(): Collection
    {
        $query = Ticket::query()
            ->withoutGlobalScopes()
            ->with(['empresa', 'creador', 'agente'])
            ->withCount('mensajes');

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        } else {
            // By default, hide closed tickets
            $query->whereNotIn('estado', ['cerrado']);
        }

        if ($this->filtroPrioridad) {
            $query->where('prioridad', $this->filtroPrioridad);
        }

        if ($this->filtroAgente === 'sin_asignar') {
            $query->whereNull('asignado_a');
        } elseif ($this->filtroAgente === 'mis') {
            $query->where('asignado_a', auth()->id());
        } elseif ($this->filtroAgente) {
            $query->where('asignado_a', $this->filtroAgente);
        }

        if ($this->filtroEmpresa) {
            $query->where('empresa_id', $this->filtroEmpresa);
        }

        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->where('numero_ticket', 'like', "%{$this->busqueda}%")
                  ->orWhere('asunto', 'like', "%{$this->busqueda}%");
            });
        }

        return $query->orderByRaw("FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')")
            ->orderBy('fecha_ultima_actividad', 'desc')
            ->limit(100)
            ->get();
    }

    public function getCountersProperty(): array
    {
        $base = Ticket::query()->withoutGlobalScopes();

        return [
            'sin_asignar' => (clone $base)->whereNull('asignado_a')->whereNotIn('estado', ['cerrado', 'resuelto'])->count(),
            'nuevos_hoy' => (clone $base)->where('estado', 'nuevo')->whereDate('created_at', today())->count(),
            'en_revision' => (clone $base)->where('estado', 'en_revision')->count(),
            'esperando' => (clone $base)->where('estado', 'esperando_usuario')->count(),
            'mis_tickets' => (clone $base)->where('asignado_a', auth()->id())->whereNotIn('estado', ['cerrado', 'resuelto'])->count(),
            'total_abiertos' => (clone $base)->whereNotIn('estado', ['cerrado', 'resuelto'])->count(),
            'total' => (clone $base)->count(),
        ];
    }

    public function getCounterTrendsProperty(): array
    {
        $days = 7;
        $trends = [
            'sin_asignar' => [],
            'nuevos_hoy' => [],
            'mis_tickets' => [],
            'en_revision' => [],
            'esperando' => [],
            'total_abiertos' => [],
            'total' => [],
        ];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $trends['sin_asignar'][] = Ticket::withoutGlobalScopes()
                ->whereNull('asignado_a')->whereNotIn('estado', ['cerrado', 'resuelto'])
                ->whereDate('created_at', '<=', $date)->count();

            $trends['nuevos_hoy'][] = Ticket::withoutGlobalScopes()
                ->where('estado', 'nuevo')->whereDate('created_at', $date)->count();

            $trends['mis_tickets'][] = Ticket::withoutGlobalScopes()
                ->where('asignado_a', auth()->id())->whereNotIn('estado', ['cerrado', 'resuelto'])
                ->whereDate('created_at', '<=', $date)->count();

            $trends['en_revision'][] = Ticket::withoutGlobalScopes()
                ->where('estado', 'en_revision')->whereDate('fecha_ultima_actividad', '<=', $date)->count();

            $trends['esperando'][] = Ticket::withoutGlobalScopes()
                ->where('estado', 'esperando_usuario')->whereDate('fecha_ultima_actividad', '<=', $date)->count();

            $trends['total_abiertos'][] = Ticket::withoutGlobalScopes()
                ->whereNotIn('estado', ['cerrado', 'resuelto'])
                ->whereDate('created_at', '<=', $date)->count();

            $trends['total'][] = Ticket::withoutGlobalScopes()
                ->whereDate('created_at', '<=', $date)->count();
        }

        return $trends;
    }

    public function getSelectedTicketProperty(): ?Ticket
    {
        if (! $this->selectedTicketId) {
            return null;
        }

        return Ticket::withoutGlobalScopes()
            ->with(['empresa', 'creador', 'agente'])
            ->find($this->selectedTicketId);
    }

    public function getMensajesProperty(): Collection
    {
        if (! $this->selectedTicket) {
            return new Collection();
        }

        $query = $this->selectedTicket->mensajes()
            ->with(['autor', 'adjuntos'])
            ->orderBy('created_at');

        if (! auth()->user()->can('ver_notas_internas')) {
            $query->where('tipo', 'publico');
        }

        return $query->get();
    }

    public function selectTicket(int $ticketId): void
    {
        $this->selectedTicketId = $ticketId;
        $this->respuestaContenido = '';
        $this->respuestaInterna = false;
    }

    public function enviarRespuesta(): void
    {
        if (! $this->selectedTicket || ! trim($this->respuestaContenido) || ! auth()->user()->can('editar_tickets')) {
            return;
        }

        $this->selectedTicket->mensajes()->create([
            'autor_id' => auth()->id(),
            'tipo' => $this->respuestaInterna ? 'interno' : 'publico',
            'contenido' => trim($this->respuestaContenido),
            'created_at' => now(),
        ]);

        $this->selectedTicket->update(['fecha_ultima_actividad' => now()]);

        $this->respuestaContenido = '';
        $this->respuestaInterna = false;

        // Force re-computation of cached properties
        unset($this->mensajes);
        unset($this->tickets);

        Notification::make()
            ->title('Mensaje enviado')
            ->success()
            ->send();
    }

    public function asignarAMi(): void
    {
        if (! $this->selectedTicket || ! auth()->user()->can('editar_tickets')) {
            return;
        }

        $this->selectedTicket->update([
            'asignado_a' => auth()->id(),
            'estado' => $this->selectedTicket->estado === 'nuevo' ? 'en_revision' : $this->selectedTicket->estado,
            'fecha_ultima_actividad' => now(),
        ]);

        unset($this->selectedTicket, $this->tickets, $this->counters);

        Notification::make()
            ->title('Ticket asignado a ti')
            ->success()
            ->send();
    }

    public function cambiarEstado(string $estado): void
    {
        $estadosValidos = ['nuevo', 'en_revision', 'esperando_usuario', 'resuelto', 'cerrado'];

        if (! $this->selectedTicket || ! auth()->user()->can('editar_tickets') || ! in_array($estado, $estadosValidos)) {
            return;
        }

        $updates = [
            'estado' => $estado,
            'fecha_ultima_actividad' => now(),
        ];

        if (in_array($estado, ['resuelto', 'cerrado'])) {
            $updates['fecha_cierre'] = now();
        }

        $this->selectedTicket->update($updates);

        unset($this->selectedTicket, $this->tickets, $this->counters);

        Notification::make()
            ->title('Estado actualizado a: ' . $estado)
            ->success()
            ->send();
    }

    public function reabrirTicket(): void
    {
        $ticket = $this->selectedTicket;

        if (! $ticket || ! $ticket->puedeReabrirse() || ! auth()->user()->can('editar_tickets')) {
            return;
        }

        $ticket->update([
            'estado' => 'en_revision',
            'fecha_ultima_actividad' => now(),
            'fecha_cierre' => null,
        ]);

        $ticket->mensajes()->create([
            'autor_id' => auth()->id(),
            'tipo' => 'interno',
            'contenido' => 'TICKET REABIERTO por ' . auth()->user()->name . '.',
            'created_at' => now(),
        ]);

        if ($ticket->asignado_a && $ticket->agente && $ticket->agente->notif_tickets) {
            $ticketUrl = url("admin/{$ticket->empresa?->ruc}/tickets/{$ticket->id}");

            dispatch(SendEmailJob::fromTemplate(
                destinatario: $ticket->agente->email,
                nombreDestino: $ticket->agente->name,
                templateSlug: 'ticket_reabierto',
                templateVariables: [
                    'nombre' => $ticket->agente->name,
                    'numero_ticket' => $ticket->numero_ticket,
                    'asunto' => $ticket->asunto,
                    'quien_reabrio' => auth()->user()->name,
                    'motivo' => 'Reabierto desde panel de agente',
                    'enlace_ticket' => $ticketUrl,
                ],
                actionUrl: $ticketUrl,
                actionLabel: 'Ver ticket',
            ));
        }

        unset($this->selectedTicket, $this->tickets, $this->counters, $this->mensajes);

        Notification::make()
            ->title('Ticket reabierto')
            ->success()
            ->send();
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEstado = '';
        $this->filtroPrioridad = '';
        $this->filtroAgente = '';
        $this->filtroEmpresa = '';
        $this->busqueda = '';
    }

    public function getAgentesProperty(): array
    {
        return User::role(['super_admin', 'agente_helpdesk'])
            ->where('estado', 'activo')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getEmpresasProperty(): array
    {
        return \App\Models\Empresa::where('estado', 'activo')
            ->pluck('razon_social', 'id')
            ->toArray();
    }

    /**
     * Get suggested formats based on ticket category.
     */
    public function getFormatosSugeridosProperty(): array
    {
        if (! $this->selectedTicket) {
            return [];
        }

        $categoria = $this->selectedTicket->categoria;

        $sugeridos = match ($categoria) {
            'problema_tecnico' => [
                TipoFormato::F09->value => 'F09 - Notificacion de incidencias',
                TipoFormato::F10->value => 'F10 - Resolucion de incidencias',
            ],
            'error_registro' => [
                TipoFormato::F09->value => 'F09 - Notificacion de incidencias',
                TipoFormato::F10->value => 'F10 - Resolucion de incidencias',
            ],
            'solicitud_acceso' => [
                TipoFormato::F05->value => 'F05 - Personal autorizado al BD',
                TipoFormato::F06->value => 'F06 - Acceso soporte no autorizado',
            ],
            default => [
                TipoFormato::F09->value => 'F09 - Notificacion de incidencias',
            ],
        };

        // Always offer F11 as option (recovery can happen in any context)
        $sugeridos[TipoFormato::F11->value] = 'F11 - Recuperacion de datos';

        return $sugeridos;
    }

    /**
     * Create a security format record pre-filled from the selected ticket.
     */
    public function crearRegistroDesdeTicket(string $tipoFormato): void
    {
        $ticket = $this->selectedTicket;

        if (! $ticket || ! auth()->user()->can('crear_registros')) {
            return;
        }

        if (! in_array($ticket->estado, ['resuelto', 'cerrado', 'en_revision'])) {
            return;
        }

        $tipo = TipoFormato::tryFrom($tipoFormato);
        if (! $tipo) {
            return;
        }

        $empresaId = $ticket->empresa_id;

        // Prevent duplicate: only one registro per ticket (any format)
        $existente = Registro::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereJsonContains('datos->ticket_referencia', $ticket->numero_ticket)
            ->first();

        if ($existente) {
            $tipoExistente = TipoFormato::tryFrom($existente->tipo_formato);

            Notification::make()
                ->title('Este ticket ya tiene un registro')
                ->body("Ya existe el registro {$existente->numero_registro} ({$tipoExistente?->label()}) vinculado al ticket {$ticket->numero_ticket}")
                ->warning()
                ->send();

            return;
        }
        $numero = RegistroNumberService::generate($empresaId, $tipo);
        $datos = $this->buildDatosFromTicket($ticket, $tipo);

        $registro = Registro::create([
            'empresa_id' => $empresaId,
            'tipo_formato' => $tipo->value,
            'numero_registro' => $numero,
            'datos' => $datos,
            'estado' => 'activo',
            'creado_por' => auth()->id(),
        ]);

        $registroUrl = url("admin/{$ticket->empresa?->ruc}/registros/{$registro->id}/edit");

        Notification::make()
            ->title("Registro {$numero} creado")
            ->body("Se creo el registro {$tipo->label()} desde el ticket {$ticket->numero_ticket}")
            ->success()
            ->actions([
                \Filament\Actions\Action::make('ver_registro')
                    ->label('Ver registro')
                    ->url($registroUrl)
                    ->openUrlInNewTab(),
            ])
            ->persistent()
            ->send();
    }

    /**
     * Build the datos JSON array pre-filled from ticket data.
     */
    private function buildDatosFromTicket(Ticket $ticket, TipoFormato $tipo): array
    {
        $descripcionPlana = strip_tags($ticket->descripcion);
        $agenteName = $ticket->agente?->name ?? auth()->user()->name;
        $creadorName = $ticket->creador?->name ?? 'Usuario';
        $empresaNombre = $ticket->empresa?->razon_social ?? '';
        $resumenMensajes = $this->extractResumenMensajes($ticket);
        $impacto = $this->buildImpactoFromTicket($ticket);
        $ticketUrl = url("admin/{$ticket->empresa?->ruc}/tickets/{$ticket->id}");
        $observacion = "Registro generado automaticamente desde el ticket {$ticket->numero_ticket} ({$ticket->asunto}). Ver ticket: {$ticketUrl}";

        return match ($tipo) {
            TipoFormato::F09 => [
                'fecha_evento' => $ticket->created_at->format('Y-m-d H:i:s'),
                'tipo_incidencia' => $this->mapCategoriaTipoIncidencia($ticket->categoria),
                'sistema_equipo' => "Ticket {$ticket->numero_ticket} - {$empresaNombre}",
                'banco_datos' => '',
                'descripcion' => $descripcionPlana ?: $ticket->asunto,
                'medidas_inmediatas' => $resumenMensajes,
                'personas_notificadas' => array_filter([$creadorName, $agenteName]),
                'impacto_potencial' => $impacto,
                'comunica_nombre' => $creadorName,
                'severidad' => $this->mapPrioridadSeveridad($ticket->prioridad),
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],

            TipoFormato::F10 => [
                'incidencia_ref' => $this->findIncidenciaRefParaTicket($ticket),
                'fecha_cierre' => ($ticket->fecha_cierre ?? now())->format('Y-m-d H:i:s'),
                'clasificacion' => $this->mapPrioridadSeveridad($ticket->prioridad),
                'requirio_recuperacion' => false,
                'medidas_adoptadas' => $resumenMensajes ?: $descripcionPlana,
                'resultado_verificacion' => $ticket->estado === 'resuelto' ? 'Ticket resuelto satisfactoriamente' : 'Ticket cerrado',
                'ejecuto' => $agenteName,
                'firma_responsable' => $agenteName,
                'acciones_preventivas' => '',
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],

            TipoFormato::F05 => [
                'usuario' => $creadorName,
                'fecha_asignacion' => ($ticket->fecha_cierre ?? now())->format('Y-m-d H:i:s'),
                'banco_datos' => '',
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],

            TipoFormato::F06 => [
                'descripcion_soporte' => $descripcionPlana ?: $ticket->asunto,
                'persona_accede' => $creadorName,
                'fecha_acceso' => $ticket->created_at->format('Y-m-d'),
                'hora_acceso' => $ticket->created_at->format('H:i'),
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],

            TipoFormato::F11 => [
                'incidencia_relacionada' => $this->findIncidenciaRefParaTicket($ticket),
                'fecha_realizacion' => ($ticket->fecha_cierre ?? now())->format('Y-m-d H:i:s'),
                'autorizacion_escrita' => false,
                'responsable_bd' => $agenteName,
                'proceso_realizado' => $resumenMensajes ?: $descripcionPlana ?: $ticket->asunto,
                'persona_ejecutora' => $agenteName,
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],

            default => [
                'observaciones' => $observacion,
                'ticket_referencia' => $ticket->numero_ticket,
            ],
        };
    }

    private function buildImpactoFromTicket(Ticket $ticket): string
    {
        $nivel = match ($ticket->prioridad) {
            'urgente' => 'Impacto critico',
            'alta' => 'Impacto alto',
            'media' => 'Impacto moderado',
            default => 'Impacto bajo',
        };

        return "{$nivel} - {$ticket->asunto} ({$ticket->empresa?->razon_social})";
    }

    private function mapCategoriaTipoIncidencia(string $categoria): string
    {
        return match ($categoria) {
            'problema_tecnico' => 'fallo_sistema',
            'error_registro' => 'fallo_sistema',
            'solicitud_acceso' => 'acceso_no_autorizado',
            default => 'otro',
        };
    }

    private function mapPrioridadSeveridad(string $prioridad): string
    {
        return match ($prioridad) {
            'urgente', 'alta' => 'alta',
            'media' => 'media',
            default => 'baja',
        };
    }

    /**
     * Extract a summary from agent messages for pre-filling resolution fields.
     */
    private function extractResumenMensajes(Ticket $ticket): string
    {
        $agentIds = User::role(['super_admin', 'agente_helpdesk'])->pluck('id');

        $mensajes = $ticket->mensajes()
            ->whereIn('autor_id', $agentIds)
            ->where('tipo', 'publico')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->pluck('contenido')
            ->reverse()
            ->implode("\n");

        return $mensajes ?: '';
    }

    /**
     * Find if there's an existing F09 registro linked to this ticket.
     */
    private function findIncidenciaRefParaTicket(Ticket $ticket): string
    {
        $registro = Registro::withoutGlobalScopes()
            ->where('empresa_id', $ticket->empresa_id)
            ->where('tipo_formato', 'F09')
            ->whereJsonContains('datos->ticket_referencia', $ticket->numero_ticket)
            ->first();

        return $registro?->numero_registro ?? '';
    }

    /**
     * Sanitize HTML content: allow only safe tags with safe attributes.
     * Images are rendered safely with src validated against local storage.
     */
    public function sanitizeHtml(string $html): HtmlString
    {
        // Strip all tags except safe formatting and images
        $clean = strip_tags($html, '<p><br><strong><em><u><ul><ol><li><img>');

        // Remove all attributes from tags except src on img (and only allow local URLs)
        $clean = preg_replace_callback('/<img\s[^>]*>/i', function ($match) {
            if (preg_match('/src=["\']([^"\']+)["\']/i', $match[0], $srcMatch)) {
                $src = $srcMatch[1];
                // Only allow local storage URLs
                if (str_starts_with($src, '/storage/') || str_starts_with($src, url('/storage/'))) {
                    $safeSrc = e($src);

                    return '<img src="' . $safeSrc . '" style="max-width:320px;height:auto;border-radius:8px;margin:4px 0;" loading="lazy">';
                }
            }

            return '';
        }, $clean);

        // Remove all attributes from non-img tags
        $clean = preg_replace('/<(p|br|strong|em|u|ul|ol|li)\s[^>]*>/i', '<$1>', $clean);

        return new HtmlString($clean);
    }
}
