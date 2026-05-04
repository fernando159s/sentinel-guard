<?php

namespace App\Filament\Widgets;

use App\Models\Politica;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class PoliticasCumplimiento extends Widget
{
    protected static ?int $sort = 8;

    protected string $view = 'filament.widgets.politicas-cumplimiento';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function getData(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $empresaId) {
            return ['politicas' => [], 'totalUsuarios' => 0];
        }

        $totalUsuarios = User::firmantes()
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->count();

        $politicas = Politica::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->where('obligatoria', true)
            ->withCount(['aceptaciones as aceptaciones_vigentes_count' => function ($q) {
                $q->whereColumn('aceptaciones_politica.version_aceptada', 'politicas.version')
                    ->whereHas('user', fn ($u) => $u->firmantes());
            }])
            ->get()
            ->map(fn ($p) => [
                'titulo' => $p->titulo,
                'version' => $p->version,
                'aceptadas' => $p->aceptaciones_vigentes_count,
                'total' => $totalUsuarios,
                'porcentaje' => $totalUsuarios > 0 ? round(($p->aceptaciones_vigentes_count / $totalUsuarios) * 100) : 0,
            ]);

        return [
            'politicas' => $politicas,
            'totalUsuarios' => $totalUsuarios,
        ];
    }
}
