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
            ->sidebarWidth('17rem')
            ->navigationGroups([
                NavigationGroup::make('Soporte')
                    ->collapsible(),
                NavigationGroup::make('Reportes')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Activos')
                    ->collapsible(),
                NavigationGroup::make('Seguridad')
                    ->collapsible(),
                NavigationGroup::make('Administración')
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

                // Hide topbar, sidebar-only layout with user profile at bottom
                $css .= '<style>
                    .fi-topbar { display: none !important; }
                    .fi-main-ctn { padding-top: 0 !important; }
                    aside.fi-sidebar { display: flex !important; flex-direction: column !important; height: 100vh !important; overflow: hidden !important; }
                    .fi-sidebar-header-ctn { flex-shrink: 0 !important; }
                    .fi-sidebar-nav { flex: 1 1 0% !important; overflow-y: auto !important; min-height: 0 !important; }
                    .fi-sidebar-footer { flex-shrink: 0 !important; }
                </style>';

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
            ->renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_FOOTER, function () {
                $user = auth()->user();
                if (! $user) {
                    return '';
                }

                $tenant = Filament::getTenant();
                $profileUrl = $tenant ? "/admin/{$tenant->ruc}/profile" : '#';
                $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');

                return new \Illuminate\Support\HtmlString(
                    '<div class="border-t border-white/10 px-3 py-3">'
                    . '<a href="' . e($profileUrl) . '" class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-white/5">'
                    . '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-500/20 text-xs font-bold text-primary-400">' . e($initials) . '</div>'
                    . '<div class="min-w-0 flex-1">'
                    . '<p class="truncate text-sm font-medium text-white">' . e($user->name) . '</p>'
                    . '<p class="truncate text-xs text-gray-400">' . e($user->email) . '</p>'
                    . '</div>'
                    . '<svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>'
                    . '</a>'
                    . '</div>'
                );
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
