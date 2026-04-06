<?php

namespace App\Filament\Widgets;

use App\Models\EquipoAsignacion;
use App\Models\Politica;
use App\Models\Registro;
use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class UsuarioDashboard extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.usuario-dashboard';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['usuario', 'solo_lectura']) ?? false;
    }

    public function getData(): array
    {
        $user = auth()->user();
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        // Equipo
        $equipo = EquipoAsignacion::where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->whereIn('tipo', ['asignacion', 'transferencia'])
            ->with('equipo')
            ->first()?->equipo;

        // Politicas
        $politicasTotal = Politica::where('empresa_id', $empresaId)->where('obligatoria', true)->where('activa', true)->count();
        $politicasPendientes = Politica::pendientesPara($user->id, $empresaId)->count();

        // Mis tickets
        $misTickets = Ticket::where('empresa_id', $empresaId)
            ->where('creado_por', $user->id)
            ->whereNotIn('estado', ['cerrado'])
            ->latest('fecha_ultima_actividad')
            ->limit(5)
            ->get();

        $ticketsAbiertos = Ticket::where('empresa_id', $empresaId)
            ->where('creado_por', $user->id)
            ->whereNotIn('estado', ['cerrado', 'resuelto'])
            ->count();

        // Mis registros recientes
        $misRegistros = Registro::where('empresa_id', $empresaId)
            ->where('creado_por', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $registrosMes = Registro::where('empresa_id', $empresaId)
            ->where('creado_por', $user->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            'user' => $user,
            'equipo' => $equipo,
            'politicasOk' => $politicasPendientes === 0 && $politicasTotal > 0,
            'politicasPendientes' => $politicasPendientes,
            'politicasTotal' => $politicasTotal,
            'ticketsAbiertos' => $ticketsAbiertos,
            'misTickets' => $misTickets,
            'misRegistros' => $misRegistros,
            'registrosMes' => $registrosMes,
            'tenant' => $empresa,
        ];
    }
}
