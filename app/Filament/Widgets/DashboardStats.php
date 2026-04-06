<?php

namespace App\Filament\Widgets;

use App\Models\Equipo;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa', 'agente_helpdesk']) ?? false;
    }

    protected function getColumns(): int
    {
        return 6;
    }

    protected function getStats(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;
        $user = auth()->user();
        $isAgent = $user?->hasRole(['super_admin', 'agente_helpdesk']);

        if (! $empresaId) {
            return [];
        }

        $stats = [];

        // Tickets abiertos (todos ven)
        $ticketsAbiertos = Ticket::where('empresa_id', $empresaId)
            ->whereNotIn('estado', ['cerrado', 'resuelto'])->count();
        $ticketsMes = Ticket::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $stats[] = Stat::make('Tickets abiertos', $ticketsAbiertos)
            ->description("{$ticketsMes} este mes")
            ->descriptionIcon('heroicon-m-ticket')
            ->color($ticketsAbiertos > 5 ? 'warning' : 'success')
            ->chart(collect(range(6, 0))->map(fn ($i) => Ticket::where('empresa_id', $empresaId)
                ->whereDate('created_at', now()->subDays($i))->count())->toArray());

        // Sin asignar (solo agentes)
        if ($isAgent) {
            $sinAsignar = Ticket::withoutGlobalScopes()
                ->whereNull('asignado_a')->whereNotIn('estado', ['cerrado', 'resuelto'])->count();
            $stats[] = Stat::make('Sin asignar', $sinAsignar)
                ->description('Tickets sin agente')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($sinAsignar > 0 ? 'danger' : 'success');
        }

        // Registros del mes
        $registrosMes = Registro::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $registrosMesAnt = Registro::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $delta = $registrosMes - $registrosMesAnt;

        $stats[] = Stat::make('Registros', $registrosMes)
            ->description(($delta >= 0 ? '+' : '') . $delta . ' vs mes anterior')
            ->descriptionIcon($delta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($delta >= 0 ? 'success' : 'warning')
            ->chart(collect(range(6, 0))->map(fn ($i) => Registro::where('empresa_id', $empresaId)
                ->whereDate('created_at', now()->subDays($i))->count())->toArray());

        // Incidencias F09
        $incidencias = Registro::where('empresa_id', $empresaId)
            ->where('tipo_formato', 'F09')
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $stats[] = Stat::make('Incidencias', $incidencias)
            ->description('F09 este mes')
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color($incidencias > 0 ? 'danger' : 'success');

        // Equipos
        $totalEquipos = Equipo::where('empresa_id', $empresaId)->where('estado', 'activo')->count();
        $enMant = Equipo::where('empresa_id', $empresaId)->where('estado', 'mantenimiento')->count();
        $stats[] = Stat::make('Equipos', $totalEquipos)
            ->description($enMant > 0 ? "{$enMant} en mantenimiento" : 'Todos activos')
            ->descriptionIcon('heroicon-m-computer-desktop')
            ->color($enMant > 0 ? 'warning' : 'info');

        // Usuarios activos
        $usuarios = User::where('empresa_id', $empresaId)->where('estado', 'activo')->count();
        $stats[] = Stat::make('Usuarios', $usuarios)
            ->description('Activos')
            ->descriptionIcon('heroicon-m-users')
            ->color('info');

        return $stats;
    }
}
