<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class TicketsTendenciaChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Tendencia de tickets';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '250px';

    public ?string $filter = '30';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Ultima semana',
            '30' => 'Ultimo mes',
            '90' => 'Ultimos 3 meses',
        ];
    }

    protected function getData(): array
    {
        $empresa = Filament::getTenant();
        $days = (int) ($this->filter ?? 30);

        $labels = [];
        $creados = [];
        $resueltos = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $days <= 7 ? $date->translatedFormat('D d') : $date->format('d/m');

            $creados[] = Ticket::where('empresa_id', $empresa?->id)
                ->whereDate('created_at', $date)
                ->count();

            $resueltos[] = Ticket::where('empresa_id', $empresa?->id)
                ->whereIn('estado', ['resuelto', 'cerrado'])
                ->whereDate('fecha_cierre', $date)
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Creados',
                    'data' => $creados,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => $days <= 7 ? 4 : 2,
                ],
                [
                    'label' => 'Resueltos',
                    'data' => $resueltos,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => $days <= 7 ? 4 : 2,
                ],
            ],
        ];
    }
}
