<?php

namespace App\Filament\Widgets;

use App\Enums\TipoFormato;
use App\Models\Registro;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class QuickAccessFormatos extends Widget
{
    protected static ?int $sort = 5;

    protected string $view = 'filament.widgets.quick-access-formatos';

    protected int|string|array $columnSpan = 'full';

    public function getFormatos(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $empresaId) {
            return [];
        }

        $counts = Registro::where('empresa_id', $empresaId)
            ->selectRaw('tipo_formato, count(*) as total')
            ->groupBy('tipo_formato')
            ->pluck('total', 'tipo_formato')
            ->toArray();

        $formatos = [];
        foreach (TipoFormato::cases() as $tipo) {
            $formatos[] = [
                'value' => $tipo->value,
                'label' => $tipo->label(),
                'prefix' => $tipo->prefix(),
                'count' => $counts[$tipo->value] ?? 0,
                'url' => url("admin/{$empresa->ruc}/registros?tableFilters[tipo_formato][value]={$tipo->value}"),
                'create_url' => url("admin/{$empresa->ruc}/registros/create?tipo_formato={$tipo->value}"),
            ];
        }

        // Sort by count descending
        usort($formatos, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $formatos;
    }
}
