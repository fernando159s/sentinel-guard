<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\AuditableObserver;
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

        Registro::observe(\App\Observers\RegistroObserver::class);
        Registro::observe(AuditableObserver::class);
        Ticket::observe(AuditableObserver::class);
        Empresa::observe(AuditableObserver::class);
        User::observe(AuditableObserver::class);
    }
}
