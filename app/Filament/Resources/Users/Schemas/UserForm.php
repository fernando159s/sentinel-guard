<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\ChecklistEjecucion;
use App\Models\EquipoAsignacion;
use App\Models\Politica;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = auth()->user()?->hasRole('super_admin');

        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos del usuario')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150),
                        TextInput::make('password')
                            ->label('Contrasena')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->default(fn (string $operation): ?string => $operation === 'create' ? Str::random(12) : null)
                            ->helperText(fn (string $operation): ?string => $operation === 'create' ? 'Se genera una contrasena temporal.' : 'Dejar vacio para mantener la actual.'),
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'razon_social')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->default(fn () => auth()->user()?->empresa_id),
                    ]),

                Section::make('Rol y estado')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Select::make('rol')
                            ->label('Rol')
                            ->options(function () use ($isSuperAdmin) {
                                $roles = [
                                    'admin_empresa' => 'Admin Empresa',
                                    'usuario' => 'Usuario',
                                    'solo_lectura' => 'Solo Lectura',
                                ];
                                if ($isSuperAdmin) {
                                    $roles = ['super_admin' => 'Super Admin', 'agente_helpdesk' => 'Agente Helpdesk'] + $roles;
                                }

                                return $roles;
                            })
                            ->required()
                            ->default('usuario'),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo'])
                            ->default('activo')
                            ->required(),
                    ]),

                Section::make('Notificaciones')
                    ->icon('heroicon-o-bell')
                    ->columns(2)
                    ->schema([
                        Toggle::make('notif_tickets')->label('Notificaciones de tickets')->default(true),
                        Toggle::make('notif_incidencias')->label('Notificaciones de incidencias')->default(true),
                    ]),

                // ── Read-only sections (only on edit) ──

                Section::make('Equipo asignado')
                    ->icon('heroicon-o-computer-desktop')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('equipo_info')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<p class="text-sm text-gray-500">—</p>');
                                }

                                $asignacion = EquipoAsignacion::where('user_id', $record->id)
                                    ->whereNull('fecha_fin')
                                    ->whereIn('tipo', ['asignacion', 'transferencia'])
                                    ->with('equipo')
                                    ->first();

                                if (! $asignacion?->equipo) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Sin equipo asignado.</p>');
                                }

                                $e = $asignacion->equipo;

                                return new HtmlString(
                                    '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">'
                                    . '<div class="flex items-center justify-between">'
                                    . '<div>'
                                    . '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($e->codigo_interno) . ' — ' . e($e->marca) . ' ' . e($e->modelo) . '</p>'
                                    . '<p class="text-xs text-gray-500">' . e($e->tipo) . ' | S/N: ' . e($e->numero_serie ?? '—') . ' | ' . e($e->ubicacion ?? '—') . '</p>'
                                    . '</div>'
                                    . '<span class="text-xs font-medium text-success-600 dark:text-success-400">Desde ' . $asignacion->fecha_inicio->format('d/m/Y') . '</span>'
                                    . '</div>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Politicas y NDA')
                    ->icon('heroicon-o-document-check')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('politicas_info')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record || ! $record->empresa_id) {
                                    return new HtmlString('<p class="text-sm text-gray-500">—</p>');
                                }

                                $politicas = Politica::where('empresa_id', $record->empresa_id)
                                    ->where('obligatoria', true)->where('activa', true)->get();

                                if ($politicas->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">No hay politicas obligatorias.</p>');
                                }

                                $rows = '';
                                foreach ($politicas as $p) {
                                    $aceptada = $p->aceptadaPor($record->id);
                                    $icon = $aceptada
                                        ? '<span style="color:#10b981;">&#10003;</span>'
                                        : '<span style="color:#ef4444;">&#10007;</span>';
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-1.5 px-2 text-sm">' . $icon . ' ' . e($p->titulo) . '</td>'
                                        . '<td class="py-1.5 px-2 text-xs text-gray-500">v' . e($p->version) . '</td>'
                                        . '<td class="py-1.5 px-2 text-xs ' . ($aceptada ? 'text-success-600' : 'text-danger-600') . '">' . ($aceptada ? 'Aceptada' : 'Pendiente') . '</td>'
                                        . '</tr>';
                                }

                                return new HtmlString(
                                    '<table class="w-full"><thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500 text-left">Politica</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500">Version</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500">Estado</th>'
                                    . '</tr></thead><tbody>' . $rows . '</tbody></table>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Tickets')
                    ->icon('heroicon-o-ticket')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('tickets_info')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<p class="text-sm text-gray-500">—</p>');
                                }

                                $tickets = Ticket::withoutGlobalScopes()
                                    ->where('creado_por', $record->id)
                                    ->latest()
                                    ->limit(10)
                                    ->get();

                                if ($tickets->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Sin tickets.</p>');
                                }

                                $rows = '';
                                foreach ($tickets as $t) {
                                    $color = match ($t->estado) {
                                        'nuevo' => 'info', 'en_revision' => 'warning',
                                        'resuelto' => 'success', 'cerrado' => 'gray',
                                        default => 'gray',
                                    };
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-1.5 px-2 text-xs font-mono">' . e($t->numero_ticket) . '</td>'
                                        . '<td class="py-1.5 px-2 text-xs">' . e(Str::limit($t->asunto, 40)) . '</td>'
                                        . '<td class="py-1.5 px-2"><span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold bg-' . $color . '-100 text-' . $color . '-700 dark:bg-' . $color . '-500/20 dark:text-' . $color . '-400">' . e($t->estado) . '</span></td>'
                                        . '<td class="py-1.5 px-2 text-xs text-gray-500">' . $t->created_at->format('d/m/Y') . '</td>'
                                        . '</tr>';
                                }

                                return new HtmlString(
                                    '<table class="w-full"><thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500 text-left">Ticket</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500 text-left">Asunto</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500">Estado</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500">Fecha</th>'
                                    . '</tr></thead><tbody>' . $rows . '</tbody></table>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Registros creados')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('registros_info')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<p class="text-sm text-gray-500">—</p>');
                                }

                                $registros = Registro::withoutGlobalScopes()
                                    ->where('creado_por', $record->id)
                                    ->latest()
                                    ->limit(10)
                                    ->get();

                                if ($registros->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Sin registros.</p>');
                                }

                                $rows = '';
                                foreach ($registros as $r) {
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-1.5 px-2 text-xs font-mono">' . e($r->numero_registro) . '</td>'
                                        . '<td class="py-1.5 px-2 text-xs">' . e($r->tipo_formato) . '</td>'
                                        . '<td class="py-1.5 px-2 text-xs text-gray-500">' . $r->created_at->format('d/m/Y') . '</td>'
                                        . '</tr>';
                                }

                                return new HtmlString(
                                    '<table class="w-full"><thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500 text-left">Registro</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500 text-left">Formato</th>'
                                    . '<th class="py-1.5 px-2 text-xs font-medium text-gray-500">Fecha</th>'
                                    . '</tr></thead><tbody>' . $rows . '</tbody></table>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
