<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectSuperAdminToSelectEmpresa
{
    public function handle(Request $request, Closure $next)
    {
        // Solo interceptar la ruta exacta /admin (el redirect a tenant)
        if (
            $request->is('admin')
            && ! $request->is('admin/*')
            && auth()->check()
            && auth()->user()->hasRole('super_admin')
        ) {
            return redirect('/select-empresa');
        }

        return $next($request);
    }
}
