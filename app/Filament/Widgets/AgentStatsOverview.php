<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\TicketMensaje;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'agente_helpdesk']) ?? false;
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $base = Ticket::withoutGlobalScopes();

        $misTickets = (clone $base)->where('asignado_a', $userId)
            ->whereNotIn('estado', ['cerrado', 'resuelto'])->count();

        $sinAsignar = (clone $base)->whereNull('asignado_a')
            ->whereNotIn('estado', ['cerrado', 'resuelto'])->count();

        $resueltosHoy = (clone $base)->where('asignado_a', $userId)
            ->where('estado', 'resuelto')
            ->whereDate('fecha_cierre', today())->count();

        // Tickets by estado for sparkline
        $estadoCounts = [
            'nuevo' => (clone $base)->where('estado', 'nuevo')->count(),
            'en_revision' => (clone $base)->where('estado', 'en_revision')->count(),
            'esperando' => (clone $base)->where('estado', 'esperando_usuario')->count(),
            'resuelto' => (clone $base)->where('estado', 'resuelto')
                ->whereMonth('fecha_cierre', now()->month)->count(),
        ];

        // Avg response time (hours) for tickets assigned to me this month
        $avgResponseHours = $this->calcAvgResponseTime($userId);

        return [
            Stat::make('Mis tickets activos', $misTickets)
                ->description($resueltosHoy > 0 ? "{$resueltosHoy} resueltos hoy" : 'Asignados a ti')
                ->descriptionIcon($resueltosHoy > 0 ? 'heroicon-m-check-circle' : 'heroicon-m-user')
                ->color($misTickets > 5 ? 'warning' : 'primary')
                ->chart(array_values($estadoCounts)),

            Stat::make('Sin asignar', $sinAsignar)
                ->description('Tickets sin agente')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($sinAsignar > 0 ? 'danger' : 'success'),

            Stat::make('Tiempo resp. promedio', $avgResponseHours)
                ->description('Horas (este mes)')
                ->descriptionIcon('heroicon-m-clock')
                ->color($this->responseTimeColor($avgResponseHours)),

            Stat::make('Total global abiertos', (clone $base)->whereNotIn('estado', ['cerrado', 'resuelto'])->count())
                ->description('Todas las empresas')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }

    private function calcAvgResponseTime(int $userId): string
    {
        $tickets = Ticket::withoutGlobalScopes()
            ->where('asignado_a', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with(['mensajes' => fn ($q) => $q->where('autor_id', $userId)->orderBy('created_at')->limit(1)])
            ->get();

        $totalHours = 0;
        $count = 0;

        foreach ($tickets as $ticket) {
            $firstResponse = $ticket->mensajes->first();
            if ($firstResponse) {
                $totalHours += $ticket->created_at->diffInMinutes($firstResponse->created_at) / 60;
                $count++;
            }
        }

        if ($count === 0) {
            return '—';
        }

        $avg = round($totalHours / $count, 1);

        return $avg < 1 ? round($avg * 60) . 'min' : $avg . 'h';
    }

    private function responseTimeColor(string $avgResponseHours): string
    {
        if ($avgResponseHours === '—') {
            return 'gray';
        }

        $hours = (float) $avgResponseHours;

        return match (true) {
            $hours <= 2 => 'success',
            $hours <= 8 => 'warning',
            default => 'danger',
        };
    }
}
