<?php

namespace App\Providers;

use App\Models\ActivoDigital;
use App\Models\Empresa;
use App\Models\Politica;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Observers\PoliticaObserver;
use App\Observers\RegistroObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(request()->getHost(), 'tunnelmole.net')) {
            URL::forceScheme('https');
        }

        Registro::observe(AuditableObserver::class);
        Registro::observe(RegistroObserver::class);
        Ticket::observe(AuditableObserver::class);
        Empresa::observe(AuditableObserver::class);
        User::observe(AuditableObserver::class);
        ActivoDigital::observe(AuditableObserver::class);
        Politica::observe(PoliticaObserver::class);
    }
}
