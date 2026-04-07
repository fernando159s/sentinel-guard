<?php

namespace App\Http\Middleware;

use App\Models\Politica;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePoliciesAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->empresa_id) {
            return $next($request);
        }

        // Skip for admins — they manage policies, not accept them
        if ($user->hasRole(['super_admin', 'admin_empresa'])) {
            return $next($request);
        }

        // Skip Livewire update requests (POST to /livewire/update)
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        // Skip if already on acceptance page or auth routes
        $path = $request->path();
        if (str_contains($path, 'aceptar-politicas') || str_contains($path, 'logout')) {
            return $next($request);
        }

        $pendientes = Politica::pendientesPara($user->id, $user->empresa_id);

        if ($pendientes->isNotEmpty()) {
            $tenant = Filament::getTenant();
            $ruc = $tenant?->ruc ?? $user->empresa?->ruc ?? '';

            return redirect("/admin/{$ruc}/aceptar-politicas");
        }

        return $next($request);
    }
}
