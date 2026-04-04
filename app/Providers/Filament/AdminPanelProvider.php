<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplyTenantBranding;
use App\Models\Empresa;
use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->favicon(null)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->tenant(Empresa::class, slugAttribute: 'ruc')
            ->tenantRegistration(false)
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4.5rem')
            ->navigationGroups([
                NavigationGroup::make('Soporte')
                    ->icon('heroicon-o-lifebuoy')
                    ->collapsible(),
                NavigationGroup::make('Reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Activos')
                    ->icon('heroicon-o-computer-desktop')
                    ->collapsible(),
                NavigationGroup::make('Seguridad')
                    ->icon('heroicon-o-shield-check')
                    ->collapsible(),
                NavigationGroup::make('Administración')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed(),
            ])
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->breadcrumbs(true)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->tenantMiddleware([
                ApplyTenantBranding::class,
                \App\Http\Middleware\EnsurePoliciesAccepted::class,
            ], isPersistent: true)
            ->renderHook('panels::head.end', function () {
                $tenant = Filament::getTenant();

                $css = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>';

                if ($tenant?->color_sidebar) {
                    $sidebarColor = e($tenant->color_sidebar);
                    $css .= "<style>
                        :root { --sidebar-bg: {$sidebarColor}; }
                        .fi-sidebar { background-color: var(--sidebar-bg) !important; }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a { color: rgba(255,255,255,0.85) !important; }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a:hover,
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-item a.fi-active { color: #fff !important; background-color: rgba(255,255,255,0.1) !important; }
                        .fi-sidebar .fi-sidebar-header { color: #fff !important; }
                        .fi-sidebar .fi-sidebar-nav .fi-sidebar-group-label { color: rgba(255,255,255,0.6) !important; }
                    </style>";
                }

                return new \Illuminate\Support\HtmlString($css);
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
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
