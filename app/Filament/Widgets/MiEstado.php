<?php

namespace App\Filament\Widgets;

use App\Models\EquipoAsignacion;
use App\Models\Politica;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class MiEstado extends Widget
{
    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.mi-estado';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return false; // Replaced by UsuarioDashboard
    }

    public function getData(): array
    {
        $user = auth()->user();
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $user || ! $empresaId) {
            return ['items' => []];
        }

        // Equipo asignado
        $equipoAsignado = EquipoAsignacion::where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->whereIn('tipo', ['asignacion', 'transferencia'])
            ->with('equipo')
            ->first();

        $equipoInfo = $equipoAsignado
            ? "{$equipoAsignado->equipo?->codigo_interno} — {$equipoAsignado->equipo?->marca} {$equipoAsignado->equipo?->modelo}"
            : null;

        // Politicas
        $politicasTotal = Politica::where('empresa_id', $empresaId)
            ->where('obligatoria', true)->where('activa', true)->count();
        $politicasPendientes = Politica::pendientesPara($user->id, $empresaId)->count();
        $politicasAceptadas = $politicasTotal - $politicasPendientes;

        // Tickets abiertos del usuario
        $misTickets = \App\Models\Ticket::where('empresa_id', $empresaId)
            ->where('creado_por', $user->id)
            ->whereNotIn('estado', ['cerrado', 'resuelto'])
            ->count();

        // Checklists de mi equipo
        $checklistOk = false;
        if ($equipoAsignado?->equipo) {
            $ultimoChecklist = \App\Models\ChecklistEjecucion::where('equipo_id', $equipoAsignado->equipo->id)
                ->latest('fecha_ejecucion')
                ->first();
            $checklistOk = $ultimoChecklist && $ultimoChecklist->fecha_ejecucion->greaterThan(now()->subDays(30));
        }

        $items = [
            [
                'label' => 'Equipo asignado',
                'value' => $equipoInfo ?? 'Sin equipo',
                'ok' => $equipoAsignado !== null,
                'icon' => 'computer-desktop',
            ],
            [
                'label' => 'Politicas aceptadas',
                'value' => "{$politicasAceptadas}/{$politicasTotal}",
                'ok' => $politicasPendientes === 0,
                'icon' => 'shield-check',
            ],
            [
                'label' => 'NDA firmado',
                'value' => $politicasPendientes === 0 ? 'Al dia' : "{$politicasPendientes} pendiente(s)",
                'ok' => $politicasPendientes === 0,
                'icon' => 'document-check',
            ],
            [
                'label' => 'Tickets abiertos',
                'value' => $misTickets === 0 ? 'Ninguno' : "{$misTickets} abierto(s)",
                'ok' => $misTickets === 0,
                'icon' => 'ticket',
            ],
            [
                'label' => 'Checklist equipo',
                'value' => $equipoAsignado ? ($checklistOk ? 'Vigente' : 'Vencido') : 'N/A',
                'ok' => ! $equipoAsignado || $checklistOk,
                'icon' => 'clipboard-document-check',
            ],
            [
                'label' => 'Estado cuenta',
                'value' => $user->estado === 'activo' ? 'Activo' : 'Bloqueado',
                'ok' => $user->estado === 'activo',
                'icon' => 'user-circle',
            ],
        ];

        return [
            'items' => $items,
            'nombre' => $user->name,
            'rol' => $user->roles->first()?->name ?? 'usuario',
        ];
    }
}
