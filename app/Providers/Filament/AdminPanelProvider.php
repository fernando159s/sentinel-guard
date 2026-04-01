<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplyTenantBranding;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->brandName('SecuriForm')
            ->brandLogo(function () {
                $tenant = Filament::getTenant();
                if ($tenant?->logo_path) {
                    $url = route('logos.show', $tenant->logo_path);

                    return view('filament.brand-logo', ['url' => $url, 'name' => $tenant->nombre_portal ?? $tenant->razon_social]);
                }

                return null;
            })
            ->profile()
            ->tenant(Empresa::class, slugAttribute: 'ruc')
            ->tenantRegistration(false)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->tenantMiddleware([
                ApplyTenantBranding::class,
            ], isPersistent: true)
            ->renderHook('panels::head.end', function () {
                $tenant = Filament::getTenant();
                if (! $tenant?->color_sidebar) {
                    return '';
                }

                $sidebarColor = e($tenant->color_sidebar);

                return new \Illuminate\Support\HtmlString(
                    "<style>
                        :root {
                            --sidebar-bg: {$sidebarColor};
                        }
                        .fi-sidebar {
                            background-color: var(--sidebar-bg) !important;
                        }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a {
                            color: rgba(255, 255, 255, 0.85) !important;
                        }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a:hover,
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a.fi-active {
                            color: #ffffff !important;
                            background-color: rgba(255, 255, 255, 0.1) !important;
                        }
                        .fi-sidebar .fi-sidebar-header {
                            color: #ffffff !important;
                        }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-group-label {
                            color: rgba(255, 255, 255, 0.6) !important;
                        }
                    </style>"
                );
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
