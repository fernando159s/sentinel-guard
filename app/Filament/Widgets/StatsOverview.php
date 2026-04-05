<?php

namespace App\Filament\Widgets;

use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $empresaId) {
            return [];
        }

        $registrosMes = Registro::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $registrosMesAnterior = Registro::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $ticketsAbiertos = Ticket::where('empresa_id', $empresaId)
            ->whereNotIn('estado', ['cerrado', 'resuelto'])
            ->count();

        $ticketsMes = Ticket::where('empresa_id', $empresaId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $incidenciasMes = Registro::where('empresa_id', $empresaId)
            ->where('tipo_formato', 'F09')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $usuariosActivos = User::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->count();

        // Sparkline data: registros per day last 7 days
        $registrosTrend = collect(range(6, 0))->map(fn ($i) => Registro::where('empresa_id', $empresaId)
            ->whereDate('created_at', now()->subDays($i))
            ->count()
        )->toArray();

        $ticketsTrend = collect(range(6, 0))->map(fn ($i) => Ticket::where('empresa_id', $empresaId)
            ->whereDate('created_at', now()->subDays($i))
            ->count()
        )->toArray();

        return [
            Stat::make('Registros del mes', $registrosMes)
                ->description($this->deltaDescription($registrosMes, $registrosMesAnterior))
                ->descriptionIcon($registrosMes >= $registrosMesAnterior ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($registrosMes >= $registrosMesAnterior ? 'success' : 'warning')
                ->chart($registrosTrend),

            Stat::make('Tickets abiertos', $ticketsAbiertos)
                ->description("{$ticketsMes} creados este mes")
                ->descriptionIcon('heroicon-m-ticket')
                ->color($ticketsAbiertos > 5 ? 'warning' : 'success')
                ->chart($ticketsTrend),

            Stat::make('Incidencias (F09)', $incidenciasMes)
                ->description('Este mes')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($incidenciasMes > 0 ? 'danger' : 'success'),

            Stat::make('Usuarios activos', $usuariosActivos)
                ->description('En tu empresa')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }

    private function deltaDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? "+{$current} vs mes anterior" : 'Sin cambios';
        }

        $delta = $current - $previous;
        $sign = $delta >= 0 ? '+' : '';

        return "{$sign}{$delta} vs mes anterior";
    }
}
