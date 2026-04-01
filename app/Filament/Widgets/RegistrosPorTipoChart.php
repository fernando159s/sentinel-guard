<?php

namespace App\Filament\Widgets;

use App\Enums\TipoFormato;
use App\Models\Registro;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RegistrosPorTipoChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Registros por formato';

    protected ?string $description = 'Ultimos 6 meses';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public ?string $filter = '6';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            '3' => 'Ultimos 3 meses',
            '6' => 'Ultimos 6 meses',
            '12' => 'Ultimo ano',
        ];
    }

    protected function getData(): array
    {
        $empresa = Filament::getTenant();
        $months = (int) ($this->filter ?? 6);

        $labels = [];
        $datasets = [];

        // Build month labels
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
        }

        // Colors per format type
        $colors = [
            'F01' => '#6366f1', 'F02' => '#8b5cf6', 'F03' => '#a78bfa',
            'F04' => '#c084fc', 'F05' => '#3b82f6', 'F06' => '#60a5fa',
            'F07' => '#22d3ee', 'F08' => '#2dd4bf', 'F09' => '#ef4444',
            'F10' => '#f59e0b', 'F11' => '#10b981', 'F12' => '#6b7280',
            'F13' => '#f43f5e',
        ];

        // Query counts per format per month
        $query = Registro::query()
            ->where('empresa_id', $empresa?->id)
            ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
            ->selectRaw('tipo_formato, YEAR(created_at) as yr, MONTH(created_at) as mo, count(*) as total')
            ->groupBy('tipo_formato', 'yr', 'mo')
            ->get();

        // Build lookup: [formato][yr-mo] = count
        $lookup = [];
        foreach ($query as $row) {
            $key = sprintf('%d-%02d', $row->yr, $row->mo);
            $lookup[$row->tipo_formato][$key] = $row->total;
        }

        // Only include formats that have data
        foreach (TipoFormato::cases() as $tipo) {
            if (! isset($lookup[$tipo->value])) {
                continue;
            }

            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $date->format('Y-m');
                $data[] = $lookup[$tipo->value][$key] ?? 0;
            }

            $datasets[] = [
                'label' => $tipo->value . ' - ' . $tipo->prefix(),
                'data' => $data,
                'backgroundColor' => $colors[$tipo->value] ?? '#6b7280',
                'borderRadius' => 4,
            ];
        }

        // If no data at all, show empty chart
        if (empty($datasets)) {
            $datasets[] = [
                'label' => 'Sin registros',
                'data' => array_fill(0, $months, 0),
                'backgroundColor' => '#374151',
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
