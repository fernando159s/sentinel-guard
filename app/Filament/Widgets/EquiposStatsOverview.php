<?php

namespace App\Filament\Widgets;

use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EquiposStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected function getStats(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $empresaId) {
            return [];
        }

        $total = Equipo::where('empresa_id', $empresaId)->count();
        $asignados = Equipo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->whereHas('asignacionVigente')
            ->count();
        $sinAsignar = Equipo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->whereDoesntHave('asignacionVigente')
            ->count();
        $mantenimiento = Equipo::where('empresa_id', $empresaId)
            ->where('estado', 'mantenimiento')
            ->count();

        return [
            Stat::make('Total equipos', $total)
                ->description('Inventario completo')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),
            Stat::make('Asignados', $asignados)
                ->description('Con usuario asignado')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success'),
            Stat::make('Sin asignar', $sinAsignar)
                ->description('Disponibles')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($sinAsignar > 0 ? 'warning' : 'gray'),
            Stat::make('En mantenimiento', $mantenimiento)
                ->description('Requieren atencion')
                ->descriptionIcon('heroicon-m-wrench')
                ->color($mantenimiento > 0 ? 'danger' : 'gray'),
        ];
    }
}
