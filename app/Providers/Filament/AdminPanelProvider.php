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
use Openplain\FilamentShadcnTheme\Color as ShadcnColor;
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
            ->favicon(null)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->tenant(Empresa::class, slugAttribute: 'ruc')
            ->tenantRegistration(false)
            ->colors([
                'primary' => ShadcnColor::Violet,
                'danger' => ShadcnColor::Red,
                'info' => ShadcnColor::Blue,
                'success' => ShadcnColor::Green,
                'warning' => ShadcnColor::Orange,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('14rem')
            ->collapsedSidebarWidth('4.5rem')
            ->navigationGroups([
                NavigationGroup::make('General')->collapsible(),
                NavigationGroup::make('Soporte')->collapsible(),
                NavigationGroup::make('Reportes')->collapsible()->collapsed(),
                NavigationGroup::make('Activos')->collapsible(),
                NavigationGroup::make('Seguridad')->collapsible(),
                NavigationGroup::make('Administración')->collapsible()->collapsed(),
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
            ->renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START, function () {
                $user = auth()->user();
                $tenant = Filament::getTenant();
                if (! $user) {
                    return '';
                }

                $hora = now()->hour;
                $saludo = match (true) {
                    $hora < 12 => 'Buenos dias',
                    $hora < 18 => 'Buenas tardes',
                    default => 'Buenas noches',
                };
                $nombre = e(explode(' ', $user->name)[0]);
                $empresaNombre = e($tenant?->razon_social ?? 'SecuriForm');
                $logoUrl = $tenant?->logo_path ? route('logos.show', $tenant->logo_path) : null;

                $logoHtml = $logoUrl
                    ? '<img src="' . e($logoUrl) . '" alt="" style="height:28px;width:28px;border-radius:6px;object-fit:cover;">'
                    : '<div style="height:28px;width:28px;border-radius:6px;background:rgba(139,92,246,0.2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#a78bfa;">' . mb_strtoupper(mb_substr($empresaNombre, 0, 2)) . '</div>';

                return new \Illuminate\Support\HtmlString(
                    '<div style="display:flex;align-items:center;gap:10px;padding:0 8px;">'
                    . $logoHtml
                    . '<div>'
                    . '<p style="font-size:13px;font-weight:600;color:white;line-height:1.2;margin:0;">' . $empresaNombre . '</p>'
                    . '<p style="font-size:10px;color:#9ca3af;margin:0;">' . $saludo . ', ' . $nombre . '</p>'
                    . '</div>'
                    . '</div>'
                );
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
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
