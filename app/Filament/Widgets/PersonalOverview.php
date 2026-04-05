<?php

namespace App\Filament\Widgets;

use App\Models\EquipoAsignacion;
use App\Models\Politica;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class PersonalOverview extends Widget
{
    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.personal-overview';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function getPersonal(): array
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        if (! $empresaId) {
            return [];
        }

        $users = User::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->with(['roles'])
            ->get();

        $politicasTotal = Politica::where('empresa_id', $empresaId)
            ->where('obligatoria', true)->where('activa', true)->count();

        return $users->map(function ($user) use ($empresaId, $politicasTotal) {
            $equipo = EquipoAsignacion::where('user_id', $user->id)
                ->whereNull('fecha_fin')
                ->whereIn('tipo', ['asignacion', 'transferencia'])
                ->with('equipo')
                ->first();

            $politicasPendientes = Politica::pendientesPara($user->id, $empresaId)->count();
            $politicasOk = $politicasPendientes === 0 && $politicasTotal > 0;

            $ticketsAbiertos = \App\Models\Ticket::where('empresa_id', $empresaId)
                ->where('creado_por', $user->id)
                ->whereNotIn('estado', ['cerrado', 'resuelto'])
                ->count();

            $rol = $user->roles->first()?->name ?? 'usuario';
            $rolLabel = match ($rol) {
                'super_admin' => 'Super Admin',
                'admin_empresa' => 'Admin',
                'agente_helpdesk' => 'Agente',
                'solo_lectura' => 'Lectura',
                default => 'Usuario',
            };

            return [
                'nombre' => $user->name,
                'rol' => $rolLabel,
                'equipo' => $equipo ? $equipo->equipo?->codigo_interno : null,
                'nda' => $politicasOk,
                'nda_label' => $politicasOk ? 'OK' : "{$politicasPendientes} pend.",
                'tickets' => $ticketsAbiertos,
            ];
        })->toArray();
    }
}
