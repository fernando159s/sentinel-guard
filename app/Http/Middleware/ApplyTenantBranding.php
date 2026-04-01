<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantBranding
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            if ($tenant->color_primario) {
                FilamentColor::register([
                    'primary' => $tenant->color_primario,
                ]);
            }

            if ($tenant->color_secundario) {
                FilamentColor::register([
                    'warning' => $tenant->color_secundario,
                ]);
            }
        }

        return $next($request);
    }
}
